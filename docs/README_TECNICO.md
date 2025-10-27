# 📚 Documentação Técnica - RCP Sistema de Concursos (MVC)

## 📋 Visão Geral do Projeto

Projeto refatorado de **arquitetura procedural** para **arquitetura MVC orientada a objetos**, seguindo princípios SOLID e boas práticas de desenvolvimento.

---

## 🏗️ Arquitetura Implementada

### Padrão de Arquitetura
- **MVC** (Model-View-Controller)
- **PSR-4** (Autoloading)
- **Namespaces** (Organização de classes)
- **Singleton** (Conexão de banco)
- **Front Controller** (Ponto de entrada único)

### Camadas da Aplicação

```
┌─────────────────────────────────────┐
│         Front Controller            │
│         (mvc_index.php)             │
└─────────────┬───────────────────────┘
              │
              ▼
┌─────────────────────────────────────┐
│         Router                      │
│   (Roteamento de URLs)              │
└─────────────┬───────────────────────┘
              │
              ▼
┌─────────────────────────────────────┐
│       Controllers                   │
│  (Lógica de controle HTTP)          │
└─────────────┬───────────┬───────────┘
              │           │
              ▼           ▼
    ┌────────────┐  ┌────────────┐
    │   Models   │  │   Views    │
    │  (Dados)   │  │(UI/HTML)   │
    └────────────┘  └────────────┘
              │
              ▼
    ┌─────────────────────┐
    │     Database        │
    │   (PDO/MySQL)       │
    └─────────────────────┘
```

---

## 📁 Estrutura de Arquivos

### Core Classes (app/Core/)

#### BaseModel.php
Classe abstrata para todos os models. Fornece:
- `find($id)` - Buscar por ID
- `findAll($conditions)` - Buscar múltiplos
- `create($data)` - Criar registro
- `update($id, $data)` - Atualizar
- `delete($id)` - Deletar
- `count($conditions)` - Contar registros

**Uso:**
```php
class Usuario extends BaseModel
{
    protected string $table = 'usuarios';
}
```

#### BaseController.php
Classe abstrata para todos os controllers. Fornece:
- `view($name, $data)` - Renderizar view
- `json($data, $status)` - Retornar JSON
- `redirect($url)` - Redirecionar
- `setFlash($type, $message)` - Flash messages
- `isAuthenticated()` - Verificar login
- `requireAuth()` - Exigir autenticação

**Uso:**
```php
class AuthController extends BaseController
{
    public function login(): void
    {
        echo $this->view('auth/login', ['titulo' => 'Login']);
    }
}
```

#### Router.php
Sistema de rotas simples e eficiente.

**Definição de rotas:**
```php
$router = new Router();
$router->get('/usuarios', 'UsuarioController@index');
$router->post('/usuarios', 'UsuarioController@store');
```

**Rotas dinâmicas:**
```php
$router->get('/usuarios/:id', 'UsuarioController@show');
// GET /usuarios/123 → Controller recebe 123 como parâmetro
```

### Models (app/Models/)

#### Usuario.php
- `findByEmail($email)` - Buscar por email
- `criar($nome, $email, $senha)` - Criar usuário
- `verificarCredenciais($email, $senha)` - Login
- `obterDadosCompletos($id)` - Dados completos

#### Questao.php
- `findByEdital($editalId)` - Por edital
- `findByDisciplina($disciplinaId)` - Por disciplina
- `findRandom($conditions)` - Aleatória
- `buscarParaSimulado($qtd, $disciplinas)` - Para simulado
- `verificarResposta($id, $resposta)` - Verificar corretude

#### Simulado.php
- `findByUsuario($usuarioId)` - Listar do usuário
- `findConcluidos($usuarioId)` - Apenas concluídos
- `finalizar($id, $corretas, $total)` - Finalizar

#### Edital.php
- `findByUsuario($usuarioId)` - Listar do usuário
- `contarPorUsuario($usuarioId)` - Contar
- `ultimoEdital($usuarioId)` - Último cadastrado

#### Progresso.php
- `obterOuCriar($usuarioId)` - Buscar ou criar
- `adicionarPontos($usuarioId, $pontos)` - Adicionar pontos
- `atualizarStreak($usuarioId)` - Atualizar sequência
- `calcularNivel($pontos)` - Calcular nível

### Controllers (app/Controllers/)

#### AuthController.php
- `login()` - Exibir formulário
- `processarLogin()` - Autenticar
- `register()` - Exibir formulário
- `processarRegistro()` - Cadastrar
- `logout()` - Sair

#### DashboardController.php
- `index()` - Dashboard principal
- Acessível apenas com autenticação

#### HomeController.php
- `index()` - Homepage pública

### Views (app/Views/)

**Organização:**
```
Views/
├── layouts/
│   └── default.php      # Layout principal
├── pages/
│   ├── home/
│   │   └── index.php    # Homepage
│   ├── auth/
│   │   ├── login.php    # Login
│   │   └── register.php # Registro
│   └── dashboard/
│       └── index.php    # Dashboard
└── components/          # Componentes reutilizáveis
    (pronto para expansão)
```

