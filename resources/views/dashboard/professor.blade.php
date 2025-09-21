<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Professor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="bg-blue-600 text-white p-4">
            <div class="container mx-auto flex justify-between items-center">
                <h1 class="text-xl font-bold">Dashboard - Professor</h1>
                <button id="logout" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">
                    Sair
                </button>
            </div>
        </nav>
        
        <div class="container mx-auto mt-8 p-4">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-4">Bem-vindo, Professor!</h2>
                <p class="text-gray-600 mb-4">Este é seu painel de controle.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-100 p-4 rounded">
                        <h3 class="font-bold">Meus Agendamentos</h3>
                        <p class="text-sm text-gray-600">Visualizar agendamentos</p>
                    </div>
                    <div class="bg-green-100 p-4 rounded">
                        <h3 class="font-bold">Novo Agendamento</h3>
                        <p class="text-sm text-gray-600">Criar novo agendamento</p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded">
                        <h3 class="font-bold">Perfil</h3>
                        <p class="text-sm text-gray-600">Editar informações</p>
                    </div>
                </div>
                
                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-4">Informações da API</h3>
                    <div id="user-info" class="bg-gray-50 p-4 rounded">
                        <p>Carregando informações do usuário...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Verificar se o usuário está logado
        const token = localStorage.getItem('token');
        const userData = localStorage.getItem('user');
        
        if (!token || !userData) {
            window.location.href = '/login';
        } else {
            try {
                const user = JSON.parse(userData);
                document.getElementById('user-info').innerHTML = `
                    <p><strong>Nome:</strong> ${user.name}</p>
                    <p><strong>Email:</strong> ${user.email}</p>
                    <p><strong>Role:</strong> ${user.role}</p>
                    <p><strong>Phone:</strong> ${user.phone || 'Não informado'}</p>
                `;
            } catch (error) {
                console.error('Erro ao processar dados do usuário:', error);
                window.location.href = '/login';
            }
        }

        document.getElementById('logout').addEventListener('click', function() {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        });
    </script>
</body>
</html>
