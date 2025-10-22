<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION["usuario_id"])) {
    echo "Usuário não logado!";
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

echo "🧹 Limpando simulados do usuário $usuario_id...\n\n";

try {
    // 1. Remover questões dos simulados
    $sql = "DELETE sq FROM simulados_questoes sq 
            JOIN simulados s ON sq.simulado_id = s.id 
            WHERE s.usuario_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $questoes_removidas = $stmt->rowCount();
    echo "✅ $questoes_removidas questões de simulados removidas\n";
    
    // 2. Remover simulados
    $sql = "DELETE FROM simulados WHERE usuario_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $simulados_removidos = $stmt->rowCount();
    echo "✅ $simulados_removidos simulados removidos\n";
    
    // 3. Remover respostas do usuário (opcional)
    $sql = "DELETE FROM respostas_usuario WHERE usuario_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $respostas_removidas = $stmt->rowCount();
    echo "✅ $respostas_removidas respostas removidas\n";
    
    echo "\n🎉 Limpeza concluída com sucesso!\n";
    echo "💡 Agora você pode acessar os simulados pré-definidos novamente.\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
