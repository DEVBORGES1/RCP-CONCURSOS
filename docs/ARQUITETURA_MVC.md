# Arquitetura MVC - RCP Sistema de Concursos

## 📋 Visão Geral

Este documento descreve a arquitetura **Model-View-Controller (MVC)** implementada no sistema RCP Concursos, focada em princípios de **Orientação a Objetos** e **boas práticas de desenvolvimento**.

## 🏗️ Estrutura de Pastas

```
RCP-CONCURSOS/
├── app/                          # Código da aplicação
│   ├── Controllers/              # Controladores (Lógica de controle)
│   │   ├── AuthController.php    # Autenticação e registro
│   │   ├── DashboardController.php
│   │   └── HomeController.php
│   ├── Models/                   # Modelos (Dados e regras de negócio)
│   │   ├── Usuario.php
│   │   ├── Questao.php
│   │   ├── Simulado.php
│   │   ├── Edital.php
│   │   └── Progresso.php
│   ├── Views/                    # Views (Apresentação)
│   │   ├── layouts/             # Layouts principais
│   │   │   └── default.php
│   │   ├── pages/               # Páginas
│   │   │   ├── auth/
│   │   │   │   ├── login.php
│   │   │   │   └── register.php
│   │   │   ├── dashboard/
│   │   │   │   └── index.php
│   │   │   └── home/
│   │   │       └── index.php
│   │   └── components/          # Componentes reutilizáveis
│   ├── Services/                # Serviços e lógica de negócio
│   └── Core/                     # Classes base e utilitários
│       ├── BaseModel.php        # Modelo base com CRUD
│       ├── BaseController.php   # Controller base
│       ├── Router.php           # Sistema de rotas
│       └── Autoloader.php       # Autoloader PSR-4
├── config/                       # Configurações
│   ├── config.php               # Configurações gerais
│   └── database.php             # Gerenciador de conexão (Singleton)
├── public/                       # Arquivos públicos
├── vendor/                      # Dependências (Composer)
├── bootstrap.php                # Inicialização da aplicação
├── mvc_index.php                # Front Controller
└── composer.json                # Autoloading PSR-4
```

## 🔧 Componentes Principais

### 1. Controllers (app/Controllers/)

**Responsabilidade**: Processar requisições HTTP, orquestrar operações e preparar dados para a view.

#### Características:
- Herdam de `BaseController`
- Métodos públicos representam ações (index, create, store, etc.)
- Não fazem queries diretas no banco
- Delegam operações complexas para Models e Services

#### Exemplo:

```php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Usuario;

class AuthController extends BaseController
{
    public function login(): void
    {
        // Processar lógica de autenticação
        // Renderizar view
    }
}
```

### 2. Models (app/Models/)

**Responsabilidade**: Acesso a dados e regras de negócio básicas.

#### Características:
- Herdam de `BaseModel`
- Métodos para operações CRUD
- Não conhecem HTTP, apenas dados
- Reutilizáveis

#### Exemplo:

```php
namespace App\Models;

use App\Core\BaseModel;

class Usuario extends BaseModel
{
    protected string $table = 'usuarios';

    public function findByEmail(string $email): ?array
    {
        // Buscar usuário por email
    }
}
```

### 3. Views (app/Views/)

**Responsabilidade**: Apresentação visual dos dados.

#### Características:
- Apenas HTML, CSS e PHP para apresentação
- Sem lógica de negócio
- Recebem dados do Controller
- Organizadas por módulo

#### Estrutura:
```
Views/
├── layouts/      # Layouts principais
├── pages/        # Páginas individuais
└── components/   # Componentes reutilizáveis
```

### 4. Core (app/Core/)

Classes base que fornecem funcionalidades comuns:

#### BaseModel
- Operações CRUD padrão
- Queries dinâmicas
- Tratamento de erros

#### BaseController
- Renderização de views
- Redirecionamentos
- Flash messages
- Verificação de autenticação

#### Router
- Sistema de rotas simples
- Parâmetros dinâmicos
- Definição de rotas por método HTTP

### 5. Config (config/)

#### config.php
- Configurações centralizadas
- Ambiente (dev/prod)
- Credenciais de banco
- Settings da aplicação

#### database.php
- Padrão Singleton
- Conexão única ao banco
- Gerenciamento de PDO

## 🔄 Fluxo de Requisição

```
1. Usuário acessa URL
   ↓
2. Front Controller (mvc_index.php) recebe
   ↓
3. Bootstrap inicializa aplicação
   ↓
4. Router resolve a rota
   ↓
5. Controller apropriado é instanciado
   ↓
6. Método do Controller processa requisição
   ↓
7. Controller usa Models para buscar dados
   ↓
8. Controller renderiza View com dados
   ↓
9. Resposta HTML é enviada ao navegador
```

## 📝 Exemplo de Fluxo Completo

### Rota: GET /login

