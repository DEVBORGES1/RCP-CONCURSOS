<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

if (!isset($_SESSION["usuario_id"])) {
    die("Usuário não logado");
}

$usuario_id = $_SESSION["usuario_id"];
$gamificacao = new Gamificacao($pdo);

echo "<h2>Correção do Sistema de Conquistas</h2>";

// 1. Verificar conquistas existentes
echo "<h3>1. Conquistas Existentes:</h3>";
$sql = "SELECT * FROM conquistas ORDER BY pontos_necessarios";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$conquistas = $stmt->fetchAll();

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Nome</th><th>Tipo</th><th>Pontos Necessários</th><th>Descrição</th></tr>";
foreach ($conquistas as $conquista) {
    echo "<tr>";
    echo "<td>" . $conquista['id'] . "</td>";
    echo "<td>" . $conquista['nome'] . "</td>";
    echo "<td>" . $conquista['tipo'] . "</td>";
    echo "<td>" . $conquista['pontos_necessarios'] . "</td>";
    echo "<td>" . $conquista['descricao'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// 2. Adicionar conquistas baseadas em pontos se não existirem
echo "<h3>2. Adicionando Conquistas Baseadas em Pontos:</h3>";

$conquistas_pontos = [
    ['Primeira Pontuação', 'Ganhe seus primeiros 10 pontos', '🎯', 10, 'pontos'],
    ['Iniciante', 'Ganhe 50 pontos', '🌟', 50, 'pontos'],
    ['Estudioso', 'Ganhe 100 pontos', '📚', 100, 'pontos'],
    ['Expert', 'Ganhe 250 pontos', '🏆', 250, 'pontos'],
    ['Mestre', 'Ganhe 500 pontos', '👑', 500, 'pontos']
];

foreach ($conquistas_pontos as $conquista) {
    // Verificar se já existe
    $sql = "SELECT COUNT(*) FROM conquistas WHERE nome = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$conquista[0]]);
    
    if ($stmt->fetchColumn() == 0) {
        $sql = "INSERT INTO conquistas (nome, descricao, icone, pontos_necessarios, tipo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($conquista);
        echo "Conquista '{$conquista[0]}' adicionada!<br>";
    } else {
        echo "Conquista '{$conquista[0]}' já existe!<br>";
    }
}

// 3. Verificar dados atuais do usuário
echo "<h3>3. Dados Atuais do Usuário:</h3>";
$dados_usuario = $gamificacao->obterDadosUsuario($usuario_id);
echo "Pontos: " . $dados_usuario['pontos_total'] . "<br>";
echo "Nível: " . $dados_usuario['nivel'] . "<br>";
echo "Questões respondidas: " . $dados_usuario['questoes_respondidas'] . "<br>";
echo "Streak: " . $dados_usuario['streak_dias'] . "<br>";

// 4. Forçar verificação de todas as conquistas
echo "<h3>4. Forçando Verificação de Todas as Conquistas:</h3>";
$gamificacao->verificarTodasConquistas($usuario_id);
echo "Verificação executada!<br>";

// 5. Verificar conquistas do usuário após correção
echo "<h3>5. Conquistas do Usuário Após Correção:</h3>";
$sql = "SELECT c.*, uc.data_conquista 
        FROM conquistas c 
        LEFT JOIN usuarios_conquistas uc ON c.id = uc.conquista_id AND uc.usuario_id = ?
        ORDER BY c.pontos_necessarios";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$conquistas_usuario = $stmt->fetchAll();

echo "<table border='1'>";
echo "<tr><th>ID</th><th>Nome</th><th>Tipo</th><th>Pontos Necessários</th><th>Conquistada</th><th>Data</th></tr>";
foreach ($conquistas_usuario as $conquista) {
    $cor = $conquista['data_conquista'] ? 'green' : 'red';
    echo "<tr style='background-color: $cor;'>";
    echo "<td>" . $conquista['id'] . "</td>";
    echo "<td>" . $conquista['nome'] . "</td>";
    echo "<td>" . $conquista['tipo'] . "</td>";
    echo "<td>" . $conquista['pontos_necessarios'] . "</td>";
    echo "<td>" . ($conquista['data_conquista'] ? 'SIM' : 'NÃO') . "</td>";
    echo "<td>" . ($conquista['data_conquista'] ?: '-') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>6. Conclusão:</h3>";
$conquistas_ganhas = array_filter($conquistas_usuario, function($c) { return $c['data_conquista']; });
echo "Total de conquistas ganhas: " . count($conquistas_ganhas) . "<br>";
echo "Total de conquistas disponíveis: " . count($conquistas_usuario) . "<br>";
?>
