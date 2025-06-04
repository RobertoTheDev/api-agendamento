<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EEEP Adolfo Ferreira de Sousa - Agendamentos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Estilos da barra lateral */
        .sidebar {
            position: fixed;
            top: 0;
            left: -250px; /* Barra lateral começa oculta */
            width: 250px;
            height: 100%;
            background-color: #1a1a1a;
            padding-top: 20px;
            transition: left 0.3s ease;
        }

        .sidebar.open {
            left: 0; /* Quando a barra lateral é aberta */
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
            font-size: 18px;
            transition: background-color 0.3s;
            text-align: center;
            border-bottom: 1px solid #333;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        /* Ícone de engrenagem */
        .gear-icon {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #1a1a1a;
            padding: 15px;
            border-radius: 50%;
            color: white;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s;
        }

        .gear-icon:hover {
            background-color: #575757;
        }

        /* Conteúdo principal */
        .main-content {
            margin-left: 0;
            transition: margin-left 0.3s ease;
            padding: 20px;
        }

        .main-content.sidebar-open {
            margin-left: 250px; /* Desloca o conteúdo quando a barra lateral está aberta */
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-200">

    <!-- Barra lateral -->
    <div class="sidebar" id="sidebar">
        <h1 class="text-3xl text-center text-white mb-10">EEEP</h1>

        @auth
            <a href="{{ route('home') }}">Início</a>
            <a href="{{ route('agendamentos.index') }}">Agendamentos</a>
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
               Sair
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        @else
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Cadastrar</a>
        @endauth
    </div>

    <!-- Ícone da engrenagem -->
    <div class="gear-icon" id="gear-icon">
        ⚙️
    </div>

    <!-- Conteúdo principal -->
    <div class="main-content" id="main-content">
        <header class="bg-gray-800 p-6 text-center shadow-md">
            <h1 class="text-4xl font-bold">EEEP Adolfo Ferreira de Sousa</h1>
        </header>

        <section class="bg-gray-700 text-center py-16 shadow-md rounded-lg mx-4 mt-8">
            <h1 class="text-5xl font-bold">Sistema de Agendamento</h1>
            <p class="text-lg mt-4 max-w-2xl mx-auto">
                Gerencie facilmente o uso dos espaços da escola com um clique.
            </p>
            <div class="mt-6">
                @guest
                    <a href="{{ route('login') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-4 rounded-lg text-lg font-semibold mx-2">Entrar</a>
                    <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-lg text-lg font-semibold mx-2">Criar Conta</a>
                @endguest
            </div>
        </section>

        <section class="text-center py-12">
            <h2 class="text-3xl font-bold">Sobre o Sistema</h2>
            <p class="text-lg mt-4 max-w-2xl mx-auto">
                Nossa plataforma permite que professores agendem laboratórios, quadras e auditórios de forma rápida e segura.
            </p>
        </section>

        <footer class="bg-gray-800 p-4 text-center mt-12">
            <p>&copy; 2024 EEEP Adolfo Ferreira de Sousa | Todos os direitos reservados</p>
        </footer>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const gearIcon = document.getElementById('gear-icon');

        gearIcon.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            mainContent.classList.toggle('sidebar-open');
        });

        // Adicionando o redirecionamento
        @auth
            const redirectUrl = '{{ session('redirect_url') }}';
            if (redirectUrl) {
                window.location.href = redirectUrl;
            }
        @endauth
    </script>

</body>
</html>