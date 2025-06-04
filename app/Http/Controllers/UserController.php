<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Exibir a lista de usuários.
     * Apenas administradores podem visualizar todos os usuários.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Verifica se o usuário autenticado é admin
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $users = User::all();
        return response()->json($users);
    }

    /**
     * Criar um novo usuário.
     * Apenas administradores podem criar usuários.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Somente administradores podem criar usuários
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:professor,gestor,admin', // Agora permite admin
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        if ($request->hasFile('profile_picture')) {
            $validated['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $user = User::create($validated);
        return response()->json($user, 201);
    }

    /**
     * Exibir um usuário específico.
     * Apenas administradores ou o próprio usuário podem visualizar os dados.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Permite que o próprio usuário ou um admin veja os dados
        if ($request->user()->id !== $user->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return response()->json($user);
    }

    /**
     * Atualizar os dados do usuário.
     * O próprio usuário pode editar seus dados, e o admin pode editar qualquer usuário.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Permite que apenas o próprio usuário ou um admin edite os dados
        if ($request->user()->id !== $user->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|in:professor,gestor,admin',
            'password' => 'nullable|string|min:8',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && Storage::exists($user->profile_picture)) {
                Storage::delete($user->profile_picture);
            }
            $validated['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $user->update($validated);
        return response()->json($user);
    }

    /**
     * Excluir um usuário.
     * Apenas administradores podem excluir usuários.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        // Apenas administradores podem excluir usuários
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $user = User::findOrFail($id);

        if ($user->profile_picture && Storage::exists($user->profile_picture)) {
            Storage::delete($user->profile_picture);
        }

        $user->delete();
        return response()->json(['message' => 'Usuário excluído com sucesso']);
    }
}
