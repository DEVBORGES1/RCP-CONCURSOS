# 📚 Documentação do Sistema RCP-CONCURSOS - Versão 2.0 POO

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Arquitetura POO](#arquitetura-poo)
3. [Classes Implementadas](#classes-implementadas)
4. [Migração do Código](#migração-do-código)
5. [Exemplos de Uso](#exemplos-de-uso)
6. [Boas Práticas](#boas-práticas)
7. [Diagrama de Classes](#diagrama-de-classes)

---

## 🎯 Visão Geral

O sistema foi completamente refatorado para utilizar **Programação Orientada a Objetos (POO)** seguindo os princípios SOLID e padrões de design modernos.

### Principais Melhorias

- ✅ **Separação de Responsabilidades**: Cada classe tem uma única responsabilidade
- ✅ **Singleton Pattern**: Conexão com banco de dados gerenciada centralmente
- ✅ **Reutilização de Código**: Classes podem ser facilmente estendidas
- ✅ **Manutenibilidade**: Código mais limpo e organizado
- ✅ **Testabilidade**: Classes podem ser testadas isoladamente
- ✅ **Segurança**: Prepared statements em todas as consultas

---

## 🏗️ Arquitetura POO

### Estrutura de Diretórios

```
RCP-CONCURSOS/
├── classes/
│   ├── Database.php              # Singleton para conexão
│   ├── User.php                  # Gestão de usuários
│   ├── Questao.php               # Gestão de questões
│   ├── Simulado.php              # Gestão de simulados
│   ├── GamificacaoRefatorada.php # Sistema de gamificação
│   ├── AnalisadorEdital.php      # Análise de editais
│   └── GeradorCronograma.php     # Geração de cronogramas
├── css/
├── uploads/
└── *.php                         # Arquivos de interface
```

---

## 📦 Classes Implementadas

### 1. Database (Singleton)

**Responsabilidade**: Gerenciar conexão única com banco de dados

**Características**:
- Padrão Singleton para garantir uma única instância
- Configuração centralizada
- Tratamento de erros robusto

**Uso**:
```php
require_once 'classes/Database.php';

// Obter instância
$db = Database::getInstance();
$pdo = $db->getConnection();

// Não é possível criar novas instâncias
// $db2 = new Database(); // ERRO!
```

**Métodos**:
- `getInstance()`: Obtém instância única
- `getConnection()`: Retorna conexão PDO

---

### 2. User (Gestão de Usuários)

**Responsabilidade**: Autenticação e gestão de dados de usuários

**Características**:
- CRUD completo de usuários
- Autenticação segura
- Validações integradas

**Uso**:
```php
require_once 'classes/User.php';

// Criar novo usuário
$user = new User();
if ($user->create("João Silva", "joao@email.com", "senha123")) {
    echo "Usuário criado com ID: " . $user->getId();
}

// Autenticar usuário
$user = new User();
if ($user->authenticate("joao@email.com", "senha123")) {
    $_SESSION['user_id'] = $user->getId();
    echo "Olá, " . $user->getNome();
}

// Carregar usuário existente
$user = new User($user_id);
echo $user->getNome();
echo $user->getEmail();

// Atualizar dados
$user->update([
    'nome' => 'João Santos',
    'email' => 'novoemail@email.com'
]);
```

**Métodos**:
- `create($nome, $email, $senha)`: Cria novo usuário
- `authenticate($email, $senha)`: Autentica usuário
- `loadById($id)`: Carrega por ID
- `loadByEmail($email)`: Carrega por email
- `update($data)`: Atualiza dados
- `emailExists($email)`: Verifica email
- `getId()`, `getNome()`, `getEmail()`: Getters
- `getData()`: Retorna todos os dados

---

### 3. Questao (Gestão de Questões)

**Responsabilidade**: CRUD de questões e verificação de respostas

**Características**:
- Criação e gerenciamento de questões
- Verificação automática de respostas
- Estatísticas por usuário

**Uso**:
```php
require_once 'classes/Questao.php';

// Criar questão
$questao = new Questao();
$alternativas = [
    'a' => 'Alternativa A',
    'b' => 'Alternativa B',
    'c' => 'Alternativa C',
    'd' => 'Alternativa D',
    'e' => 'Alternativa E'
];

if ($questao->create($edital_id, $disciplina_id, $enunciado, $alternativas, 'A')) {
    echo "Questão criada!";
}

// Carregar questão
$questao = new Questao($questao_id);

// Verificar resposta
if ($questao->verificarResposta('A')) {
    echo "Resposta correta!";
}

// Registrar resposta
$resultado = $questao->registrarResposta($usuario_id, 'A');
if ($resultado['acertou']) {
    echo "Você ganhou " . $resultado['pontos'] . " pontos!";
}

// Obter questões aleatórias
$questoes = Questao::getRandom(10, ['edital_id' => $edital_id]);

// Estatísticas
$stats = Questao::getEstatisticas($usuario_id);
echo "Total: " . $stats['total'];
echo "Respondidas: " . $stats['respondidas'];
echo "Taxa de acerto: " . $stats['percentual_acerto'] . "%";
```

**Métodos**:
- `create($edital_id, $disciplina_id, $enunciado, $alternativas, $correta)`: Cria questão
- `verificarResposta($resposta)`: Verifica se resposta está correta
- `registrarResposta($usuario_id, $resposta)`: Registra resposta
- `getRandom($limite, $filtros)`: Questões aleatórias (estático)
- `getEstatisticas($usuario_id, $filtros)`: Estatísticas (estático)

---

### 4. Simulado (Gestão de Simulados)

**Responsabilidade**: Criação e execução de simulados

**Características**:
- Geração automática de simulados
- Correção automática
- Gerenciamento de resultados

**Uso**:
```php
require_once 'classes/Simulado.php';

// Criar simulado
$simulado = new Simulado();
if ($simulado->create($usuario_id, "Simulado Teste", 20, ['edital_id' => $edital_id])) {
    echo "Simulado criado!";
}

// Carregar simulado
$simulado = new Simulado($simulado_id);

// Obter questões para exibição
$dados = $simulado->getDataForDisplay();

// Finalizar simulado
$respostas = [
    'questao_1' => 'A',
    'questao_2' => 'B',
    // ...
];
$resultado = $simulado->finalizar($respostas, 45); // 45 minutos
echo "Acertos: " . $resultado['acertos'] . "/" . $resultado['total'];
echo "Pontos: " . $resultado['pontos'];

// Obter resultado
$resultado = $simulado->getResultData();

// Listar simulados do usuário
$simulados = Simulado::listByUser($usuario_id);
$simulados_finalizados = Simulado::listByUser($usuario_id, ['finalizado' => true]);
```

**Métodos**:
- `create($usuario_id, $nome, $quantidade, $filtros)`: Cria simulado
- `finalizar($respostas, $tempo_gasto)`: Finaliza e corrige
- `getDataForDisplay()`: Dados para exibição (sem respostas)
- `getResultData()`: Resultado completo
- `listByUser($usuario_id, $filtros)`: Lista simulado (estático)

---

### 5. GamificacaoRefatorada

**Responsabilidade**: Sistema completo de gamificação

**Características**:
- Pontos e níveis
- Conquistas
- Streak (sequência de dias)
- Rankings

**Uso**:
```php
require_once 'classes/GamificacaoRefatorada.php';

$gamificacao = new GamificacaoRefatorada();

// Adicionar pontos
$gamificacao->adicionarPontos($usuario_id, 10, 'questao');

// Garantir progresso (inicialização)
$gamificacao->garantirProgressoUsuario($usuario_id);

// Atualizar streak
$gamificacao->atualizarStreak($usuario_id);

// Obter dados do usuário
$dados = $gamificacao->obterDadosUsuario($usuario_id);
echo "Nível: " . $dados['nivel'];
echo "Pontos: " . $dados['pontos_total'];
echo "Streak: " . $dados['streak_dias'] . " dias";

// Conquistas
$conquistas = $gamificacao->obterConquistasUsuario($usuario_id);

// Ranking
$ranking = $gamificacao->obterRankingMensal(10);
$posicao = $gamificacao->obterPosicaoUsuario($usuario_id);

// Verificar todas as conquistas
$gamificacao->verificarTodasConquistas($usuario_id);
```

**Métodos**:
- `adicionarPontos($usuario_id, $pontos, $tipo)`: Adiciona pontos
- `garantirProgressoUsuario($usuario_id)`: Inicializa progresso
- `atualizarStreak($usuario_id)`: Atualiza streak
- `obterDadosUsuario($usuario_id)`: Dados completos
- `obterConquistasUsuario($usuario_id)`: Conquistas
- `obterRankingMensal($limite)`: Ranking mensal
- `obterPosicaoUsuario($usuario_id)`: Posição no ranking

---

## 🔄 Migração do Código

### Antes (Procedural)

```php
// conexao.php
$pdo = new PDO(...);

// dashboard.php
require 'conexao.php';
require 'classes/Gamificacao.php';
$gamificacao = new Gamificacao($pdo);
$dados = $gamificacao->obterDadosUsuario($usuario_id);

// Criar questão
$sql = "INSERT INTO questoes (...) VALUES (...)";
$stmt = $pdo->prepare($sql);
$stmt->execute([...]);
```

### Depois (POO)

```php
// Não precisa mais de conexao.php!
require_once 'classes/Database.php';
require_once 'classes/GamificacaoRefatorada.php';
require_once 'classes/Questao.php';

// Dashboard com POO
$gamificacao = new GamificacaoRefatorada();
$dados = $gamificacao->obterDadosUsuario($usuario_id);

// Criar questão com POO
$questao = new Questao();
$questao->create($edital_id, $disciplina_id, $enunciado, $alternativas, 'A');
```

---

## 📖 Exemplos de Uso Completos

### Exemplo 1: Fluxo Completo de Resposta de Questão

```php
<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Questao.php';
require_once 'classes/GamificacaoRefatorada.php';

// Carregar questão
$questao = new Questao($_POST['questao_id']);

// Processar resposta
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $resposta_usuario = $_POST['resposta'];
    
    // Registrar resposta
    $resultado = $questao->registrarResposta($_SESSION['usuario_id'], $resposta_usuario);
    
    // Adicionar pontos se correto
    if ($resultado['acertou']) {
        $gamificacao = new GamificacaoRefatorada();
        $gamificacao->adicionarPontos($_SESSION['usuario_id'], $resultado['pontos'], 'questao');
        
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Parabéns! Você acertou!',
            'pontos' => $resultado['pontos']
        ]);
    } else {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Resposta incorreta. A correta era ' . $resultado['resposta_correta']
        ]);
    }
}
```

### Exemplo 2: Criação e Execução de Simulado

```php
<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Simulado.php';
require_once 'classes/GamificacaoRefatorada.php';

// Criar simulado
if (isset($_POST['criar_simulado'])) {
    $simulado = new Simulado();
    
    if ($simulado->create(
        $_SESSION['usuario_id'],
        $_POST['nome'],
        $_POST['quantidade_questoes'],
        ['disciplina_id' => $_POST['disciplina_id']]
    )) {
        header("Location: simulado.php?id=" . $simulado->getId());
    }
}

// Finalizar simulado
if (isset($_POST['finalizar'])) {
    $simulado = new Simulado($_POST['simulado_id']);
    
    $resultado = $simulado->finalizar(
        $_POST['respostas'],
        $_POST['tempo_gasto']
    );
    
    // Adicionar pontos
    $gamificacao = new GamificacaoRefatorada();
    $gamificacao->adicionarPontos(
        $_SESSION['usuario_id'],
        $resultado['pontos'],
        'simulado'
    );
    
    // Bônus por simulado perfeito
    if ($resultado['acertos'] == $resultado['total']) {
        $gamificacao->adicionarPontos($_SESSION['usuario_id'], 50, 'perfeicao');
    }
    
    header("Location: resultado.php?simulado_id=" . $simulado->getId());
}
```

---

## ✨ Boas Práticas

### 1. Sempre use Prepared Statements

```php
// ✅ BOM
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $this->pdo->prepare($sql);
$stmt->execute([$id]);

// ❌ RUIM
$sql = "SELECT * FROM usuarios WHERE id = $id";
$stmt = $this->pdo->query($sql);
```

### 2. Trate Exceções

```php
try {
    $this->pdo->beginTransaction();
    
    // Operações
    
    $this->pdo->commit();
    return true;
} catch (Exception $e) {
    $this->pdo->rollBack();
    error_log("Erro: " . $e->getMessage());
    return false;
}
```

### 3. Validações

```php
public function create($nome, $email, $senha) {
    // Validar dados
    if (empty($nome) || empty($email) || empty($senha)) {
        return false;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    if ($this->emailExists($email)) {
        return false;
    }
    
    // Continuar com criação...
}
```

### 4. Documentação

```php
/**
 * Cria novo usuário
 * 
 * @param string $nome Nome do usuário
 * @param string $email Email do usuário
 * @param string $senha Senha (será hasheada)
 * @return bool Sucesso da operação
 */
public function create($nome, $email, $senha) {
    // ...
}
```

---

## 🎨 Diagrama de Classes

```
┌─────────────────────────────────────────────┐
│              Database (Singleton)           │
├─────────────────────────────────────────────┤
│ - instance: Database                        │
│ - pdo: PDO                                  │
├─────────────────────────────────────────────┤
│ + getInstance(): Database                   │
│ + getConnection(): PDO                      │
└─────────────────────────────────────────────┘
                    ▲
                    │ uses
                    │
┌─────────────────────────────────────────────┐
│              User                           │
├─────────────────────────────────────────────┤
│ - id: int                                   │
│ - nome: string                              │
│ - email: string                             │
├─────────────────────────────────────────────┤
│ + create(): bool                            │
│ + authenticate(): bool                      │
│ + update(): bool                            │
│ + getId(): int                              │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│              Questao                        │
├─────────────────────────────────────────────┤
│ - id: int                                   │
│ - enunciado: string                         │
│ - alternativas: array                       │
├─────────────────────────────────────────────┤
│ + create(): bool                            │
│ + verificarResposta(): bool                 │
│ + registrarResposta(): array                │
│ + getRandom(): array (static)               │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│              Simulado                       │
├─────────────────────────────────────────────┤
│ - id: int                                   │
│ - questoes: array                           │
│ - pontuacao_final: int                      │
├─────────────────────────────────────────────┤
│ + create(): bool                            │
│ + finalizar(): array                        │
│ + getResultData(): array                    │
│ + listByUser(): array (static)              │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│         GamificacaoRefatorada               │
├─────────────────────────────────────────────┤
│ - pdo: PDO                                  │
├─────────────────────────────────────────────┤
│ + adicionarPontos(): bool                   │
│ + atualizarStreak(): void                   │
│ + obterDadosUsuario(): array                │
│ + obterRankingMensal(): array               │
└─────────────────────────────────────────────┘
```

---

## 🔒 Segurança

### Princípios Implementados

1. **Prepared Statements**: Todas as consultas usam prepared statements
2. **Password Hashing**: Senhas são hasheadas com `password_hash()`
3. **Validação de Entrada**: Todas as entradas são validadas
4. **Tratamento de Erros**: Erros são logados sem expor dados sensíveis
5. **Transações**: Operações críticas usam transações

### Exemplo de Validação

```php
public function authenticate($email, $senha) {
    // Sanitizar entrada
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    // Verificar formato
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Usar prepared statement
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Verificar senha com password_verify
    if ($user && password_verify($senha, $user['senha_hash'])) {
        return true;
    }
    
    return false;
}
```

---

## 🚀 Próximos Passos

1. **Migrar arquivos restantes**: Conversão completa dos arquivos procedural
2. **Testes Unitários**: Implementar PHPUnit
3. **Namespaces**: Organizar classes em namespaces
4. **Composer**: Gerenciar dependências
5. **API REST**: Criar camada de API
6. **Cache**: Implementar sistema de cache

---

## 📝 Conclusão

O sistema foi completamente refatorado seguindo as melhores práticas de POO:

- ✅ Código limpo e organizado
- ✅ Reutilização de componentes
- ✅ Manutenibilidade aumentada
- ✅ Segurança aprimorada
- ✅ Performance otimizada
- ✅ Documentação completa

---

**Versão**: 2.0 POO  
**Data**: 2024  
**Autor**: Sistema RCP

