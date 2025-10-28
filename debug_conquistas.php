<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

if (!isset($_SESSION["usuario_id"])) {
    die("Usuário não logado");
}

$usuario_id = $_SESSION["usuario_id"];
$gamificacao = new Gamificacao($pdo);

echo "<h2>Debug do Sistema de Conquistas</h2>";
echo "<h3>Usuário ID: $usuario_id</h3>";

// 1. Verificar dados do usuário
echo "<h4>1. Dados do Usuário:</h4>";
$dados_usuario = $gamificacao->obterDadosUsuario($usuario_id);
echo "<pre>";
print_r($dados_usuario);
echo "</pre>";

// 2. Verificar conquistas cadastradas
echo "<h4>2. Conquistas Cadastradas:</h4>";
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

// 3. Verificar conquistas do usuário
echo "<h4>3. Conquistas do Usuário:</h4>";
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
    echo "<tr>";
    echo "<td>" . $conquista['id'] . "</td>";
    echo "<td>" . $conquista['nome'] . "</td>";
    echo "<td>" . $conquista['tipo'] . "</td>";
    echo "<td>" . $conquista['pontos_necessarios'] . "</td>";
    echo "<td>" . ($conquista['data_conquista'] ? 'SIM' : 'NÃO') . "</td>";
    echo "<td>" . ($conquista['data_conquista'] ?: '-') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 4. Verificar questões respondidas
echo "<h4>4. Questões Respondidas:</h4>";
$sql = "SELECT COUNT(*) as total FROM respostas_usuario WHERE usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$total_questoes = $stmt->fetchColumn();

$sql = "SELECT COUNT(*) as corretas FROM respostas_usuario WHERE usuario_id = ? AND correta = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$questoes_corretas = $stmt->fetchColumn();

echo "Total de questões respondidas: $total_questoes<br>";
echo "Questões corretas: $questoes_corretas<br>";

// 5. Verificar simulados
echo "<h4>5. Simulados:</h4>";
$sql = "SELECT COUNT(*) as total FROM simulados WHERE usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$total_simulados = $stmt->fetchColumn();

$sql = "SELECT COUNT(*) as concluidos FROM simulados WHERE usuario_id = ? AND questoes_corretas IS NOT NULL";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$simulados_concluidos = $stmt->fetchColumn();

echo "Total de simulados: $total_simulados<br>";
echo "Simulados concluídos: $simulados_concluidos<br>";

// 6. Testar verificação manual de conquistas
echo "<h4>6. Teste Manual de Conquistas:</h4>";
foreach ($conquistas as $conquista) {
    $conquistada = false;
    
    switch ($conquista['tipo']) {
        case 'questoes':
            $conquistada = $total_questoes >= $conquista['pontos_necessarios'];
            break;
        case 'nivel':
            $conquistada = $dados_usuario['nivel'] >= $conquista['pontos_necessarios'];
            break;
        case 'streak':
            $conquistada = $dados_usuario['streak_dias'] >= $conquista['pontos_necessarios'];
            break;
        case 'simulado':
            $conquistada = $simulados_concluidos >= $conquista['pontos_necessarios'];
            break;
    }
    
    $status = $conquistada ? "✅ DEVERIA SER CONQUISTADA" : "❌ Não conquistada";
    echo "{$conquista['nome']} ({$conquista['tipo']} - {$conquista['pontos_necessarios']}): $status<br>";
}

// 7. Forçar verificação de conquistas
echo "<h4>7. Forçando Verificação de Conquistas:</h4>";
$gamificacao->adicionarPontos($usuario_id, 0, 'questao');
echo "Verificação forçada executada!<br>";

// 8. Verificar conquistas novamente
echo "<h4>8. Conquistas Após Verificação:</h4>";
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
    echo "<tr>";
    echo "<td>" . $conquista['id'] . "</td>";
    echo "<td>" . $conquista['nome'] . "</td>";
    echo "<td>" . $conquista['tipo'] . "</td>";
    echo "<td>" . $conquista['pontos_necessarios'] . "</td>";
    echo "<td>" . ($conquista['data_conquista'] ? 'SIM' : 'NÃO') . "</td>";
    echo "<td>" . ($conquista['data_conquista'] ?: '-') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
