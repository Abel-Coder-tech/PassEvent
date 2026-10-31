<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ParametresController extends Controller
{
    public function index()
    {
        $commissionPhysique = Setting::getFloat('commission_physique', \App\Models\LotPhysique::TAUX_AUTO);

        return view('superadmin.parametres.index', compact('commissionPhysique'));
    }

    public function updateCommissionPhysique(Request $request)
    {
        $validated = $request->validate([
            'commission_physique' => 'required|numeric|min:0|max:100',
        ], [
            'commission_physique.required' => 'Le taux de commission est obligatoire.',
            'commission_physique.numeric' => 'Le taux doit être un nombre.',
            'commission_physique.min' => 'Le taux ne peut pas être négatif.',
            'commission_physique.max' => 'Le taux ne peut pas dépasser 100%.',
        ]);

        Setting::set('commission_physique', (float) $validated['commission_physique']);

        return back()->with('success', 'Le taux de commission des tickets physiques a été mis à jour.');
    }
}
