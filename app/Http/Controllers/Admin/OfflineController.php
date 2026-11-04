<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Evenement;
use App\Models\OfflineLease;
use App\Services\OfflineLeaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
class OfflineController extends Controller {
    public function index(Evenement $evenement): View {
        $this->authorizeEvenement($evenement);
        $lease = OfflineLease::where('evenement_id',$evenement->id)->with(['agent','events'])->latest()->first();
        $agents = Agent::where('evenement_id',$evenement->id)->get();
        return view('admin.offline.index', compact('evenement','lease','agents'));
    }
    public function authorize(Request $request, Evenement $evenement, OfflineLeaseService $service): RedirectResponse {
        $this->authorizeEvenement($evenement);
        $request->validate(['agent_id'=>'required|exists:agents,id']);
        $agent = Agent::where('id',$request->agent_id)->where('evenement_id',$evenement->id)->firstOrFail();
        try { $service->authorize($evenement,$agent,Auth::user()); return back()->with('success','Agent autorisé pour le mode hors ligne.'); }
        catch (\Exception $e) { return back()->with('error',$e->getMessage()); }
    }
    public function activate(Request $request, Evenement $evenement, OfflineLeaseService $service): RedirectResponse {
        $this->authorizeEvenement($evenement);
        $lease = $service->activeForEvent($evenement);
        if(!$lease) return back()->with('error','Aucune session hors ligne à activer.');
        $service->requestActivation($lease);
        return back()->with('success','Demande d\'activation envoyée. L\'agent doit acquitter.');
    }
    public function revoke(Request $request, Evenement $evenement, OfflineLeaseService $service): RedirectResponse {
        $this->authorizeEvenement($evenement);
        $lease = $service->activeForEvent($evenement);
        if(!$lease) return back()->with('error','Aucune session hors ligne à révoquer.');
        $service->requestRevocation($lease, Auth::user());
        return back()->with('success','Révocation demandée. En attente d\'acquittement.');
    }
    public function forceRevoke(Request $request, Evenement $evenement, OfflineLeaseService $service): RedirectResponse {
        $this->authorizeEvenement($evenement);
        $lease = $service->activeForEvent($evenement);
        if(!$lease) return back()->with('error','Aucune session hors ligne à révoquer.');
        $service->forceRevoke($lease, Auth::user());
        return back()->with('success','Session hors ligne révoquée de force.');
    }
    public function report(Evenement $evenement): View {
        $this->authorizeEvenement($evenement);
        return view('admin.offline.report', compact('evenement'));
    }
    private function authorizeEvenement(Evenement $evenement): void {
        if((int)$evenement->user_id !== (int)Auth::id()) abort(403);
    }
}
