<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Criando a tabela de usuários
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nome do usuário
            $table->string('email')->unique(); // Email
            $table->timestamp('email_verified_at')->nullable(); // Verificação do email
            $table->string('password'); // Senha
            $table->string('profile_picture')->nullable(); // Foto do perfil
            $table->enum('role', ['admin', 'professor', 'gestor'])->default('professor'); // Definição de função
            $table->rememberToken(); // Token de "lembrar-me"
            $table->timestamps(); // Data de criação e atualização
        });

        // Criar o usuário admin padrão (diretor da escola)
        DB::table('users')->insert([
            'name' => 'Administrador',
            'email' => 'admin@escola.com',
            'password' => Hash::make('admin123'), // Senha segura
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tabela para redefinir a senha
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary(); // Email do usuário
            $table->string('token'); // Token de redefinição de senha
            $table->timestamp('created_at')->nullable(); // Data de criação do token
        });

        // Tabela para sessões dos usuários
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // ID da sessão
            $table->foreignId('user_id')->nullable()->index(); // Relacionamento com o usuário
            $table->string('ip_address', 45)->nullable(); // Endereço IP do usuário
            $table->text('user_agent')->nullable(); // Agente do usuário (navegador, dispositivo)
            $table->longText('payload'); // Informações da sessão
            $table->integer('last_activity')->index(); // Última atividade na sessão
        });

        // Tabela de agendamentos (Booking)
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Relacionamento com o usuário (professor)
            $table->string('location'); // Local reservado (ex: quadra, laboratório)
            $table->integer('lesson_period'); // Período da aula (1 a 9)
            $table->timestamp('start_time'); // Hora de início do agendamento
            $table->timestamp('end_time'); // Hora de término do agendamento
            $table->boolean('is_friendly_match')->default(false); // Define se o agendamento é um amistoso
            $table->timestamps(); // Data de criação e atualização do agendamento
        });

        // Tabela de suspensões (para períodos de prova ou bloqueios temporários)
        Schema::create('suspensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Gestor que criou a suspensão
            $table->string('location'); // Local afetado (ex: quadra)
            $table->date('start_date'); // Data de início da suspensão
            $table->date('end_date'); // Data de término da suspensão
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suspensions'); // Remover tabela de suspensões
        Schema::dropIfExists('bookings'); // Remover tabela de agendamentos
        Schema::dropIfExists('users'); // Remover tabela de usuários
        Schema::dropIfExists('password_reset_tokens'); // Remover tabela de redefinição de senha
        Schema::dropIfExists('sessions'); // Remover tabela de sessões
    }
};
