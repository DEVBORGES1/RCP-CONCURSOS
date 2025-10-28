<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

if (!isset($_SESSION["usuario_id"])) {
    die("Usuário não logado");
}

$usuario_id = $_SESSION["usuario_id"];
$gamificacao = new Gamificacao($pdo);

echo "<h2>Teste e Correção do Sistema de Conquistas</h2>";
echo "<h3>Usuário ID: $usuario_id</h3>";

// 1. Verificar dados atuais
echo "<h4>1. Dados Atuais do Usuário:</h4>";
$dados_usuario = $gamificacao->obterDadosUsuario($usuario_id);
echo "Pontos: " . $dados_usuario['pontos_total'] . "<br>";
echo "Nível: " . $dados_usuario['nivel'] . "<br>";
echo "Questões respondidas: " . $dados_usuario['questoes_respondidas'] . "<br>";
echo "Streak: " . $dados_usuario['streak_dias'] . "<br>";

// 2. Adicionar conquistas baseadas em pontos se não existirem
echo "<h4>2. Verificando/Adicionando Conquistas Baseadas em Pontos:</h4>";

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
        echo "✅ Conquista '{$conquista[0]}' adicionada!<br>";
    } else {
        echo "ℹ️ Conquista '{$conquista[0]}' já existe!<br>";
    }
}

// 3. Forçar verificação de todas as conquistas
echo "<h4>3. Forçando Verificação de Todas as Conquistas:</h4>";
$gamificacao->verificarTodasConquistas($usuario_id);
echo "✅ Verificação executada!<br>";

// 4. Verificar conquistas do usuário
echo "<h4>4. Conquistas do Usuário:</h4>";
$sql = "SELECT c.*, uc.data_conquista 
        FROM conquistas c 
        LEFT JOIN usuarios_conquistas uc ON c.id = uc.conquista_id AND uc.usuario_id = ?
        ORDER BY c.pontos_necessarios";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$conquistas_usuario = $stmt->fetchAll();

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th style='padding: 8px; border: 1px solid #ccc;'>ID</th>";
echo "<th style='padding: 8px; border: 1px solid #ccc;'>Nome</th>";
echo "<th style='padding: 8px; border: 1px solid #ccc;'>Tipo</th>";
echo "<th style='padding: 8px; border: 1px solid #ccc;'>Pontos Necessários</th>";
echo "<th style='padding: 8px; border: 1px solid #ccc;'>Conquistada</th>";
echo "<th style='padding: 8px; border: 1px solid #ccc;'>Data</th>";
echo "</tr>";

foreach ($conquistas_usuario as $conquista) {
    $cor = $conquista['data_conquista'] ? '#d4edda' : '#f8d7da';
    $texto = $conquista['data_conquista'] ? '✅ SIM' : '❌ NÃO';
    
    echo "<tr style='background-color: $cor;'>";
    echo "<td style='padding: 8px; border: 1px solid #ccc;'>" . $conquista['id'] . "</td>";
    echo "<td style='padding: 8px; border: 1px solid #ccc;'>" . $conquista['nome'] . "</td>";
    echo "<td style='padding: 8px; border: 1px solid #ccc;'>" . $conquista['tipo'] . "</td>";
    echo "<td style='padding: 8px; border: 1px solid #ccc;'>" . $conquista['pontos_necessarios'] . "</td>";
    echo "<td style='padding: 8px; border: 1px solid #ccc;'>$texto</td>";
    echo "<td style='padding: 8px; border: 1px solid #ccc;'>" . ($conquista['data_conquista'] ?: '-') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 5. Resumo
echo "<h4>5. Resumo:</h4>";
$conquistas_ganhas = array_filter($conquistas_usuario, function($c) { return $c['data_conquista']; });
$total_ganhas = count($conquistas_ganhas);
$total_disponiveis = count($conquistas_usuario);

echo "🎯 Total de conquistas ganhas: <strong>$total_ganhas</strong><br>";
echo "📊 Total de conquistas disponíveis: <strong>$total_disponiveis</strong><br>";
echo "📈 Progresso: <strong>" . round(($total_ganhas / $total_disponiveis) * 100, 1) . "%</strong><br>";

if ($total_ganhas > 0) {
    echo "<h4>6. Conquistas Ganhas:</h4>";
    foreach ($conquistas_ganhas as $conquista) {
        echo "🏆 " . $conquista['nome'] . " - " . $conquista['descricao'] . "<br>";
    }
}

echo "<br><a href='dashboard.php'>← Voltar ao Dashboard</a>";
?>