```php
// 1. Router encontra rota definida
$this->get('/login', 'AuthController@login');

// 2. Executa método no Controller
class AuthController extends BaseController
{
    public function login(): void
    {
        // 3. Verificar se já está logado
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }

        // 4. Preparar dados para view
        $data = [
            'titulo' => 'Login - Sistema de Concursos',
            'mensagem' => ''
        ];

        // 5. Renderizar view
        echo $this->view('auth/login', $data);
    }
}

// 6. View renderizada
// app/Views/pages/auth/login.php
```

### Rota: POST /login

```php
class AuthController extends BaseController
{
    public function processarLogin(): void
    {
        // 1. Obter dados do POST
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        // 2. Validar
        if (empty($email) || empty($senha)) {
            $this->setFlash('error', 'Preencha todos os campos.');
            $this->redirect('/login');
            return;
        }

        // 3. Usar Model para verificar credenciais
        $usuario = $this->usuarioModel->verificarCredenciais($email, $senha);

        // 4. Processar resultado
        if ($usuario) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $this->redirect('/dashboard');
        } else {
            $this->setFlash('error', 'Credenciais inválidas.');
            $this->redirect('/login');
        }
    }
}
```

## 🎯 Princípios de Design

### 1. Single Responsibility Principle (SRP)
- Cada classe tem uma única responsabilidade
- Controllers controlam fluxo
- Models acessam dados
- Views apresentam

### 2. Separation of Concerns
- Separação clara entre camadas
- Código organizado e manutenível
- Fácil de testar

### 3. DRY (Don't Repeat Yourself)
- Classes base reutilizáveis
- Componentes comuns em base
- Lógica centralizada

### 4. Dependency Injection
- Models injetados nos Controllers
- Facilita testes
- Baixo acoplamento

## 🔐 Segurança

### Implementado:
- ✅ Prepared Statements (PDO)
- ✅ Password Hashing (bcrypt)
- ✅ Session Management
- ✅ CSRF Protection (a implementar)
- ✅ XSS Prevention (htmlspecialchars)

## 📚 Padrões de Projeto Utilizados

### 1. Singleton (Database)
Garante uma única instância de conexão ao banco.

### 2. Front Controller
Todas as requisições passam por um ponto de entrada.

### 3. Repository Pattern (BaseModel)
Abstração de acesso a dados.

### 4. Template Method (BaseController/BaseModel)
Define estrutura comum para subclasses.

## 🚀 Como Usar

### 1. Criar uma nova funcionalidade

#### Passo 1: Criar Model
```php
// app/Models/MinhaEntidade.php
namespace App\Models;
use App\Core\BaseModel;

class MinhaEntidade extends BaseModel
{
    protected string $table = 'minha_tabela';
    
    // Métodos customizados aqui
}
```

#### Passo 2: Criar Controller
```php
// app/Controllers/MinhaEntidadeController.php
namespace App\Controllers;
use App\Core\BaseController;
use App\Models\MinhaEntidade;

class MinhaEntidadeController extends BaseController
{
    public function index(): void
    {
        // Lógica do controller
    }
}
```

#### Passo 3: Criar View
```php
// app/Views/pages/minha_entidade/index.php
<h1>Minha Entidade</h1>
// ... conteúdo
```

#### Passo 4: Definir Rota
```php
// app/Core/Router.php - método defineRoutes()
$this->get('/minha-entidade', 'MinhaEntidadeController@index');
```

## 🧪 Testes

### Estrutura sugerida:
```
tests/
├── Models/
├── Controllers/
└── Integration/
```

## 📖 Documentação de Classes

### BaseController
```php
/**
 * Métodos disponíveis:
 * - view($name, $data)        // Renderiza view
 * - json($data, $statusCode) // Retorna JSON
 * - redirect($url)              // Redireciona
 * - setFlash($type, $message)   // Define flash message
 * - isAuthenticated()           // Verifica autenticação
 * - requireAuth()              // Exige autenticação
 */
```

### BaseModel
```php
/**
 * Métodos disponíveis:
 * - find($id)                  // Busca por ID
 * - findAll($conditions)       // Busca múltiplos
 * - create($data)              // Cria registro
 * - update($id, $data)         // Atualiza
 * - delete($id)                // Deleta
 * - count($conditions)         // Conta registros
 */
```

## ✅ Vantagens da Arquitetura

1. **Manutenibilidade**: Código organizado e fácil de entender
2. **Escalabilidade**: Fácil adicionar novas funcionalidades
3. **Testabilidade**: Componentes isolados e testáveis
4. **Reusabilidade**: Classes base reutilizáveis
5. **Documentação**: Código auto-documentado
6. **Padrões**: Seguindo PSR-4 e boas práticas PHP

## 🔄 Migração do Código Antigo

O código antigo foi gradualmente migrado para esta arquitetura. Para usar o sistema MVC:

1. Renomeie `index.php` para `index_old.php`
2. Renomeie `mvc_index.php` para `index.php`
3. Acesse: `http://localhost/RCP-CONCURSOS/`

## 📞 Suporte

Para dúvidas sobre a arquitetura:
- Email: Bstech.ti@gmail.com
- Documentação: Este arquivo
- Código-fonte: `app/Core/` para classes base

---

**Desenvolvido com ❤️ seguindo princípios SOLID e boas práticas de OOP**