### Config (config/)

#### config.php
Configurações centralizadas:
```php
return [
    'database' => [...],
    'app' => [...],
    'upload' => [...],
    'paths' => [...]
];
```

#### database.php
**Singleton Pattern** para conexão:
```php
$db = Database::getInstance();
$connection = $db->getConnection();
```

---

## 🔄 Fluxo de Execução

### Exemplo: Login

1. **Usuário acessa** `http://localhost/RCP-CONCURSOS/login`

2. **Front Controller** (`mvc_index.php`)
   - Recebe requisição
   - Carrega `bootstrap.php`

3. **Bootstrap** (`bootstrap.php`)
   - Inicia sessão
   - Registra autoloader
   - Inicializa Router

4. **Router** (`app/Core/Router.php`)
   - Encontra rota: `GET /login`
   - Mapeia para: `AuthController@login`

5. **Controller** (`app/Controllers/AuthController.php`)
   ```php
   public function login(): void
   {
       if ($this->isAuthenticated()) {
           $this->redirect('/dashboard');
           return;
       }
       
       echo $this->view('auth/login', ['mensagem' => '']);
   }
   ```

6. **View** (`app/Views/pages/auth/login.php`)
   - Renderiza HTML
   - Exibe formulário

7. **Resposta HTML** enviada ao navegador

---

## 🎯 Princípios Aplicados

### SOLID

#### S - Single Responsibility (Responsabilidade Única)
- **Controller**: Controla fluxo HTTP
- **Model**: Acessa dados
- **View**: Apresenta dados

#### O - Open/Closed (Aberto/Fechado)
- Classes base abstratas
- Extensível sem modificar

#### L - Liskov Substitution (Substituição de Liskov)
- Qualquer Model pode substituir BaseModel
- Qualquer Controller pode substituir BaseController

#### I - Interface Segregation (Segregação de Interface)
- Métodos específicos por classe
- Sem métodos desnecessários

#### D - Dependency Inversion (Inversão de Dependência)
- Controllers recebem Models
- Facilita testes e manutenção

### Design Patterns

#### Singleton
```php
// config/database.php
class Database
{
    private static ?Database $instance = null;
    
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

#### Front Controller
```php
// Todas as requisições passam por index.php
require_once __DIR__ . '/bootstrap.php';
```

#### Repository
```php
// BaseModel implementa padrão Repository
$usuarios = $usuarioModel->findAll();
```

---

## 🔐 Segurança Implementada

### 1. SQL Injection
- ✅ Prepared Statements (PDO)
- ✅ Parâmetros vinculados

### 2. XSS (Cross-Site Scripting)
- ✅ `htmlspecialchars()` em views
- ✅ Escape de dados do usuário

### 3. CSRF
- 🔄 A implementar
- Token de sessão

### 4. Senha
- ✅ Hash com `password_hash()`
- ✅ Verificação com `password_verify()`

### 5. Session
- ✅ Gerenciamento seguro
- ✅ Timeout configurável

---

## 📊 Estrutura de Banco

### Tabelas Principais

**usuarios** - Dados de usuários
**usuarios_progresso** - Gamificação
**conquistas** - Sistema de conquistas
**questoes** - Banco de questões
**respostas_usuario** - Histórico de respostas
**simulados** - Simulados criados
**editais** - Editais enviados
**disciplinas** - Disciplinas por edital

---

## 🧪 Como Testar

### 1. Teste de Autenticação
```
Acesse: http://localhost/RCP-CONCURSOS/login
```

### 2. Teste de Registro
```
Acesse: http://localhost/RCP-CONCURSOS/register
```

### 3. Teste de Dashboard
```
Login → Acessa: http://localhost/RCP-CONCURSOS/dashboard
```

---

## 📈 Métricas do Projeto

### Código
- **Total de Classes**: 15+
- **Total de Arquivos**: 30+
- **Linhas de Código**: ~2000+
- **Documentação**: 4 arquivos MD

### Estrutura
- **Controllers**: 3 implementados, 5+ planejados
- **Models**: 5 implementados
- **Views**: 5 implementadas, 20+ planejadas
- **Services**: Prontos para expansão

---

## 🚀 Próximos Passos

### Curto Prazo (1-2 semanas)
- [ ] Migrar `questoes.php` → `QuestaoController`
- [ ] Migrar `simulados.php` → `SimuladoController`
- [ ] Migrar `editais.php` → `EditalController`
- [ ] Adicionar testes unitários

### Médio Prazo (1 mês)
- [ ] Sistema de cache
- [ ] API REST
- [ ] Logs estruturados
- [ ] Métricas de performance

### Longo Prazo (3+ meses)
- [ ] Testes automatizados
- [ ] CI/CD
- [ ] Documentação API
- [ ] Performance optimization

---

## 📖 Referências

- PSR-4: https://www.php-fig.org/psr/psr-4/
- SOLID Principles
- MVC Pattern
- Repository Pattern
- Singleton Pattern

---

**Desenvolvido com ❤️ seguindo princípios de engenharia de software profissional**

