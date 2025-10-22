<?php
require 'conexao.php';

echo "🔧 Iniciando correção dos simulados...\n\n";

try {
    // 1. Remover simulados duplicados (manter apenas o mais recente)
    echo "1. Removendo simulados duplicados...\n";
    $sql = "DELETE s1 FROM simulados s1
            INNER JOIN simulados s2 
            WHERE s1.id < s2.id 
            AND s1.usuario_id = s2.usuario_id 
            AND s1.nome = s2.nome
            AND s1.nome IN ('Simulado Geral Básico', 'Simulado Português e Matemática', 'Simulado Conhecimentos Específicos', 'Simulado Raciocínio e Informática', 'Simulado Completo')";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    echo "✅ Simulados duplicados removidos\n\n";

    // 2. Limpar questões duplicadas nos simulados
    echo "2. Removendo questões duplicadas nos simulados...\n";
    $sql = "DELETE sq1 FROM simulados_questoes sq1
            INNER JOIN simulados_questoes sq2 
            WHERE sq1.id < sq2.id 
            AND sq1.simulado_id = sq2.simulado_id 
            AND sq1.questao_id = sq2.questao_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    echo "✅ Questões duplicadas removidas\n\n";

    // 3. Verificar e corrigir simulados sem questões
    echo "3. Verificando simulados sem questões...\n";
    $sql = "SELECT s.id, s.nome, s.usuario_id, COUNT(sq.questao_id) as total_questoes
            FROM simulados s 
            LEFT JOIN simulados_questoes sq ON s.id = sq.simulado_id 
            WHERE s.nome IN ('Simulado Geral Básico', 'Simulado Português e Matemática', 'Simulado Conhecimentos Específicos', 'Simulado Raciocínio e Informática', 'Simulado Completo')
            GROUP BY s.id, s.nome, s.usuario_id
            HAVING total_questoes = 0";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $simulados_sem_questoes = $stmt->fetchAll();
    
    if (!empty($simulados_sem_questoes)) {
        echo "⚠️  Encontrados " . count($simulados_sem_questoes) . " simulados sem questões. Removendo...\n";
        foreach ($simulados_sem_questoes as $simulado) {
            $sql = "DELETE FROM simulados WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$simulado['id']]);
            echo "   - Removido: {$simulado['nome']} (ID: {$simulado['id']})\n";
        }
    } else {
        echo "✅ Todos os simulados têm questões\n";
    }
    echo "\n";

    // 4. Verificar questões com respostas corretas inválidas
    echo "4. Verificando questões com respostas corretas inválidas...\n";
    $sql = "SELECT id, alternativa_correta FROM questoes 
            WHERE alternativa_correta NOT IN ('A', 'B', 'C', 'D', 'E') 
            OR alternativa_correta IS NULL";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $questoes_invalidas = $stmt->fetchAll();
    
    if (!empty($questoes_invalidas)) {
        echo "⚠️  Encontradas " . count($questoes_invalidas) . " questões com respostas corretas inválidas:\n";
        foreach ($questoes_invalidas as $questao) {
            echo "   - Questão ID: {$questao['id']}, Resposta: '{$questao['alternativa_correta']}'\n";
        }
    } else {
        echo "✅ Todas as questões têm respostas corretas válidas\n";
    }
    echo "\n";

    // 5. Estatísticas finais
    echo "5. Estatísticas finais:\n";
    
    // Total de simulados
    $sql = "SELECT COUNT(*) as total FROM simulados";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $total_simulados = $stmt->fetchColumn();
    echo "   - Total de simulados: $total_simulados\n";
    
    // Total de questões
    $sql = "SELECT COUNT(*) as total FROM questoes";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $total_questoes = $stmt->fetchColumn();
    echo "   - Total de questões: $total_questoes\n";
    
    // Simulados pré-definidos por usuário
    $sql = "SELECT usuario_id, COUNT(*) as simulados_predefinidos 
            FROM simulados 
            WHERE nome IN ('Simulado Geral Básico', 'Simulado Português e Matemática', 'Simulado Conhecimentos Específicos', 'Simulado Raciocínio e Informática', 'Simulado Completo')
            GROUP BY usuario_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $simulados_por_usuario = $stmt->fetchAll();
    
    echo "   - Simulados pré-definidos por usuário:\n";
    foreach ($simulados_por_usuario as $usuario) {
        echo "     * Usuário {$usuario['usuario_id']}: {$usuario['simulados_predefinidos']} simulados\n";
    }

    echo "\n🎉 Correção dos simulados concluída com sucesso!\n";
    echo "💡 Agora os simulados pré-definidos devem funcionar corretamente.\n";

} catch (Exception $e) {
    echo "❌ Erro durante a correção: " . $e->getMessage() . "\n";
}
?>
