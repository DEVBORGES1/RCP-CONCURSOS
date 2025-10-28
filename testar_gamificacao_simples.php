<?php
require 'conexao.php';
require 'classes/Gamificacao.php';

echo "🧪 TESTE SIMPLES DA GAMIFICAÇÃO\n";
echo "===============================\n\n";

try {
    $gamificacao = new Gamificacao($pdo);
    
    // 1. Testar garantia de progresso
    echo "1. Testando garantia de progresso...\n";
    $gamificacao->garantirProgressoUsuario(1);
    echo "   ✅ Progresso garantido\n\n";
    
    // 2. Testar adição de pontos diretamente
    echo "2. Testando adição de pontos...\n";
    
    // Verificar estado antes
    $sql = "SELECT pontos_total FROM usuarios_progresso WHERE usuario_id = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pontos_antes = $stmt->fetchColumn();
    echo "   Pontos antes: $pontos_antes\n";
    
    // Tentar adicionar pontos
    $resultado = $gamificacao->adicionarPontos(1, 10, 'questao');
    echo "   Resultado: " . ($resultado ? 'SUCESSO' : 'FALHA') . "\n";
    
    // Verificar estado depois
    $sql = "SELECT pontos_total FROM usuarios_progresso WHERE usuario_id = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pontos_depois = $stmt->fetchColumn();
    echo "   Pontos depois: $pontos_depois\n";
    
    if ($pontos_depois > $pontos_antes) {
        echo "   ✅ Pontos adicionados com sucesso!\n";
    } else {
        echo "   ❌ Pontos NÃO foram adicionados!\n";
    }
    echo "\n";
    
    // 3. Verificar conquistas
    echo "3. Verificando conquistas...\n";
    $sql = "SELECT COUNT(*) FROM usuarios_conquistas WHERE usuario_id = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $conquistas = $stmt->fetchColumn();
    echo "   Conquistas conquistadas: $conquistas\n";
    
    if ($conquistas > 0) {
        echo "   ✅ Conquistas funcionando!\n";
    } else {
        echo "   ❌ Nenhuma conquista conquistada!\n";
    }
    echo "\n";
    
    // 4. Verificar se há problemas na transação
    echo "4. Verificando transações...\n";
    $sql = "SELECT @@autocommit";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $autocommit = $stmt->fetchColumn();
    echo "   Autocommit: $autocommit\n";
    
    // 5. Testar inserção manual
    echo "5. Testando inserção manual...\n";
    try {
        $sql = "INSERT INTO usuarios_conquistas (usuario_id, conquista_id) VALUES (1, 1)";
        $stmt = $pdo->prepare($sql);
        $resultado = $stmt->execute();
        echo "   Inserção manual: " . ($resultado ? 'SUCESSO' : 'FALHA') . "\n";
        
        if ($resultado) {
            // Remover para não duplicar
            $sql = "DELETE FROM usuarios_conquistas WHERE usuario_id = 1 AND conquista_id = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            echo "   ✅ Inserção manual funcionando\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Erro na inserção manual: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Teste concluído!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
