<?php

namespace App\Http\Controllers\Ressources;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TblEtudiant;

class TblEtudiantController extends Controller
{
    public function index()
    {
        return TblEtudiant::all();
    }

    public function show($id)
    {
        return TblEtudiant::findOrFail($id);
    }

    public function store(Request $request)
    {
        $etudiant = TblEtudiant::create($request->all());
        return response()->json($etudiant, 201);
    }

    public function update(Request $request, $id)
    {
        $etudiant = TblEtudiant::findOrFail($id);
        $etudiant->update($request->all());
        return response()->json($etudiant, 200);
    }

    public function destroy($id)
    {
        TblEtudiant::destroy($id);
        return response()->json(null, 204);
    }
}
