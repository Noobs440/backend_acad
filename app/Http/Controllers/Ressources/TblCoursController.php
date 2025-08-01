<?php

namespace App\Http\Controllers\Ressources;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TblCours;

class TblCoursController extends Controller
{
    public function index()
    {
        return TblCours::all();
    }

    public function show($id)
    {
        return TblCours::findOrFail($id);
    }

    public function store(Request $request)
    {
        $cours = TblCours::create($request->all());
        return response()->json($cours, 201);
    }

    public function update(Request $request, $id)
    {
        $cours = TblCours::findOrFail($id);
        $cours->update($request->all());
        return response()->json($cours, 200);
    }

    public function destroy($id)
    {
        TblCours::destroy($id);
        return response()->json(null, 204);
    }
}
