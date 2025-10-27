# 🎓 Guia Completo: Migração para Arquitetura MVC

## 📖 Índice
1. [Visão Geral](#visão-geral)
2. [O que foi Criado](#o-que-foi-criado)
3. [Como Ativar](#como-ativar)
4. [Exemplos Práticos](#exemplos-práticos)
5. [Perguntas Frequentes](#perguntas-frequentes)

---

## 🎯 Visão Geral

### Objetivo
Transformar o projeto atual de **"vibe coding"** (código spaghetti) para uma **arquitetura MVC profissional** com:
- ✅ Separação de responsabilidades
- ✅ Código orientado a objetos
- ✅ Padrões de projeto
- ✅ Documentação completa
- ✅ Fácil manutenção

---

## 📁 O que foi Criado

### 1. Estrutura de Pastas
```
app/
├── Controllers/        # Lógica de controle (mvc_index.php aponta aqui)
│   ├── AuthController.php       ✅ Criado
│   ├── DashboardController.php  ✅ Criado
│   └── HomeController.php        ✅ Criado
│
├── Models/            # Acesso a dados
│   ├── Usuario.php    ✅ Criado
│   ├── Questao.php    ✅ Criado
│   ├── Simulado.php   ✅ Criado
│   ├── Edital.php     ✅ Criado
│   └── Progresso.php  ✅ Criado
│
├── Views/             # Apresentação
│   ├── layouts/default.php     ✅ Criado
│   └── pages/
│       ├── auth/
│       │   ├── login.php       ✅ Criado
│       │   └── register.php    ✅ Criado
│       ├── dashboard/index.php ✅ Criado
│       └── home/index.php      ✅ Criado
│
├── Core/              # Classes base
│   ├── BaseModel.php      ✅ Criado
│   ├── BaseController.php ✅ Criado
│   ├── Router.php         ✅ Criado
│   └── Autoloader.php     ✅ Criado
│
└── Services/          # Lógica de negócio (vazio, pronta para expansão)

config/
├── config.php      ✅ Criado (configurações centralizadas)
└── database.php    ✅ Criado (Singleton para PDO)
```

### 2. Arquivos de Configuração
- ✅ `composer.json` - Autoloading PSR-4
- ✅ `bootstrap.php` - Inicialização da aplicação
- ✅ `mvc_index.php` - Front Controller (novo index)
- ✅ `.htaccess` - Configurações Apache
- ✅ `ARQUITETURA_MVC.md` - Documentação técnica
- ✅ `README_MIGRACAO.md` - Guia de migração

---

## 🚀 Como Ativar

### Opção 1: Usar o Sistema MVC (Recomendado)

**1. Fazer backup:**
```bash
# Renomear arquivos antigos
mv index.php index_old.php
mv bootstrap.php bootstrap_old.php
```

**2. Ativar MVC:**
```bash
# Renomear novo index
mv mvc_index.php index.php
```

**3. Acessar:**
```
http://localhost/RCP-CONCURSOS/
```

### Opção 2: Manter Ambas Versões (Desenvolvimento)

1. **Sistema Antigo**: `http://localhost/RCP-CONCURSOS/index_old.php`
2. **Sistema MVC**: `http://localhost/RCP-CONCURSOS/mvc_index.php`

---

## 💡 Exemplos Práticos

### Exemplo 1: Criar Login Funcional

**Já implementado!** Veja:
- Controller: `app/Controllers/AuthController.php`
- View: `app/Views/pages/auth/login.php`
- Model: `app/Models/Usuario.php`

### Exemplo 2: Adicionar Nova Funcionalidade

Vamos criar um sistema de **Perfil**:

#### Passo 1: Criar Model
```php
// app/Models/Perfil.php
<?php
namespace App\Models;
use App\Core\BaseModel;

class Perfil extends BaseModel
{
    protected string $table = 'usuarios';
    
    public function atualizarPerfil(int $usuarioId, array $dados): bool
    {
        return $this->update($usuarioId, $dados);
    }
}
```

#### Passo 2: Criar Controller
```php
// app/Controllers/PerfilController.php
<?php
namespace App\Controllers;
use App\Core\BaseController;
use App\Models\Perfil;

class PerfilController extends BaseController
{
    private Perfil $perfilModel;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->perfilModel = new Perfil();
    }
    
    public function index(): void
    {
        $userId = $this->getUserId();
        $usuario = $this->perfilModel->find($userId);
        
        $data = [
            'titulo' => 'Meu Perfil',
            'usuario' => $usuario
        ];
        
        echo $this->view('perfil/index', $data);
    }
    
    public function atualizar(): void
    {
        $nome = $_POST['nome'] ?? '';
        
        if (empty($nome)) {
            $this->setFlash('error', 'Nome obrigatório!');
            $this->redirect('/perfil');
            return;
        }
        
        $this->perfilModel->atualizarPerfil(
            $this->getUserId(),
            ['nome' => $nome]
        );
        
        $this->setFlash('success', 'Perfil atualizado!');
        $this->redirect('/perfil');
    }
}
```

#### Passo 3: Criar View
```php
// app/Views/pages/perfil/index.php
<h1>Meu Perfil</h1>

<form method="POST" action="/perfil/atualizar">
    <label>Nome: <input name="nome" value="<?= $usuario['nome'] ?>"></label>
    <button type="submit">Atualizar</button>
</form>
```

#### Passo 4: Definir Rotas
```php
// app/Core/Router.php - método defineRoutes()
$this->get('/perfil', 'PerfilController@index');
$this->post('/perfil/atualizar', 'PerfilController@atualizar');
```

---

## ❓ Perguntas Frequentes

### 1. Como funciona o autoloading?
**Resposta**: Usa namespace PSR-4. Todas as classes em `app/` são carregadas automaticamente.

```php
use App\Models\Usuario;  // App\ = app/
use App\Controllers\AuthController;
```

### 2. O que é o BaseModel?
**Resposta**: Classe base que fornece operações CRUD para todos os models.

```php
class MeuModel extends BaseModel
{
    protected string $table = 'minha_tabela';
    
    // Já tem: find(), findAll(), create(), update(), delete()
}
```

### 3. Como funciona o Router?
**Resposta**: Sistema de rotas simples que mapeia URLs para métodos de Controllers.

```php
// Definir rota
$this->get('/usuarios', 'UsuarioController@index');

// Acessar
GET /usuarios → UsuarioController::index()
```

### 4. Posso misturar código antigo e novo?
**Resposta**: Sim, gradualmente. O sistema antigo continua funcionando normalmente.

### 5. Como adiciono autenticação em uma rota?
**Resposta**: No construtor do Controller:

```php
public function __construct()
{
    $this->requireAuth();  // Exige login
}
```

### 6. Como redireciono após uma ação?
**Resposta**: No Controller:

```php
$this->redirect('/dashboard');
```

### 7. Como mostro mensagens ao usuário?
**Resposta**: Flash messages:

```php
// No Controller
$this->setFlash('success', 'Operação realizada!');
$this->setFlash('error', 'Erro ao processar!');

// Na View
<?php if (isset($_SESSION['flash'])): ?>
    <?php foreach ($_SESSION['flash'] as $type => $message): ?>
        <div class="alert alert-<?= $type ?>">
            <?= $message ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
```

### 8. Como acesso dados do banco?
**Resposta**: Através dos Models:

```php
// No Controller
$usuarios = $this->usuarioModel->findAll();
$usuario = $this->usuarioModel->find($id);
```

---

## 🎓 Próximos Passos

### Para Continuar a Migração:

1. **Migrar funcionalidades restantes:**
   - [ ] QuestoesController
   - [ ] SimuladoController
   - [ ] EditalController
   - [ ] VideoaulaController

2. **Expandir Services:**
   - [ ] Refatorar Gamificacao.php
   - [ ] Criar GeradorCronograma em Service
   - [ ] Criar AnalisadorEdital em Service

3. **Melhorias:**
   - [ ] Sistema de logs
   - [ ] Cache layer
   - [ ] Validação de formulários
   - [ ] Testes automatizados

---

## 📚 Recursos Adicionais

### Documentação:
- `ARQUITETURA_MVC.md` - Documentação técnica completa
- `README_MIGRACAO.md` - Guia de uso e exemplos

### Código para Estudo:
- `app/Core/BaseModel.php` - Entenda como funciona CRUD
- `app/Core/BaseController.php` - Entenda como funciona renderização
- `app/Core/Router.php` - Entenda como funcionam as rotas

---

## ✨ Vantagens da Nova Arquitetura

| Antes | Depois |
|-------|--------|
| ❌ Tudo misturado | ✅ Separado por responsabilidade |
| ❌ Difícil manter | ✅ Fácil manter |
| ❌ Difícil testar | ✅ Fácil testar |
| ❌ Código repetido | ✅ Código reutilizável |
| ❌ Sem padrões | ✅ Padrões estabelecidos |

---

**🎉 Parabéns! Agora você tem uma arquitetura profissional e orientada a objetos!**

*Transforme seus projetos de "vibe coding" para código de qualidade empresarial! 🚀*

