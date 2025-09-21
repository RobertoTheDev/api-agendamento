<?php 

namespace Tests\Feature;

use App\Models\User;
use App\Models\Suspension;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuspensionTest extends TestCase
{
    use RefreshDatabase;

    private User $gestor;
    private User $professor;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->gestor = User::factory()->create(['role' => 'gestor']);
        $this->professor = User::factory()->create(['role' => 'professor']);
    }

    public function test_gestor_can_create_suspension(): void
    {
        $response = $this->actingAs($this->gestor, 'sanctum')
                        ->postJson('/api/management/suspensions', [
                            'user_id' => $this->professor->id,
                            'location' => 'Quadra 1',
                            'reason' => 'Manutenção programada',
                            'start_date' => Carbon::now()->addDay()->format('Y-m-d H:i:s'),
                            'end_date' => Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
                            'is_evaluation_period' => false,
                        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'suspension' => ['id', 'location', 'reason'],
                ]);
    }

    public function test_professor_cannot_create_suspension(): void
    {
        $response = $this->actingAs($this->professor, 'sanctum')
                        ->postJson('/api/management/suspensions', [
                            'location' => 'Quadra 1',
                            'reason' => 'Teste',
                            'start_date' => Carbon::now()->addDay()->format('Y-m-d H:i:s'),
                            'end_date' => Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
                            'is_evaluation_period' => false,
                        ]);

        $response->assertStatus(403);
    }

    public function test_gestor_can_list_suspensions(): void
    {
        Suspension::factory()->count(3)->create();

        $response = $this->actingAs($this->gestor, 'sanctum')
                        ->getJson('/api/management/suspensions');

        $response->assertStatus(200);
    }

    public function test_gestor_can_delete_suspension(): void
    {
        $suspension = Suspension::factory()->create([
            'user_id' => $this->professor->id,
        ]);

        $response = $this->actingAs($this->gestor, 'sanctum')
                        ->deleteJson("/api/management/suspensions/{$suspension->id}");

        $response->assertStatus(200);
        $this->assertModelMissing($suspension);
    }
}