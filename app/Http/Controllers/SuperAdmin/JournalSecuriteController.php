<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\IpBloquee;
use App\Models\JournalSecurite;
use App\Support\PerPage;
use Illuminate\Http\Request;

// Journal de sécurité : tentatives de connexion, liens suspects, attaques, IP bloquées
class JournalSecuriteController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type');
        $q = trim((string) $request->input('q'));
        $ip = trim((string) $request->input('ip'));
        $periode = $request->input('periode', '7');

        $debut = match ($periode) {
            '1' => now()->subDay(),
            '30' => now()->subDays(30),
            '90' => now()->subDays(90),
            default => now()->subDays(7),
        };

        $query = JournalSecurite::query()->where('created_at', '>=', $debut);

        if ($type && isset(JournalSecurite::TYPES[$type])) {
            $query->where('type', $type);
        }
        if ($ip !== '') {
            $query->where('ip', $ip);
        }
        if ($q !== '') {
            $query->where(fn ($s) => $s->where('ip', 'like', "%{$q}%")
                ->orWhere('identifiant', 'like', "%{$q}%")
                ->orWhere('url', 'like', "%{$q}%")
                ->orWhere('user_agent', 'like', "%{$q}%")
                ->orWhere('regle', 'like', "%{$q}%"));
        }

        $evenements = $query->orderByDesc('id')->paginate(PerPage::resolve())->withQueryString();

        $base = JournalSecurite::query()->where('created_at', '>=', $debut);
        $stats = [
            'total' => (clone $base)->count(),
            'connexions' => (clone $base)->whereIn('type', ['connexion_echouee', 'connexion_bloquee'])->count(),
            'attaques' => (clone $base)->whereIn('type', ['attaque', 'robot', 'lien_suspect'])->count(),
            'ips' => (clone $base)->distinct('ip')->count('ip'),
        ];

        // IP les plus actives sur la période
        $topIps = (clone $base)->selectRaw('ip, COUNT(*) as total, MAX(created_at) as dernier')
            ->groupBy('ip')->orderByDesc('total')->limit(8)->get();

        $ipsBloquees = IpBloquee::orderByDesc('id')->get();
        $bloqueesActives = $ipsBloquees->filter->estActive()->pluck('ip')->all();

        // Vue par IP : résumé de ce que cette IP a fait
        $resumeIp = null;
        if ($ip !== '') {
            $resumeIp = [
                'ip' => $ip,
                'total' => JournalSecurite::where('ip', $ip)->count(),
                'premier' => JournalSecurite::where('ip', $ip)->min('created_at'),
                'dernier' => JournalSecurite::where('ip', $ip)->max('created_at'),
                'parType' => JournalSecurite::where('ip', $ip)->selectRaw('type, COUNT(*) as n')->groupBy('type')->pluck('n', 'type'),
                'identifiants' => JournalSecurite::where('ip', $ip)->whereNotNull('identifiant')->distinct()->limit(10)->pluck('identifiant'),
                'bloquee' => in_array($ip, $bloqueesActives, true),
            ];
        }

        $types = JournalSecurite::TYPES;

        return view('superadmin.journal-securite', compact(
            'evenements', 'stats', 'topIps', 'ipsBloquees', 'bloqueesActives', 'resumeIp',
            'types', 'type', 'q', 'ip', 'periode'
        ));
    }

    public function bloquer(Request $request)
    {
        $data = $request->validate([
            'ip' => ['required', 'ip'],
            'duree' => ['required', 'in:1,24,168,720,permanent'],
            'motif' => ['nullable', 'string', 'max:255'],
        ]);

        // Ne jamais se bloquer soi-même
        if ($data['ip'] === $request->ip()) {
            return back()->with('error', 'Impossible de bloquer votre propre IP.');
        }

        IpBloquee::updateOrCreate(['ip' => $data['ip']], [
            'motif' => $data['motif'] ?? null,
            'bloque_par' => auth('superadmin')->user()?->email,
            'expire_le' => $data['duree'] === 'permanent' ? null : now()->addHours((int) $data['duree']),
        ]);

        return back()->with('success', "IP {$data['ip']} bloquée.");
    }

    public function debloquer(IpBloquee $ipBloquee)
    {
        $ip = $ipBloquee->ip;
        $ipBloquee->delete();

        return back()->with('success', "IP {$ip} débloquée.");
    }
}
