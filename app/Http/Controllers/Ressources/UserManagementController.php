<?php

namespace App\Http\Controllers\Ressources;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    // Liste des utilisateurs
    public function index()
    {
        $users = User::select('id', 'nom_user', 'email', 'photo', 'matricule', 'tbl_filiere_id', 'role')->get();
        return response()->json($users);
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'nom_user' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string|min:6',
        'matricule' => 'nullable|string|max:255',
        'tbl_filiere_id' => 'nullable|exists:tbl_filieres,id',
    ]);

    $user = User::create([
        'nom_user' => $validated['nom_user'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
        'matricule' => $validated['matricule'],
        'tbl_filiere_id' => $validated['tbl_filiere_id'],
        'role' => $request->input('role', 'user'),

    ]);

    return response()->json($user, 201);
}



public function update(Request $request, User $user)
{

    $data = $request->validate([
        'nom_user' => 'required|string|max:255',
        'email' => ['required','email', Rule::unique('users')->ignore($user->id)],
        'matricule' => 'nullable|string|max:100',
        'tbl_filiere_id' => 'nullable|integer|exists:tbl_filieres,id',
        'role' => 'required|string|in:admin,superviseur,user',

    ]);

    $user->update($data);

    return response()->json($user);
}


public function destroy(User $user)
{
    try {
        // Supprimer les relations éventuelles si nécessaire
       // $user->collaborateurs()->detach();
        //$user->superviseurs()->detach();

        // Supprimer documents et projets s'ils sont en cascade ou manuellement
        $user->documents()->delete();
        $user->projets()->delete();

        // Supprimer l'utilisateur
        $user->delete();

        return response()->json(null, 204);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors de la suppression : ' . $e->getMessage()
        ], 500);
    }
}

public function resetPassword(Request $request, $id)
{
    $request->validate([
        'password' => 'required|string|min:6'
    ]);

    $user = User::findOrFail($id);
    $user->password = Hash::make($request->password);
    $user->save();

    return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
}

}
