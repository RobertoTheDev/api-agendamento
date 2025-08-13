# 📅 API de Agendamentos - PHP + Laravel
API para gerenciamento de agendamentos com operações CRUD, filtros e controle de acesso baseado em papéis (admin, gestor, professor).
Desenvolvida com PHP 8.3.11 e Laravel Framework 11.42.1.
O foco deste projeto é exclusivamente back-end, com toda a API já funcional e estruturada.

## 📌 Ajustes e melhorias
O projeto ainda está em desenvolvimento e as próximas atualizações serão voltadas para as seguintes tarefas:

 - [x] CRUD completo de agendamentos

 - [x] Controle de acesso por função (role-based)

 - [x] Filtros e listagem personalizada

 - [ ] Documentação Swagger/OpenAPI

 - [ ] Testes automatizados

 - [ ] Integração com notificações (e-mail/SMS)

## 💻 Pré-requisitos
Antes de começar, verifique se você possui:

- **PHP** 8.3.11

- **Composer** 2.x

- **Laravel** 11.42.1

- Banco de dados **MySQL** ou **PostgreSQL** configurado

- **Laravel Sanctum** para autenticação via token

- Compatível com **Windows**, **Linux** e **macOS**

## 🚀 Instalando API de Agendamentos
**1. Clone o repositório:**

```bash
git clone https://github.com/RobertoTheDev/api-agendamentos.git
cd api-agendamentos
```
**2. Instale as dependências via Composer:**

```bash
composer install
```
**3. Configure o arquivo .env:**

```env
APP_NAME=API_Agendamentos
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=senha
```
**4. Gere a chave da aplicação:**

```bash
php artisan key:generate
```
**5. Execute as migrações:**

```bash
php artisan migrate
```
**6. Inicie o servidor local:**

```bash
php artisan serve
```
## 📡 Endpoints da API:
**A API utiliza Laravel Sanctum para autenticação.
Endpoints marcados com 🔒 exigem token no header:**

```css

Authorization: Bearer {TOKEN}
🔑 Autenticação (/api/auth)
Método	Endpoint	Auth	Descrição
POST	/api/auth/register	❌	Registrar novo usuário
POST	/api/auth/login	❌	Login e retorno do token
POST	/api/auth/forgot-password	❌	Solicitar redefinição de senha
POST	/api/auth/logout	🔒	Logout
GET	/api/auth/user	🔒	Dados do usuário autenticado

👤 Usuários (/api/users)
Método	Endpoint	Auth	Descrição
GET	/api/users	🔒	Listar todos os usuários
POST	/api/users	🔒	Criar novo usuário
GET	/api/users/{id}	🔒	Exibir usuário
PUT	/api/users/{id}	🔒	Atualizar usuário
DELETE	/api/users/{id}	🔒	Remover usuário

📅 Agendamentos (/api/bookings)
Método	Endpoint	Auth	Descrição
GET	/api/bookings/my-bookings	🔒	Listar agendamentos do usuário logado
POST	/api/bookings	🔒	Criar agendamento
DELETE	/api/bookings/{id}	🔒	Cancelar agendamento
POST	/api/bookings/friendly-match	🔒	Criar amistoso (somente 1ª e 3ª semana do mês)

🛠 Gestão (/api/management) – Apenas Gestores
Método	Endpoint	Auth	Descrição
POST	/api/management/suspensions	🔒 (role:gestor)	Criar suspensão
DELETE	/api/management/suspensions/{id}	🔒 (role:gestor)	Remover suspensão
GET	/api/management/suspensions	🔒 (role:gestor)	Listar suspensões
```
## ☕ Exemplos curl: 

### Login: 
```bash

curl -X POST http://localhost:8000/api/auth/login \
-H "Content-Type: application/json" \
-d '{"email":"usuario@example.com","password":"senha123"}'
Listar 
```
### agendamentos do usuário: 
```bash

curl -X GET http://localhost:8000/api/bookings/my-bookings \
-H "Authorization: Bearer SEU_TOKEN_AQUI"

```
### Criar amistoso: 
```bash

curl -X POST http://localhost:8000/api/bookings/friendly-match \
-H "Authorization: Bearer SEU_TOKEN_AQUI" \
-H "Content-Type: application/json" \
-d '{"data":"2025-08-20 14:00:00","cliente_id":1}'
```
## 🛠 Tecnologias utilizadas: 
- **PHP** 8.3.11

- **Laravel** 11.42.1

- **Laravel Sanctum**

- **MySQL** ou **PostgreSQL**

- **Composer**

