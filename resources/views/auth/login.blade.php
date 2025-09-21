<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EEEP Adolfo Ferreira de Sousa - Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <style>
    body {
      background: linear-gradient(-45deg, #1a1a2e, #16213e, #0f3460, #533483);
      background-size: 400% 400%;
      animation: gradientBG 10s ease infinite;
    }
    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
  </style>
</head>
<body class="text-gray-200 flex items-center justify-center min-h-screen">

  <section class="bg-gray-900 p-8 rounded-lg shadow-lg w-full max-w-md text-center">
    <h2 class="text-3xl font-bold mb-6">Login</h2>
    
    <!-- Mensagens de erro e sucesso -->
    <p id="error-message" class="bg-red-600 text-white p-3 rounded-lg mb-4 hidden"></p>
    <p id="success-message" class="bg-green-600 text-white p-3 rounded-lg mb-4 hidden"></p>
    
    <form id="loginForm">
      <div class="mb-4 text-left">
        <label for="email" class="block font-medium">E-mail</label>
        <input type="email" id="email" class="w-full px-4 py-2 mt-2 bg-gray-800 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required autofocus>
      </div>
      
      <div class="mb-4 text-left relative">
        <label for="password" class="block font-medium">Senha</label>
        <input type="password" id="password" class="w-full px-4 py-2 mt-2 bg-gray-800 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        <button type="button" onclick="togglePassword()" class="absolute right-3 top-10 text-gray-400 hover:text-gray-200">👁️</button>
      </div>

      <!-- Campo de tipo de usuário -->
      <div class="mb-4 text-left">
        <label for="role" class="block font-medium">Tipo de Usuário</label>
        <select id="role" class="w-full px-4 py-2 mt-2 bg-gray-800 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
          <option value="professor">Professor</option>
          <option value="gestor">Gestor</option>
        </select>
      </div>
      
      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition duration-300">Entrar</button>
    </form>
    
    <p class="mt-4 text-sm">Não tem uma conta? <a href="/register" class="text-blue-400 hover:underline">Cadastre-se</a></p>
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      try {
        const token = localStorage.getItem('token');
        const userData = localStorage.getItem('user');
        const currentPath = window.location.pathname;

        console.log('🚀 Página carregada. URL atual:', currentPath);
        console.log('🔍 Token encontrado:', !!token);
        
        // Se estamos na página de login e o usuário já está logado, redirecionar
        if (token && userData && currentPath === '/login') {
          const user = JSON.parse(userData);
          console.log('👤 Usuário já logado:', user.role);
          
          const redirectURL = user.role === 'professor' ? '/dashboard/professor' : '/dashboard/gestor';
          console.log('🔄 Redirecionando usuário logado para:', redirectURL);
          window.location.href = redirectURL;
          return; // Sair da função para evitar execução adicional
        }

        // Se não estamos na página de login e não há token, redirecionar para login
        if (!token && currentPath !== '/login' && currentPath !== '/register') {
          console.log('🔄 Usuário não logado, redirecionando para login');
          window.location.href = '/login';
        }
        
      } catch (error) {
        console.error('⚠ Erro ao processar dados do usuário:', error);
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        // Só redirecionar se não estivermos já na página de login
        if (window.location.pathname !== '/login') {
          window.location.href = '/login';
        }
      }
    });

    function togglePassword() {
      const passwordInput = document.getElementById('password');
      passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
    }

    document.getElementById('loginForm').addEventListener('submit', function(event) {
      event.preventDefault();

      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value.trim();
      const role = document.getElementById('role').value;
      const errorMessage = document.getElementById('error-message');
      const successMessage = document.getElementById('success-message');
      const submitButton = event.target.querySelector('button[type="submit"]');

      // Resetando mensagens
      errorMessage.classList.add('hidden');
      successMessage.classList.add('hidden');
      submitButton.disabled = true;
      submitButton.textContent = 'Entrando...';

      console.log('🔑 Tentando login com:', { email, role });

      // Configurar axios com base URL da API
      axios.post('http://127.0.0.1:8000/api/auth/login', { 
        email, 
        password, 
        role 
      })
      .then(response => {
        console.log('✅ Resposta da API:', response.data);

        if (!response.data.user || !response.data.user.role) {
          throw new Error('Dados do usuário estão incompletos.');
        }

        // Salvar dados no localStorage
        localStorage.setItem('token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.user));

        // Configurar header de autenticação para futuras requisições
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;

        // Definir URL de redirecionamento
        const redirectURL = response.data.user.role === 'professor' ? '/dashboard/professor' : '/dashboard/gestor';

        console.log('🔄 Redirecionando para:', redirectURL);

        // Exibir mensagem de sucesso
        successMessage.textContent = 'Login realizado com sucesso!';
        successMessage.classList.remove('hidden');

        // Redirecionar após delay
        setTimeout(() => {
          window.location.href = redirectURL;
        }, 1000);
      })
      .catch(error => {
        console.error('⚠ Erro ao fazer login:', error);

        let errorMsg = 'Erro: Verifique suas credenciais!';
        
        if (error.response) {
          // Erro da API
          if (error.response.data && error.response.data.message) {
            errorMsg = error.response.data.message;
          } else if (error.response.status === 401) {
            errorMsg = 'Email, senha ou tipo de usuário incorretos.';
          } else if (error.response.status === 422) {
            errorMsg = 'Dados de validação inválidos.';
          }
        } else if (error.request) {
          // Erro de rede
          errorMsg = 'Erro de conexão. Verifique sua internet ou se o servidor está rodando.';
        }

        errorMessage.textContent = errorMsg;
        errorMessage.classList.remove('hidden');

        // Reativar botão
        submitButton.disabled = false;
        submitButton.textContent = 'Entrar';
      });
    });
  </script>
</body>
</html>