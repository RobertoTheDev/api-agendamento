<?php
namespace App\Http\Controllers;

use App\Models\Suspension;
use Illuminate\Http\Request;

class ManagementController extends Controller
{
    // Construtor para garantir que apenas gestores e o admin possam acessar essas rotas
    public function __construct()
    {
        $this->middleware('role:gestor,admin'); // Permite acesso tanto para gestores quanto para o superusuário admin
    }

    /**
     * Criar uma suspensão de local.
     */
    public function createSuspension(Request $request)
    {
        // Validação dos dados da requisição
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_evaluation_period' => 'required|boolean', // Adicionando o campo para período de avaliação
        ]);

        // Verificar se já existe uma suspensão no mesmo local para as datas
        $existingSuspension = Suspension::where('location', $validated['location'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                    ->orWhere(function ($query) use ($validated) {
                        $query->where('start_date', '<=', $validated['start_date'])
                              ->where('end_date', '>=', $validated['end_date']);
                    });
            })->exists();

        if ($existingSuspension) {
            return response()->json(['message' => 'Já existe uma suspensão no local para as datas informadas.'], 400);
        }

        // Criar a suspensão no banco de dados
        $suspension = Suspension::create([
            'location' => $validated['location'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_evaluation_period' => $validated['is_evaluation_period'], // Adicionando o campo para o período de avaliação
        ]);

        return response()->json(['message' => 'Suspensão criada com sucesso.', 'data' => $suspension], 201);
    }

    /**
     * Listar todas as suspensões.
     */
    public function listSuspensions()
    {
        return response()->json(Suspension::all());
    }

    /**
     * Excluir uma suspensão.
     */
    public function deleteSuspension($id)
    {
        $suspension = Suspension::findOrFail($id);
        $suspension->delete();

        return response()->json(['message' => 'Suspensão removida com sucesso.']);
    }
}
