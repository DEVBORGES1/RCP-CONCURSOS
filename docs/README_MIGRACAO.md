# 🔄 Guia de Migração para Arquitetura MVC

## 📌 Objetivo

Este documento explica como migrar do sistema antigo (código spaghetti) para a nova arquitetura MVC orientada a objetos.

## 🎯 Por que refatorar?

### Problemas do Código Antigo:
1. ❌ Lógica misturada: PHP, HTML e SQL no mesmo arquivo
2. ❌ Sem separação de responsabilidades
3. ❌ Dificuldade para testar
4. ❌ Difícil manutenção e escalabilidade
5. ❌ Violação de princípios SOLID

### Soluções da Arquitetura MVC:
1. ✅ Separação clara de responsabilidades
2. ✅ Código organizado e bem documentado
3. ✅ Fácil de testar (cada componente isolado)
4. ✅ Reutilização de código
5. ✅ Seguindo padrões PSR-4 e SOLID

## 🚀 Como Usar a Nova Arquitetura

### Passo 1: Instalar Dependências

```bash
cd C:\laragon\www\RCP-CONCURSOS
composer install
```

Se não tiver composer, baixe em: https://getcomposer.org/

### Passo 2: Ativar o Sistema MVC

1. **Backup do index.php antigo**:
```bash
cp index.php index_old.php
```

2. **Ativar o novo index**:
```bash
cp mvc_index.php index.php
```

3. **Acessar**: `http://localhost/RCP-CONCURSOS/`

## 📚 Como Trabalhar com a Arquitetura

### Criar uma Nova Funcionalidade

#### Exemplo: Sistema de Comentários

**1. Criar Model** (`app/Models/Comentario.php`):
```php
<?php
namespace App\Models;
use App\Core\BaseModel;

class Comentario extends BaseModel
{
    protected string $table = 'comentarios';
    
    public function findByQuestao(int $questaoId): array
    {
        return $this->findAll(['questao_id' => $questaoId]);
    }
}
```

**2. Criar Controller** (`app/Controllers/ComentarioController.php`):
```php
<?php
namespace App\Controllers;
use App\Core\BaseController;
use App\Models\Comentario;

class ComentarioController extends BaseController
{
    private Comentario $comentarioModel;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->comentarioModel = new Comentario();
    }
    
    public function store(): void
    {
        $texto = $_POST['texto'] ?? '';
        $questaoId = $_POST['questao_id'] ?? 0;
        
        if (empty($texto)) {
            $this->setFlash('error', 'Texto vazio!');
            $this->redirect('/questoes/' . $questaoId);
            return;
        }
        
        $this->comentarioModel->create([
            'texto' => $texto,
            'questao_id' => $questaoId,
            'usuario_id' => $this->getUserId()
        ]);
        
        $this->setFlash('success', 'Comentário adicionado!');
        $this->redirect('/questoes/' . $questaoId);
    }
}
```

**3. Definir Rota** (em `app/Core/Router.php`):
```php
public function defineRoutes(): void
{
    // ... rotas existentes
    
    // Nova rota
    $this->post('/comentarios', 'ComentarioController@store');
}
```

**4. Criar View** (`app/Views/pages/questoes/show.php`):
```php
<h1>Questão #<?= $questao['id'] ?></h1>
<p><?= $questao['enunciado'] ?></p>

<!-- Formulário de comentário -->
<form method="POST" action="/comentarios">
    <input type="hidden" name="questao_id" value="<?= $questao['id'] ?>">
    <textarea name="texto" required></textarea>
    <button type="submit">Adicionar Comentário</button>
</form>
```

## 🎓 Conceitos Importantes

### 1. Controllers
- **Responsabilidade**: Processar requisições HTTP
- **Não deve**: Fazer queries diretas no banco
- **Deve**: Usar Models para buscar dados
- **Deve**: Renderizar views com dados preparados

### 2. Models
- **Responsabilidade**: Acesso a dados
- **Não deve**: Conhecer HTTP ou views
- **Deve**: Fornecer métodos de busca/atualização
- **Deve**: Contar com regras de negócio básicas

