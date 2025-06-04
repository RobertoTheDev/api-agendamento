<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor - EEEP Adolfo Ferreira de Sousa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-200">
    
    <!-- Barra lateral -->
    <div class="sidebar bg-gray-800 w-64 h-full fixed left-0 top-0 p-5">
        <h1 class="text-white text-2xl font-bold text-center mb-6">EEEP</h1>
        
        <!-- Perfil do usuário -->
        <div class="text-center mb-6">
            <img src="{{ auth()->user()->foto ?? 'https://via.placeholder.com/100' }}" class="w-20 h-20 rounded-full mx-auto">
            <p class="mt-2 font-semibold">{{ auth()->user()->name }}</p>
        </div>
        
        <nav>
            <a href="{{ route('home') }}" class="block py-2 px-4 text-white hover:bg-gray-700">Início</a>
            <a href="{{ route('agendamentos.index') }}" class="block py-2 px-4 text-white hover:bg-gray-700">Reservas</a>
            <a href="{{ route('agendamentos.create') }}" class="block py-2 px-4 text-white hover:bg-gray-700">Fazer Reserva</a>
            <a href="{{ route('logout') }}" class="block py-2 px-4 text-white hover:bg-gray-700" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sair</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
        </nav>
    </div>

    <!-- Conteúdo principal -->
    <div class="ml-64 p-6">
        <header class="bg-gray-800 p-6 text-center shadow-md rounded-lg">
            <h1 class="text-3xl font-bold">Painel do Gestor</h1>
        </header>

        <!-- Lista de professores -->
        <section class="bg-gray-700 p-6 mt-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4">Usuários (Professores)</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($professores as $professor)
                    <div class="bg-gray-800 p-4 rounded-lg text-center">
                        <img src="{{ $professor->foto ?? 'https://via.placeholder.com/100' }}" class="w-16 h-16 rounded-full mx-auto">
                        <p class="mt-2">{{ $professor->name }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Reservas existentes -->
        <section class="bg-gray-700 p-6 mt-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4">Reservas</h2>
            <ul>
                @foreach ($reservas as $reserva)
                    <li class="bg-gray-800 p-3 rounded-lg mb-2">
                        <strong>{{ $reserva->espaco }}</strong> - {{ $reserva->data }} ({{ $reserva->horario }})
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</body>
</html>
