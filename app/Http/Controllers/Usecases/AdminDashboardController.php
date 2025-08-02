<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblUniversite;
use App\Models\TblFiliere;
use App\Models\TblProjet;

class AdminDashboardController extends Controller
{
    public function getStats(Request $request)
    {
        // On compte les éléments dans les tables
        $universities = TblUniversite::count();
        $filieres = TblFiliere::count();
        $projets = TblProjet::count();

        return response()->json([
            'universities' => $universities,
            'filieres' => $filieres,
            'projets' => $projets,
        ]);
    }
}