### 3. Views
- **Responsabilidade**: Apresentação visual
- **Não deve**: Ter lógica complexa
- **Deve**: Receber dados do Controller
- **Deve**: Ser apenas HTML/CSS/PHP de apresentação

## 📝 Exemplos Práticos

### Exemplo 1: Listar Questões

**Controller:**
```php
public function index(): void
{
    $questoes = $this->questaoModel->findAll([], 'id DESC');
    
    $data = [
        'titulo' => 'Banco de Questões',
        'questoes' => $questoes
    ];
    
    echo $this->view('questoes/index', $data);
}
```

**View:**
```php
<h1><?= $titulo ?></h1>

<?php foreach ($questoes as $questao): ?>
    <div class="questao">
        <h3><?= $questao['enunciado'] ?></h3>
        <p>Alternativa correta: <?= $questao['alternativa_correta'] ?></p>
    </div>
<?php endforeach; ?>
```

### Exemplo 2: Criar Questão

**Controller:**
```php
public function store(): void
{
    $enunciado = $_POST['enunciado'] ?? '';
    $correta = $_POST['alternativa_correta'] ?? '';
    
    if (empty($enunciado) || empty($correta)) {
        $this->setFlash('error', 'Preencha todos os campos!');
        $this->redirect('/questoes');
        return;
    }
    
    $questao = $this->questaoModel->create([
        'enunciado' => $enunciado,
        'alternativa_correta' => $correta,
        // ... outros campos
    ]);
    
    if ($questao) {
        $this->setFlash('success', 'Questão criada!');
        $this->redirect('/questoes');
    }
}
```

## 🔄 Migração de Arquivos Antigos

### Arquivos a migrar:
1. ✅ `login.php` → `app/Controllers/AuthController.php`
2. ✅ `dashboard.php` → `app/Controllers/DashboardController.php`
3. ✅ `index.php` → `app/Controllers/HomeController.php`
4. 🔄 `questoes.php` → `app/Controllers/QuestaoController.php`
5. 🔄 `simulados.php` → `app/Controllers/SimuladoController.php`
6. 🔄 `editais.php` → `app/Controllers/EditalController.php`

## 🛠️ Ferramentas de Desenvolvimento

### Usar Namespaces Corretamente
```php
use App\Models\Usuario;  // Import do Model
use App\Core\BaseController;  // Import da classe base
```

### Debug e Logs
```php
// Log de erro
error_log("Erro ao processar: " . $e->getMessage());

// Debug (desenvolvimento)
var_dump($data);  // Ou use debugger
```

### Flash Messages
```php
// Definir mensagem
$this->setFlash('success', 'Operação realizada!');
$this->setFlash('error', 'Erro ao processar!');

// Exibir na view
<?php if (isset($_SESSION['flash'])): ?>
    <?php foreach ($_SESSION['flash'] as $type => $message): ?>
        <div class="alert alert-<?= $type ?>">
            <?= $message ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
```

## ✅ Checklist de Migração

- [x] Estrutura de pastas criada
- [x] Sistema de autoloading configurado
- [x] Classes base criadas (BaseModel, BaseController)
- [x] Router implementado
- [x] Models principais criados
- [x] Controllers iniciais criados
- [x] Views de exemplo criadas
- [x] Documentação escrita
- [ ] Migrar todas as funcionalidades
- [ ] Testar cada módulo
- [ ] Atualizar README.md
- [ ] Deploy em produção

## 🎓 Recursos para Aprender

### Padrões de Projeto:
- **Singleton**: `Config\Database`
- **Front Controller**: `bootstrap.php`
- **Repository**: `BaseModel`
- **Template Method**: `BaseController`

### Princípios SOLID:
- **S**ingle Responsibility: Cada classe uma responsabilidade
- **O**pen/Closed: Extensível sem modificar
- **L**iskov Substitution: Herança correta
- **I**nterface Segregation: Interfaces específicas
- **D**ependency Inversion: Injeção de dependências

## 📞 Suporte

- **Documentação**: Leia `ARQUITETURA_MVC.md`
- **Código**: Explore `app/Core/` para entender as classes base
- **Email**: Bstech.ti@gmail.com

---

**Transforme seu código de "vibe coding" para arquitetura profissional! 🚀**

