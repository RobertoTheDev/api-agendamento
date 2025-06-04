<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - EEEP Adolfo Ferreira de Sousa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script> <!-- Adicionado Axios -->
</head>
<body class="bg-gray-900 text-gray-200 flex items-center justify-center min-h-screen">
    <section class="max-w-md mx-auto bg-gray-800 p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-semibold text-center">Cadastrar Usuário</h2>
        <p id="error-message" class="bg-red-600 text-white p-3 rounded-lg mb-4 hidden"></p>
        <form id="registerForm" class="mt-6">
            <div class="mb-4">
                <label for="name" class="block">Nome</label>
                <input type="text" id="name" class="w-full p-2 mt-2 bg-gray-700 text-white rounded-md" required>
            </div>

            <div class="mb-4">
                <label for="email" class="block">E-mail</label>
                <input type="email" id="email" class="w-full p-2 mt-2 bg-gray-700 text-white rounded-md" required>
            </div>

            <div class="mb-4 relative">
                <label for="password" class="block">Senha</label>
                <input type="password" id="password" class="w-full p-2 mt-2 bg-gray-700 text-white rounded-md" required>
                <button type="button" onclick="togglePassword()" class="absolute right-3 top-10 text-gray-400 hover:text-gray-200">👁️</button>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="block">Confirmar Senha</label>
                <input type="password" id="password_confirmation" class="w-full p-2 mt-2 bg-gray-700 text-white rounded-md" required>
            </div>

            <!-- Campo de Tipo de Usuário -->
            <div class="mb-4">
                <label for="role" class="block">Tipo de Usuário</label>
                <select id="role" class="w-full p-2 mt-2 bg-gray-700 text-white rounded-md" required>
                    <option value="professor">Professor</option>
                    <option value="gestor">Gestor</option>
                </select>
            </div>

            <div class="text-center">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg w-full">Cadastrar</button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p>Já tem uma conta? <a href="/login" class="text-blue-400 hover:underline">Faça login</a></p>
        </div>
    </section>

    <script>
        function togglePassword() {
            let passwordField = document.getElementById("password");
            passwordField.type = passwordField.type === "password" ? "text" : "password";
        }

        document.getElementById('registerForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            let name = document.getElementById('name').value;
            let email = document.getElementById('email').value;
            let password = document.getElementById('password').value;
            let password_confirmation = document.getElementById('password_confirmation').value;
            let role = document.getElementById('role').value;
            let errorMessage = document.getElementById('error-message');

            errorMessage.classList.add('hidden'); // Esconder erro antes da requisição

            // Validação do e-mail institucional (somente @prof.ce.gov.br)
            let emailPattern = /^[a-zA-Z0-9._%+-]+@prof\.ce\.gov\.br$/;
            
            if (!emailPattern.test(email)) {
                errorMessage.textContent = "O e-mail deve ser institucional e terminar com '@prof.ce.gov.br'.";
                errorMessage.classList.remove('hidden');
                return;
            }

            axios.post('/api/auth/register', {
                name: name,
                email: email,
                password: password,
                password_confirmation: password_confirmation,
                role: role
            })
            .then(response => {
                console.log('Usuário cadastrado:', response.data);
                alert("Cadastro realizado com sucesso!");
                window.location.href = '/login';
            })
            .catch(error => {
                console.error('Erro ao cadastrar:', error.response?.data);
                
                if (error.response && error.response.data) {
                    let errors = error.response.data.errors;
                    let errorMsg = 'Erro no cadastro.';

                    if (errors) {
                        errorMsg = Object.values(errors).flat().join(' ');
                    } else if (error.response.data.message) {
                        errorMsg = error.response.data.message;
                    }

                    errorMessage.textContent = errorMsg;
                    errorMessage.classList.remove('hidden');
                } else {
                    errorMessage.textContent = "Erro desconhecido. Tente novamente.";
                    errorMessage.classList.remove('hidden');
                }
            });
        });
    </script>
</body>
</html>
