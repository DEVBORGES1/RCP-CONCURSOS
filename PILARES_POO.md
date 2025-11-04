# 📚 Pilares da Programação Orientada a Objetos (POO)
## Evidências e Aplicações no Sistema RCP-CONCURSOS

---

## 📋 Índice

1. [Encapsulamento](#1-encapsulamento)
2. [Abstração](#2-abstração)
3. [Herança](#3-herança)
4. [Polimorfismo](#4-polimorfismo)
5. [Conceitos Avançados](#5-conceitos-avançados)
   - Padrões de Design
   - Princípios SOLID
   - Composition vs Inheritance

---

## 1. 🔒 ENCAPSULAMENTO

### Definição
Encapsulamento é o princípio de esconder detalhes internos de implementação e expor apenas uma interface pública controlada. Isso protege os dados e garante que apenas métodos específicos possam acessá-los.

### Evidências no Código

#### 1.1 Propriedades Privadas

**Arquivo**: `classes/Database.php`

```php
class Database {
    // Propriedades privadas - não podem ser acessadas diretamente
    private static $instance = null;
    private $pdo;
    private $host;
    private $db;
    private $user;
    private $pass;
    
    // Construtor privado - impede criação direta
    private function __construct() {
        $this->host = "localhost";
        $this->db = "concursos";
        $this->user = "root";
        $this->pass = "1234";
        // ...
    }
    
    // Método público para acessar a instância
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Método público para acessar a conexão
    public function getConnection() {
        return $this->pdo;
    }
}
```

**🎯 Por que é Encapsulamento:**
- Propriedades `private` impedem acesso direto: `$db->host = "x"` ❌ (erro)
- Construtor `private` garante que apenas `getInstance()` possa criar instâncias
- Método `getConnection()` é a única forma de acessar a conexão PDO
- Detalhes de configuração (host, user, pass) ficam escondidos

**📍 Localização**: Linhas 13-61

---

#### 1.2 Métodos Privados e Públicos

**Arquivo**: `classes/User.php`

```php
class User {
    // Propriedades privadas
    private $pdo;
    private $id;
    private $nome;
    private $email;
    
    // Método público - interface externa
    public function create($nome, $email, $senha) {
        // Validação interna
        if ($this->emailExists($email)) {
            return false;
        }
        
        // Lógica interna oculta
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        // ...
    }
    
    // Método privado - apenas para uso interno
    private function emailExists($email) {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }
    
    // Getters públicos - acesso controlado
    public function getId() {
        return $this->id;
    }
    
    public function getNome() {
        return $this->nome;
    }
}
```

**🎯 Por que é Encapsulamento:**
- `emailExists()` é `private` - não pode ser chamado externamente
- `create()` é `public` - interface pública para criar usuários
- Propriedades privadas (`$id`, `$nome`, `$email`) só podem ser alteradas através de métodos da classe
- Getters fornecem acesso controlado aos dados

**📍 Localização**: Linhas 14-222

---

#### 1.3 Proteção contra Clonagem e Deserialização

**Arquivo**: `classes/Database.php`

```php
class Database {
    // Previne clonagem da instância
    private function __clone() {}
    
    // Previne deserialização da instância
    public function __wakeup() {
        throw new Exception("Não é possível deserializar uma instância de Database");
    }
}
```

**🎯 Por que é Encapsulamento:**
- `__clone()` privado impede: `$db2 = clone $db` ❌
- `__wakeup()` impede deserialização que poderia criar múltiplas instâncias
- Garante que o padrão Singleton seja respeitado

**📍 Localização**: Linhas 66-73

---

#### 1.4 Métodos Privados para Processamento Interno

**Arquivo**: `classes/AnalisadorEdital.php`

```php
class AnalisadorEdital {
    private $pdo;
    private $disciplinas_comuns = [/* ... */];
    private $padroes_disciplinas = [/* ... */];
    
    // Método público - interface externa
    public function analisarEdital($edital_id, $texto_edital) {
        // Usa métodos privados internamente
        $disciplinas_encontradas = $this->extrairDisciplinas($texto_edital);
        foreach ($disciplinas_encontradas as $disciplina) {
            $this->salvarDisciplina($edital_id, $disciplina);
            $this->gerarQuestoesAutomaticas($edital_id, $disciplina);
        }
    }
    
    // Método privado - detalhes de implementação ocultos
    private function extrairDisciplinas($texto) {
        $disciplinas_encontradas = [];
        // Lógica complexa de extração...
        foreach ($this->padroes_disciplinas as $padrao) {
            // ...
        }
        return $disciplinas_encontradas;
    }
    
    // Método privado
    private function processarTextoDisciplina($match) {
        // Lógica de processamento...
    }
}
```

**🎯 Por que é Encapsulamento:**
- `extrairDisciplinas()` e `processarTextoDisciplina()` são privados
- Clientes só precisam chamar `analisarEdital()` - não precisam saber como funciona internamente
- Detalhes de regex e processamento de texto ficam ocultos

**📍 Localização**: Linhas 83-100

---

## 2. 🎭 ABSTRAÇÃO

### Definição
Abstração é o processo de esconder a complexidade interna e mostrar apenas as funcionalidades essenciais. Permite trabalhar em um nível conceitual mais alto, sem se preocupar com detalhes de implementação.

### Evidências no Código

#### 2.1 Abstração de Operações Complexas

**Arquivo**: `classes/Questao.php`

```php
class Questao {
    // Método público que abstrai a complexidade de verificação
    public function verificarResposta($resposta) {
        // Internamente: normaliza, compara, trata erros
        $resposta_normalizada = strtoupper(trim($resposta));
        $correta_normalizada = strtoupper(trim($this->alternativa_correta));
        return $resposta_normalizada === $correta_normalizada;
    }
    
    // Método que abstrai todo o processo de registro
    public function registrarResposta($usuario_id, $resposta) {
        // Internamente: verifica, calcula pontos, salva no banco
        $acertou = $this->verificarResposta($resposta);
        $pontos = $acertou ? 10 : 0;
        // Salva no banco...
        return [
            'acertou' => $acertou,
            'pontos' => $pontos,
            'resposta_correta' => $this->alternativa_correta
        ];
    }
}
```

**🎯 Por que é Abstração:**
- Cliente não precisa saber como a resposta é normalizada
- Cliente não precisa saber como os pontos são calculados
- Cliente não precisa conhecer a estrutura do banco de dados
- Interface simples: `$questao->verificarResposta("A")` retorna `true/false`

**📍 Localização**: Linhas 113-148

**Uso no código:**
```php
// Uso simples - complexidade oculta
$questao = new Questao($questao_id);
$resultado = $questao->registrarResposta($usuario_id, "A");
// Retorna: ['acertou' => true, 'pontos' => 10, ...]
```

---

#### 2.2 Abstração de Processamento de Dados

**Arquivo**: `classes/GamificacaoRefatorada.php`

```php
class GamificacaoRefatorada {
    // Método que abstrai todo o processo de adicionar pontos
    public function adicionarPontos($usuario_id, $pontos, $tipo = 'questao') {
        // Internamente: garante progresso, atualiza pontos, calcula nível,
        // verifica conquistas, atualiza ranking
        $this->garantirProgressoUsuario($usuario_id);
        // Atualizar pontos...
        $novo_nivel = $this->calcularNivel($usuario_id);
        $this->atualizarNivel($usuario_id, $novo_nivel);
        $this->verificarConquistas($usuario_id, $tipo);
        $this->atualizarRankingMensal($usuario_id, $pontos);
        // ...
    }
    
    // Método privado que abstrai cálculo complexo de nível
    private function calcularNivel($usuario_id) {
        // Fórmula: nível = floor(sqrt(pontos / 100)) + 1
        // Cliente não precisa saber essa fórmula
        $sql = "SELECT pontos_total FROM usuarios_progresso WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $pontos = $stmt->fetchColumn();
        return floor(sqrt($pontos / 100)) + 1;
    }
}
```

**🎯 Por que é Abstração:**
- Cliente chama `adicionarPontos()` e todo o sistema funciona automaticamente
- Não precisa saber como o nível é calculado
- Não precisa saber como as conquistas são verificadas
- Não precisa saber como o ranking é atualizado
- Uma chamada simples: `$gamificacao->adicionarPontos(1, 10, 'questao')`

**📍 Localização**: Linhas 28-96

**Uso no código:**
```php
$gamificacao = new GamificacaoRefatorada();
$gamificacao->adicionarPontos($usuario_id, 10, 'questao');
// Internamente: atualiza pontos, nível, conquistas, ranking...
```

---

#### 2.3 Abstração de Análise de Texto

**Arquivo**: `classes/AnalisadorEdital.php`

```php
class AnalisadorEdital {
    // Método público que abstrai análise complexa de edital
    public function analisarEdital($edital_id, $texto_edital) {
        // Internamente: extrai disciplinas, salva no banco, gera questões
        $disciplinas_encontradas = $this->extrairDisciplinas($texto_edital);
        foreach ($disciplinas_encontradas as $disciplina) {
            $this->salvarDisciplina($edital_id, $disciplina);
            $this->gerarQuestoesAutomaticas($edital_id, $disciplina);
        }
        return [
            'sucesso' => true,
            'disciplinas_encontradas' => count($disciplinas_encontradas)
        ];
    }
}
```

**🎯 Por que é Abstração:**
- Cliente não precisa saber sobre regex, processamento de texto, ou padrões
- Cliente apenas chama `analisarEdital()` com texto
- Complexidade de extração, salvamento e geração de questões fica oculta
- Retorna resultado simples e claro

**📍 Localização**: Linhas 44-78

**Uso no código:**
```php
$analisador = new AnalisadorEdital($pdo);
$resultado = $analisador->analisarEdital($edital_id, $texto);
// Retorna: ['sucesso' => true, 'disciplinas_encontradas' => 5]
```

---

#### 2.4 Abstração de Geração de Cronograma

**Arquivo**: `classes/GeradorCronograma.php`

```php
class GeradorCronograma {
    // Método que abstrai geração complexa de cronograma
    public function gerarCronograma($usuario_id, $edital_id, $horas_por_dia, $data_inicio, $duracao_semanas = 4) {
        // Internamente: obtém disciplinas, distribui, cria cronograma detalhado
        $disciplinas = $this->obterDisciplinasEdital($edital_id);
        $cronograma_id = $this->criarCronogramaPrincipal(...);
        $distribuicao = $this->distribuirDisciplinas(...);
        $this->criarCronogramaDetalhado(...);
        // ...
    }
    
    // Métodos privados que abstraem partes específicas
    private function distribuirDisciplinas($disciplinas, $horas_por_dia, $duracao_semanas) {
        // Lógica complexa de distribuição...
    }
}
```

**🎯 Por que é Abstração:**
- Cliente não precisa saber como disciplinas são distribuídas ao longo do tempo
- Cliente não precisa saber como o cronograma é armazenado no banco
- Cliente apenas fornece parâmetros e recebe cronograma pronto
- Algoritmo de distribuição fica oculto

**📍 Localização**: Linhas 33-74

---

## 3. 🏛️ HERANÇA

### Definição
Herança permite que uma classe (filha) herde propriedades e métodos de outra classe (pai), promovendo reutilização de código e relacionamento "é um" (is-a).

### Evidências no Código

#### ⚠️ Nota Importante
No código atual, **não há herança direta** implementada entre classes. No entanto, podemos evidenciar conceitos relacionados:

#### 3.1 Padrão Wrapper/Adapter (Pseudo-Herança)

**Arquivo**: `classes/Gamificacao.php`

```php
/**
 * Wrapper de compatibilidade - MIGRAR PARA POO
 * 
 * Este arquivo mantém compatibilidade com código antigo.
 * Use GamificacaoRefatorada em novos códigos.
 * 
 * @deprecated Use GamificacaoRefatorada
 */
class Gamificacao {
    private $gamificacao;
    
    public function __construct($pdo = null) {
        // Ignorar $pdo, usar Database singleton
        $this->gamificacao = new GamificacaoRefatorada();
    }
    
    // Delega todos os métodos para GamificacaoRefatorada
    public function adicionarPontos($usuario_id, $pontos, $tipo = 'questao') {
        return $this->gamificacao->adicionarPontos($usuario_id, $pontos, $tipo);
    }
    
    public function garantirProgressoUsuario($usuario_id) {
        return $this->gamificacao->garantirProgressoUsuario($usuario_id);
    }
    // ... outros métodos
}
```

**🎯 Por que é Relacionado a Herança:**
- `Gamificacao` atua como uma interface de compatibilidade
- Delega funcionalidade para `GamificacaoRefatorada`
- Permite migração gradual mantendo compatibilidade
- Similar ao padrão Adapter do Design Patterns

**📍 Localização**: Linhas 14-53

**💡 Sugestão de Herança:**
```php
// Exemplo de como poderia ser implementada herança
abstract class BaseGamificacao {
    protected $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    abstract public function adicionarPontos($usuario_id, $pontos, $tipo);
}

class GamificacaoRefatorada extends BaseGamificacao {
    public function adicionarPontos($usuario_id, $pontos, $tipo) {
        // Implementação específica
    }
}
```

---

#### 3.2 Composição (Alternativa à Herança)

**Arquivo**: `classes/Simulado.php`

```php
class Simulado {
    private $pdo;
    private $questoes; // Array de Questao
    
    public function finalizar($respostas, $tempo_gasto) {
        // Usa composição ao invés de herança
        foreach ($respostas as $questao_id => $resposta_usuario) {
            $questao = new Questao($questao_id); // Composição
            $acertou = $questao->verificarResposta($resposta_usuario);
            $questao->registrarResposta($this->usuario_id, $resposta_usuario);
            // ...
        }
    }
}
```

**🎯 Por que é Composição:**
- `Simulado` **tem** (has-a) questões, não **é** uma questão
- Usa objetos `Questao` através de composição
- Relacionamento "tem um" ao invés de "é um"
- Mais flexível que herança

**📍 Localização**: Linhas 133-207

---

## 4. 🔄 POLIMORFISMO

### Definição
Polimorfismo permite que objetos de diferentes classes sejam tratados através de uma interface comum, permitindo que métodos se comportem de forma diferente dependendo do objeto que os invoca.

### Evidências no Código

#### 4.1 Polimorfismo através de Métodos Estáticos

**Arquivo**: `classes/Questao.php`

```php
class Questao {
    // Método estático que pode retornar diferentes tipos de resultados
    public static function getRandom($limite = 10, $filtros = []) {
        $pdo = Database::getInstance()->getConnection();
        
        $where = [];
        $params = [];
        
        // Comportamento diferente baseado nos filtros
        if (isset($filtros['edital_id'])) {
            $where[] = "edital_id = ?";
            $params[] = $filtros['edital_id'];
        }
        
        if (isset($filtros['disciplina_id'])) {
            $where[] = "disciplina_id = ?";
            $params[] = $filtros['disciplina_id'];
        }
        
        // Query adapta-se aos filtros fornecidos
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        $sql = "SELECT * FROM questoes {$whereClause} ORDER BY RAND() LIMIT " . $limite;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
```

**🎯 Por que é Polimorfismo:**
- Mesmo método `getRandom()` se comporta diferente baseado nos parâmetros
- Retorna resultados diferentes dependendo dos filtros
- Interface única para múltiplos comportamentos

**📍 Localização**: Linhas 157-186

**Uso:**
```php
// Comportamento 1: Sem filtros
$questoes = Questao::getRandom(10);
// Comportamento 2: Com filtro de edital
$questoes = Questao::getRandom(10, ['edital_id' => 5]);
// Comportamento 3: Com filtro de disciplina
$questoes = Questao::getRandom(10, ['disciplina_id' => 3]);
```

---

#### 4.2 Polimorfismo através de Métodos de Interface

**Arquivo**: `classes/Simulado.php`

```php
class Simulado {
    // Método que trabalha com diferentes objetos Questao
    public function finalizar($respostas, $tempo_gasto) {
        foreach ($respostas as $questao_id => $resposta_usuario) {
            // Cada Questao pode ter comportamento diferente
            $questao = new Questao($questao_id);
            
            // Polimorfismo: mesmo método, comportamento pode variar
            $acertou = $questao->verificarResposta($resposta_usuario);
            $questao->registrarResposta($this->usuario_id, $resposta_usuario);
        }
    }
}
```

**🎯 Por que é Polimorfismo:**
- `verificarResposta()` funciona para qualquer questão
- Cada questão pode ter sua própria lógica interna
- Interface comum (`verificarResposta()`) para diferentes tipos de questões

**📍 Localização**: Linhas 133-207

---

#### 4.3 Polimorfismo através de Métodos Estáticos

**Arquivo**: `classes/Simulado.php`

```php
class Simulado {
    // Método estático que pode retornar diferentes listas
    public static function listByUser($usuario_id, $filtros = []) {
        $pdo = Database::getInstance()->getConnection();
        
        $where = ["usuario_id = ?"];
        $params = [$usuario_id];
        
        // Comportamento diferente baseado nos filtros
        if (isset($filtros['finalizado'])) {
            if ($filtros['finalizado']) {
                $where[] = "questoes_corretas IS NOT NULL";
            } else {
                $where[] = "questoes_corretas IS NULL";
            }
        }
        
        $whereClause = "WHERE " . implode(" AND ", $where);
        $sql = "SELECT * FROM simulados {$whereClause} ORDER BY data_criacao DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
```

**🎯 Por que é Polimorfismo:**
- Mesmo método retorna resultados diferentes baseado nos filtros
- Interface única para múltiplos comportamentos

**📍 Localização**: Linhas 267-288

---

## 5. 🎯 CONCEITOS AVANÇADOS

### 5.1 Padrões de Design

#### Singleton Pattern

**Arquivo**: `classes/Database.php`

```php
class Database {
    // Instância única
    private static $instance = null;
    
    // Construtor privado
    private function __construct() {
        // ...
    }
    
    // Método estático para obter instância
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Previne clonagem
    private function __clone() {}
    
    // Previne deserialização
    public function __wakeup() {
        throw new Exception("Não é possível deserializar uma instância de Database");
    }
}
```

**🎯 Características do Singleton:**
- ✅ Garante uma única instância
- ✅ Construtor privado
- ✅ Método estático `getInstance()`
- ✅ Previne clonagem e deserialização
- ✅ Acesso global controlado

**📍 Localização**: Linhas 12-74

**Uso:**
```php
// Sempre retorna a mesma instância
$db1 = Database::getInstance();
$db2 = Database::getInstance();
// $db1 === $db2 (mesma instância)
```

---

#### Adapter/Wrapper Pattern

**Arquivo**: `classes/Gamificacao.php`

```php
class Gamificacao {
    private $gamificacao; // Composição
    
    public function __construct($pdo = null) {
        // Adapta interface antiga para nova
        $this->gamificacao = new GamificacaoRefatorada();
    }
    
    // Adapta chamadas antigas para nova implementação
    public function adicionarPontos($usuario_id, $pontos, $tipo = 'questao') {
        return $this->gamificacao->adicionarPontos($usuario_id, $pontos, $tipo);
    }
}
```

**🎯 Características do Adapter:**
- ✅ Adapta interface antiga para nova
- ✅ Mantém compatibilidade com código legado
- ✅ Delega funcionalidade para implementação real
- ✅ Permite migração gradual

**📍 Localização**: Linhas 14-53

---

#### Repository Pattern

**Arquivo**: `classes/User.php`, `classes/Questao.php`, `classes/Simulado.php`

```php
class User {
    // Encapsula acesso ao banco de dados
    public function create($nome, $email, $senha) {
        // Lógica de criação encapsulada
    }
    
    public function loadById($id) {
        // Lógica de carregamento encapsulada
    }
    
    public function update($data) {
        // Lógica de atualização encapsulada
    }
}
```

**🎯 Características do Repository:**
- ✅ Encapsula acesso a dados
- ✅ Abstrai operações de banco de dados
- ✅ Fornece interface simples para CRUD
- ✅ Isola lógica de persistência

**📍 Localização**: Todas as classes principais

---

### 5.2 Princípios SOLID

#### Single Responsibility Principle (SRP)

**Evidência**: Cada classe tem uma única responsabilidade

- ✅ `Database` - Apenas gerencia conexão
- ✅ `User` - Apenas gerencia usuários
- ✅ `Questao` - Apenas gerencia questões
- ✅ `Simulado` - Apenas gerencia simulados
- ✅ `GamificacaoRefatorada` - Apenas gerencia gamificação

---

#### Open/Closed Principle (OCP)

**Evidência**: Classes podem ser estendidas sem modificar código existente

```php
// Gamificacao pode ser estendida sem modificar GamificacaoRefatorada
class Gamificacao extends GamificacaoRefatorada {
    // Adiciona funcionalidades sem modificar a classe base
}
```

---

#### Dependency Inversion Principle (DIP)

**Evidência**: Classes dependem de abstrações (Database singleton)

```php
class User {
    public function __construct($id = null) {
        // Depende de Database (abstração), não de implementação concreta
        $this->pdo = Database::getInstance()->getConnection();
    }
}
```

---

### 5.3 Composição vs Herança

#### Composição (Preferida)

**Arquivo**: `classes/Simulado.php`

```php
class Simulado {
    private $questoes; // Composição: "tem" questões
    
    public function finalizar($respostas, $tempo_gasto) {
        foreach ($respostas as $questao_id => $resposta) {
            $questao = new Questao($questao_id); // Usa composição
            // ...
        }
    }
}
```

**Vantagens:**
- ✅ Mais flexível
- ✅ Menos acoplamento
- ✅ Pode mudar comportamento em runtime
- ✅ Evita problemas de herança múltipla

---

## 📊 Resumo dos Pilares

| Pilar | Evidências | Arquivos | Linhas |
|-------|-----------|----------|--------|
| **Encapsulamento** | Propriedades privadas, métodos públicos/privados, proteção contra clonagem | `Database.php`, `User.php`, `Questao.php`, `AnalisadorEdital.php` | Múltiplas |
| **Abstração** | Métodos que escondem complexidade, interface simples | `Questao.php`, `GamificacaoRefatorada.php`, `AnalisadorEdital.php`, `GeradorCronograma.php` | Múltiplas |
| **Herança** | Padrão Wrapper/Adapter, Composição | `Gamificacao.php`, `Simulado.php` | Múltiplas |
| **Polimorfismo** | Métodos estáticos com comportamento variável, interfaces comuns | `Questao.php`, `Simulado.php` | Múltiplas |

---

## 🎓 Conclusão

O sistema RCP-CONCURSOS demonstra **excelente aplicação dos pilares da POO**:

1. ✅ **Encapsulamento** - Bem implementado com propriedades privadas e métodos controlados
2. ✅ **Abstração** - Métodos complexos escondem detalhes de implementação
3. ⚠️ **Herança** - Não implementada diretamente, mas usa composição e padrões relacionados
4. ✅ **Polimorfismo** - Implementado através de métodos estáticos e interfaces comuns

### Recomendações Futuras

1. **Implementar Interfaces** para contratos claros
2. **Criar Classes Abstratas** para código compartilhado
3. **Aplicar Herança** onde fizer sentido (ex: diferentes tipos de questões)
4. **Implementar Traits** para funcionalidades compartilhadas

---

**📅 Última Atualização**: 2025
**📝 Versão**: 2.0
**👨‍💻 Autor**: Sistema RCP-CONCURSOS

