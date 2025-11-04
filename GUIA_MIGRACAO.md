# 🔄 Guia de Migração: Procedural para POO

Este guia mostra como migrar gradualmente o código existente para POO.

---

## 📋 Índice

1. [Como Migrar](#como-migrar)
2. [Exemplos de Migração](#exemplos-de-migração)
3. [Checklist](#checklist)
4. [FAQ](#faq)

---

## 🔀 Como Migrar

### Passo 1: Atualizar arquivos para usar Database Singleton

**Antes**:
```php
// Arquivo: dashboard.php
require 'conexao.php';
require 'classes/Gamificacao.php';

$gamificacao = new Gamificacao($pdo);
```

**Depois**:
```php
// Arquivo: dashboard.php
require 'classes/Database.php';
require 'classes/GamificacaoRefatorada.php';

$gamificacao = new GamificacaoRefatorada(); // Não precisa mais passar $pdo
```

### Passo 2: Substituir Gamificacao por GamificacaoRefatorada

**Procurar e substituir em TODOS os arquivos**:

```bash
# Buscar ocorrências
grep -r "new Gamificacao" .

# Substituir
sed -i "s/new Gamificacao(\$pdo)/new GamificacaoRefatorada()/g" *.php
```

### Passo 3: Migrar criação de questões

**Antes**:
```php
$sql = "INSERT INTO questoes (...) VALUES (...)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$enunciado, ...]);
```

**Depois**:
```php
require_once 'classes/Questao.php';

$questao = new Questao();
$alternativas = [
    'a' => $_POST['a'],
    'b' => $_POST['b'],
    'c' => $_POST['c'],
    'd' => $_POST['d'],
    'e' => $_POST['e']
];

$questao->create($edital_id, $disciplina_id, $enunciado, $alternativas, $correta);
```

---

## 📝 Exemplos de Migração

### Exemplo 1: dashboard.php

**Código Atual**:
```php
<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$gamificacao = new Gamificacao($pdo);
$gamificacao->atualizarStreak($_SESSION["usuario_id"]);
$gamificacao->verificarTodasConquistas($_SESSION["usuario_id"]);

$dados_usuario = $gamificacao->obterDadosUsuario($_SESSION["usuario_id"]);
$conquistas = $gamificacao->obterConquistasUsuario($_SESSION["usuario_id"]);
```

**Código Migrado**:
```php
<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/GamificacaoRefatorada.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$gamificacao = new GamificacaoRefatorada();
$gamificacao->atualizarStreak($_SESSION["usuario_id"]);
$gamificacao->verificarTodasConquistas($_SESSION["usuario_id"]);

$dados_usuario = $gamificacao->obterDadosUsuario($_SESSION["usuario_id"]);
$conquistas = $gamificacao->obterConquistasUsuario($_SESSION["usuario_id"]);
```

### Exemplo 2: questoes.php

**Código Atual**:
```php
<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

// Adicionar questão
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar_questao'])) {
    $sql = "INSERT INTO questoes (edital_id, disciplina_id, enunciado, alternativa_a, 
            alternativa_b, alternativa_c, alternativa_d, alternativa_e, alternativa_correta)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$edital_id, $disciplina_id, $enunciado, $a, $b, $c, $d, $e, $correta]);
    
    $mensagem = "Questão adicionada com sucesso!";
}

// Responder questão
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['responder_questao'])) {
    $sql = "SELECT alternativa_correta FROM questoes WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$questao_id]);
    $resposta_correta = $stmt->fetchColumn();
    
    $acertou = ($resposta == $resposta_correta) ? 1 : 0;
    $pontos = $acertou ? 10 : 0;
    
    $sql = "INSERT INTO respostas_usuario (usuario_id, questao_id, resposta, correta, pontos_ganhos)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION["usuario_id"], $questao_id, $resposta, $acertou, $pontos]);
    
    $gamificacao = new Gamificacao($pdo);
    $gamificacao->adicionarPontos($_SESSION["usuario_id"], $pontos, 'questao');
}
```

**Código Migrado**:
```php
<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/GamificacaoRefatorada.php';
require_once 'classes/Questao.php';

// Adicionar questão
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar_questao'])) {
    $questao = new Questao();
    $alternativas = [
        'a' => $_POST['a'],
        'b' => $_POST['b'],
        'c' => $_POST['c'],
        'd' => $_POST['d'],
        'e' => $_POST['e']
    ];
    
    if ($questao->create($_POST['edital_id'], $_POST['disciplina_id'], 
                        $_POST['enunciado'], $alternativas, $_POST['correta'])) {
        $mensagem = "Questão adicionada com sucesso!";
    }
}

// Responder questão
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['responder_questao'])) {
    $questao = new Questao($_POST['questao_id']);
    $resultado = $questao->registrarResposta($_SESSION["usuario_id"], $_POST['resposta']);
    
    if ($resultado['acertou']) {
        $gamificacao = new GamificacaoRefatorada();
        $gamificacao->adicionarPontos($_SESSION["usuario_id"], $resultado['pontos'], 'questao');
        $mensagem = "Parabéns! Você acertou e ganhou " . $resultado['pontos'] . " pontos!";
    } else {
        $mensagem = "Que pena! A resposta correta era " . $resultado['resposta_correta'];
    }
}
```

### Exemplo 3: simulados.php

**Código Atual**:
```php
<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

// Criar simulado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['criar_simulado'])) {
    $sql = "INSERT INTO simulados (usuario_id, nome, questoes_total) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION["usuario_id"], $nome_simulado, $quantidade_questoes]);
    $simulado_id = $pdo->lastInsertId();

    // Selecionar questões
    $where_clause = "";
    $params = [];
    if ($disciplina_id) {
        $where_clause = "WHERE disciplina_id = ?";
        $params[] = $disciplina_id;
    }
    
    $sql = "SELECT * FROM questoes $where_clause ORDER BY RAND() LIMIT " . $quantidade_questoes;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $questoes = $stmt->fetchAll();

    foreach ($questoes as $questao) {
        $sql = "INSERT INTO simulados_questoes (simulado_id, questao_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$simulado_id, $questao['id']]);
    }

    header("Location: simulado.php?id=" . $simulado_id);
}
```

**Código Migrado**:
```php
<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/GamificacaoRefatorada.php';
require_once 'classes/Simulado.php';

// Criar simulado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['criar_simulado'])) {
    $simulado = new Simulado();
    
    $filtros = [];
    if (!empty($_POST['disciplina_id'])) {
        $filtros['disciplina_id'] = $_POST['disciplina_id'];
    }
    
    if ($simulado->create($_SESSION["usuario_id"], $_POST['nome_simulado'], 
                          $_POST['quantidade_questoes'], $filtros)) {
        header("Location: simulado.php?id=" . $simulado->getId());
    }
}
```

### Exemplo 4: login.php

**Código Atual**:
```php
<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha_hash'])) {
        $_SESSION["usuario_id"] = $user["id"];
        $gamificacao = new Gamificacao($pdo);
        $gamificacao->garantirProgressoUsuario($user["id"]);
        $gamificacao->atualizarStreak($user["id"]);
        header("Location: dashboard.php");
    }
}
```

**Código Migrado**:
```php
<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/GamificacaoRefatorada.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = new User();
    
    if ($user->authenticate($_POST['email'], $_POST['senha'])) {
        $_SESSION["usuario_id"] = $user->getId();
        
        $gamificacao = new GamificacaoRefatorada();
        $gamificacao->garantirProgressoUsuario($user->getId());
        $gamificacao->atualizarStreak($user->getId());
        
        header("Location: dashboard.php");
    }
}
```

### Exemplo 5: register.php

**Código Atual**:
```php
<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar email
    $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    
    if ($stmt->fetchColumn() > 0) {
        $erro = "Este email já está cadastrado.";
    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$nome, $email, $senha_hash])) {
            $usuario_id = $pdo->lastInsertId();
            $gamificacao = new Gamificacao($pdo);
            $gamificacao->garantirProgressoUsuario($usuario_id);
            $gamificacao->adicionarPontos($usuario_id, 50, 'primeiro_acesso');
            $_SESSION["usuario_id"] = $usuario_id;
            header("refresh:2;url=dashboard.php");
        }
    }
}
```

**Código Migrado**:
```php
<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/GamificacaoRefatorada.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = new User();
    
    // Validar dados básicos
    if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['senha'])) {
        $erro = "Todos os campos são obrigatórios.";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido.";
    } elseif ($_POST['senha'] !== $_POST['confirmar_senha']) {
        $erro = "As senhas não coincidem.";
    } elseif ($user->create($_POST['nome'], $_POST['email'], $_POST['senha'])) {
        // Sucesso
        $gamificacao = new GamificacaoRefatorada();
        $gamificacao->adicionarPontos($user->getId(), 50, 'primeiro_acesso');
        
        $_SESSION["usuario_id"] = $user->getId();
        header("refresh:2;url=dashboard.php");
    } else {
        $erro = "Erro ao cadastrar. Email já existente.";
    }
}
```

---

## ✅ Checklist de Migração

### Fase 1: Preparação
- [x] Criar classes Database, User, Questao, Simulado
- [x] Criar GamificacaoRefatorada
- [x] Documentar todas as classes
- [ ] Testar classes isoladamente

### Fase 2: Migração Gradual
- [ ] Atualizar dashboard.php
- [ ] Atualizar login.php
- [ ] Atualizar register.php
- [ ] Atualizar questoes.php
- [ ] Atualizar simulados.php
- [ ] Atualizar simulado.php
- [ ] Atualizar perfil.php
- [ ] Atualizar videoaulas.php
- [ ] Atualizar editais.php

### Fase 3: Limpeza
- [ ] Remover arquivo conexao.php antigo
- [ ] Remover classe Gamificacao antiga
- [ ] Remover arquivos de correção temporários
- [ ] Atualizar README.md

### Fase 4: Testes
- [ ] Testar login/logout
- [ ] Testar cadastro de questões
- [ ] Testar criação de simulados
- [ ] Testar correção de simulados
- [ ] Testar sistema de gamificação
- [ ] Testar rankings

---

## ❓ FAQ

### P: Preciso migrar tudo de uma vez?

**R:** Não! A migração pode ser gradual. As classes novas funcionam independentemente do código antigo.

### P: O que fazer com arquivos antigos?

**R:** Mantenha-os até confirmar que tudo funciona. Depois delete-os.

### P: Posso misturar código procedural com POO?

**R:** Temporariamente sim, mas evite. O ideal é usar apenas POO.

### P: E se eu encontrar um bug?

**R:** Adicione no `error_log()` e corrija. A nova arquitetura facilita debug.

### P: Como testar as novas classes?

**R:** Crie arquivos de teste temporários:

```php
// teste_classes.php
<?php
require_once 'classes/Database.php';
require_once 'classes/User.php';

// Teste User
$user = new User();
$resultado = $user->create("Teste", "teste@teste.com", "senha123");
var_dump($resultado);
```

---

## 🛠️ Ferramentas Úteis

### Buscar ocorrências

```bash
# Buscar require 'conexao.php'
grep -r "require 'conexao.php'" .

# Buscar new Gamificacao($pdo)
grep -r "new Gamificacao(\$pdo)" .
```

### Substituir em todos os arquivos

```bash
# Atualizar imports
sed -i "s/require 'conexao.php'/require_once 'classes\/Database.php'/g" *.php

# Atualizar Gamificacao
sed -i "s/new Gamificacao(\$pdo)/new GamificacaoRefatorada()/g" *.php
```

---

**📅 Última Atualização**: Hoje  
**👤 Autor**: Sistema RCP  
**📝 Versão**: 1.0

