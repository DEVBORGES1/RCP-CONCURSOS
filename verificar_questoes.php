<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION["usuario_id"])) {
    echo "Usuário não logado!";
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

echo "🔍 Verificando questões disponíveis para o usuário $usuario_id...\n\n";

// 1. Verificar editais do usuário
$sql = "SELECT id, nome_arquivo FROM editais WHERE usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$editais = $stmt->fetchAll();

echo "📁 Editais do usuário:\n";
if (empty($editais)) {
    echo "❌ Nenhum edital encontrado para este usuário!\n";
    echo "💡 O usuário precisa fazer upload de editais primeiro.\n";
} else {
    foreach ($editais as $edital) {
        echo "   - ID: {$edital['id']}, Arquivo: {$edital['nome_arquivo']}\n";
    }
}
echo "\n";

// 2. Verificar questões disponíveis
$sql = "SELECT COUNT(*) as total FROM questoes WHERE edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$total_questoes = $stmt->fetchColumn();

echo "📊 Total de questões disponíveis: $total_questoes\n\n";

if ($total_questoes == 0) {
    echo "❌ PROBLEMA IDENTIFICADO: Nenhuma questão disponível!\n";
    echo "💡 Soluções:\n";
    echo "   1. Fazer upload de editais com questões\n";
    echo "   2. Executar o script de instalação de questões\n";
    echo "   3. Verificar se as questões foram processadas corretamente\n";
} else {
    // 3. Verificar questões por disciplina
    $sql = "SELECT d.nome_disciplina, COUNT(q.id) as total 
            FROM questoes q 
            LEFT JOIN disciplinas d ON q.disciplina_id = d.id 
            WHERE q.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)
            GROUP BY d.nome_disciplina 
            ORDER BY total DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $disciplinas = $stmt->fetchAll();
    
    echo "📚 Questões por disciplina:\n";
    foreach ($disciplinas as $disciplina) {
        echo "   - {$disciplina['nome_disciplina']}: {$disciplina['total']} questões\n";
    }
    echo "\n";
    
    // 4. Verificar simulados existentes
    $sql = "SELECT id, nome, questoes_total, data_criacao FROM simulados WHERE usuario_id = ? ORDER BY data_criacao DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $simulados = $stmt->fetchAll();
    
    echo "📝 Simulados existentes:\n";
    if (empty($simulados)) {
        echo "   - Nenhum simulado encontrado\n";
    } else {
        foreach ($simulados as $simulado) {
            echo "   - ID: {$simulado['id']}, Nome: {$simulado['nome']}, Questões: {$simulado['questoes_total']}, Data: {$simulado['data_criacao']}\n";
        }
    }
    echo "\n";
    
    // 5. Verificar questões dos simulados
    if (!empty($simulados)) {
        echo "🔗 Questões nos simulados:\n";
        foreach ($simulados as $simulado) {
            $sql = "SELECT COUNT(*) as total FROM simulados_questoes WHERE simulado_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$simulado['id']]);
            $questoes_simulado = $stmt->fetchColumn();
            echo "   - {$simulado['nome']}: $questoes_simulado questões\n";
        }
    }
}

echo "\n✅ Verificação concluída!\n";
?>
