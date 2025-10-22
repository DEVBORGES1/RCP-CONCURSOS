<?php
require 'conexao.php';

echo "🔧 CORREÇÃO DO SISTEMA DE GAMIFICAÇÃO\n";
echo "=====================================\n\n";

try {
    // 1. Verificar se as tabelas existem
    echo "1. Verificando tabelas...\n";
    
    $tabelas = ['usuarios_progresso', 'conquistas', 'usuarios_conquistas', 'ranking_mensal'];
    foreach ($tabelas as $tabela) {
        $sql = "SHOW TABLES LIKE '$tabela'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $existe = $stmt->fetchColumn();
        
        if ($existe) {
            echo "   ✅ Tabela $tabela existe\n";
        } else {
            echo "   ❌ Tabela $tabela NÃO existe!\n";
        }
    }
    echo "\n";
    
    // 2. Verificar conquistas
    echo "2. Verificando conquistas...\n";
    $sql = "SELECT COUNT(*) FROM conquistas";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $total_conquistas = $stmt->fetchColumn();
    echo "   Total de conquistas: $total_conquistas\n";
    
    if ($total_conquistas == 0) {
        echo "   ❌ PROBLEMA: Nenhuma conquista cadastrada!\n";
        echo "   💡 Inicializando conquistas...\n";
        
        // Inserir conquistas básicas
        $conquistas = [
            ['Primeira Questão', 'Responda sua primeira questão', '🎯', 10, 'questoes'],
            ['Iniciante', 'Responda 10 questões', '🌟', 100, 'questoes'],
            ['Estudioso', 'Responda 50 questões', '📚', 500, 'questoes'],
            ['Expert', 'Responda 100 questões', '🏆', 1000, 'questoes'],
            ['Mestre', 'Responda 500 questões', '👑', 5000, 'questoes'],
            ['Streak 3', 'Estude 3 dias seguidos', '🔥', 50, 'streak'],
            ['Streak 7', 'Estude 7 dias seguidos', '🔥🔥', 200, 'streak'],
            ['Streak 30', 'Estude 30 dias seguidos', '🔥🔥🔥', 1000, 'streak'],
            ['Nível 5', 'Alcance o nível 5', '⭐', 250, 'nivel'],
            ['Nível 10', 'Alcance o nível 10', '⭐⭐', 750, 'nivel'],
            ['Simulador', 'Complete seu primeiro simulado', '📝', 100, 'simulado'],
            ['Perfeccionista', 'Acerte 100% em um simulado', '💯', 500, 'simulado']
        ];
        
        foreach ($conquistas as $conquista) {
            $sql = "INSERT INTO conquistas (nome, descricao, icone, pontos_necessarios, tipo) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($conquista);
        }
        
        echo "   ✅ Conquistas inicializadas!\n";
    }
    echo "\n";
    
    // 3. Verificar progresso do usuário
    echo "3. Verificando progresso do usuário...\n";
    $sql = "SELECT COUNT(*) FROM usuarios_progresso WHERE usuario_id = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $tem_progresso = $stmt->fetchColumn();
    
    if ($tem_progresso == 0) {
        echo "   ❌ Usuário não tem progresso!\n";
        echo "   💡 Criando progresso...\n";
        
        $sql = "INSERT INTO usuarios_progresso (usuario_id, nivel, pontos_total, streak_dias, ultimo_login) 
                VALUES (1, 1, 0, 0, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([date('Y-m-d')]);
        
        echo "   ✅ Progresso criado!\n";
    } else {
        echo "   ✅ Usuário tem progresso\n";
    }
    echo "\n";
    
    // 4. Testar adição de pontos manual
    echo "4. Testando adição de pontos manual...\n";
    
    $sql = "UPDATE usuarios_progresso SET pontos_total = pontos_total + 10 WHERE usuario_id = 1";
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute();
    
    if ($resultado) {
        echo "   ✅ Pontos adicionados manualmente!\n";
        
        // Verificar se foi adicionado
        $sql = "SELECT pontos_total FROM usuarios_progresso WHERE usuario_id = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pontos = $stmt->fetchColumn();
        echo "   Pontos atuais: $pontos\n";
    } else {
        echo "   ❌ Erro ao adicionar pontos manualmente!\n";
    }
    echo "\n";
    
    // 5. Testar concessão de conquista manual
    echo "5. Testando concessão de conquista manual...\n";
    
    $sql = "INSERT IGNORE INTO usuarios_conquistas (usuario_id, conquista_id) VALUES (1, 1)";
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute();
    
    if ($resultado) {
        echo "   ✅ Conquista concedida manualmente!\n";
        
        // Verificar se foi concedida
        $sql = "SELECT COUNT(*) FROM usuarios_conquistas WHERE usuario_id = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $conquistas = $stmt->fetchColumn();
        echo "   Conquistas conquistadas: $conquistas\n";
    } else {
        echo "   ❌ Erro ao conceder conquista manualmente!\n";
    }
    echo "\n";
    
    // 6. Criar função de gamificação simplificada
    echo "6. Criando função de gamificação simplificada...\n";
    
    function adicionarPontosSimples($pdo, $usuario_id, $pontos, $tipo) {
        try {
            // Atualizar pontos
            $sql = "UPDATE usuarios_progresso SET pontos_total = pontos_total + ? WHERE usuario_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$pontos, $usuario_id]);
            
            // Verificar conquistas
            $sql = "SELECT id, nome, pontos_necessarios FROM conquistas WHERE tipo = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tipo]);
            $conquistas = $stmt->fetchAll();
            
            foreach ($conquistas as $conquista) {
                // Verificar se já tem a conquista
                $sql = "SELECT COUNT(*) FROM usuarios_conquistas WHERE usuario_id = ? AND conquista_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$usuario_id, $conquista['id']]);
                
                if ($stmt->fetchColumn() == 0) {
                    // Verificar se pode conquistar
                    $sql = "SELECT COUNT(*) FROM respostas_usuario WHERE usuario_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$usuario_id]);
                    $questoes_respondidas = $stmt->fetchColumn();
                    
                    if ($questoes_respondidas >= $conquista['pontos_necessarios']) {
                        // Conceder conquista
                        $sql = "INSERT INTO usuarios_conquistas (usuario_id, conquista_id) VALUES (?, ?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$usuario_id, $conquista['id']]);
                        
                        echo "   🎉 Conquista '{$conquista['nome']}' concedida!\n";
                    }
                }
            }
            
            return true;
        } catch (Exception $e) {
            echo "   ❌ Erro: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    // Testar função simplificada
    $resultado = adicionarPontosSimples($pdo, 1, 10, 'questoes');
    echo "   Resultado da função simplificada: " . ($resultado ? 'SUCESSO' : 'FALHA') . "\n";
    
    echo "\n✅ Correção concluída!\n";
    echo "💡 Agora teste responder uma questão para ver se funciona!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
