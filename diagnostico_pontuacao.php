<?php
require 'conexao.php';

echo "🔍 DIAGNÓSTICO DO SISTEMA DE PONTUAÇÃO E CONQUISTAS\n";
echo "====================================================\n\n";

try {
    // 1. Verificar usuários e progresso
    echo "1. Verificando usuários e progresso...\n";
    $sql = "SELECT u.id, u.nome, p.nivel, p.pontos_total, p.streak_dias 
            FROM usuarios u 
            LEFT JOIN usuarios_progresso p ON u.id = p.usuario_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    foreach ($usuarios as $usuario) {
        echo "   - Usuário: {$usuario['nome']} (ID: {$usuario['id']})\n";
        echo "     Nível: " . ($usuario['nivel'] ?? 'Não definido') . "\n";
        echo "     Pontos: " . ($usuario['pontos_total'] ?? '0') . "\n";
        echo "     Streak: " . ($usuario['streak_dias'] ?? '0') . " dias\n";
    }
    echo "\n";
    
    // 2. Verificar respostas do usuário
    echo "2. Verificando respostas do usuário...\n";
    $sql = "SELECT COUNT(*) as total, 
                   SUM(CASE WHEN correta = 1 THEN 1 ELSE 0 END) as corretas,
                   SUM(pontos_ganhos) as pontos_totais
            FROM respostas_usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $respostas = $stmt->fetch();
    
    echo "   Total de respostas: {$respostas['total']}\n";
    echo "   Respostas corretas: {$respostas['corretas']}\n";
    echo "   Pontos ganhos: {$respostas['pontos_totais']}\n\n";
    
    // 3. Verificar conquistas disponíveis
    echo "3. Verificando conquistas disponíveis...\n";
    $sql = "SELECT id, nome, descricao, pontos_necessarios, tipo FROM conquistas ORDER BY pontos_necessarios";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $conquistas = $stmt->fetchAll();
    
    foreach ($conquistas as $conquista) {
        echo "   - {$conquista['nome']}: {$conquista['pontos_necessarios']} pontos ({$conquista['tipo']})\n";
    }
    echo "\n";
    
    // 4. Verificar conquistas do usuário
    echo "4. Verificando conquistas conquistadas...\n";
    $sql = "SELECT c.nome, uc.data_conquista 
            FROM usuarios_conquistas uc 
            JOIN conquistas c ON uc.conquista_id = c.id 
            ORDER BY uc.data_conquista";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $conquistas_usuario = $stmt->fetchAll();
    
    if (empty($conquistas_usuario)) {
        echo "   ❌ Nenhuma conquista conquistada!\n";
    } else {
        foreach ($conquistas_usuario as $conquista) {
            echo "   ✅ {$conquista['nome']} - {$conquista['data_conquista']}\n";
        }
    }
    echo "\n";
    
    // 5. Verificar se há problemas na lógica
    echo "5. Verificando lógica de conquistas...\n";
    
    // Verificar primeira conquista (10 pontos)
    $sql = "SELECT COUNT(*) FROM respostas_usuario WHERE pontos_ganhos > 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $respostas_com_pontos = $stmt->fetchColumn();
    
    echo "   Respostas com pontos: $respostas_com_pontos\n";
    
    if ($respostas_com_pontos >= 1) {
        echo "   ✅ Usuário deveria ter a primeira conquista!\n";
        
        // Verificar se a conquista existe
        $sql = "SELECT id FROM conquistas WHERE nome = 'Primeira Questão'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $conquista_id = $stmt->fetchColumn();
        
        if ($conquista_id) {
            echo "   ✅ Conquista 'Primeira Questão' existe (ID: $conquista_id)\n";
            
            // Verificar se foi concedida
            $sql = "SELECT COUNT(*) FROM usuarios_conquistas WHERE conquista_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$conquista_id]);
            $conquistada = $stmt->fetchColumn();
            
            if ($conquistada > 0) {
                echo "   ✅ Conquista já foi concedida!\n";
            } else {
                echo "   ❌ PROBLEMA: Conquista não foi concedida automaticamente!\n";
            }
        } else {
            echo "   ❌ PROBLEMA: Conquista 'Primeira Questão' não existe!\n";
        }
    } else {
        echo "   ❌ Usuário ainda não tem pontos suficientes\n";
    }
    echo "\n";
    
    // 6. Diagnóstico e recomendações
    echo "6. DIAGNÓSTICO E RECOMENDAÇÕES:\n";
    echo "===============================\n";
    
    if (empty($conquistas_usuario) && $respostas_com_pontos > 0) {
        echo "❌ PROBLEMA IDENTIFICADO: Sistema de conquistas não está funcionando!\n";
        echo "💡 POSSÍVEIS CAUSAS:\n";
        echo "   1. Conquistas não foram inicializadas no banco\n";
        echo "   2. Lógica de verificação de conquistas tem bug\n";
        echo "   3. Sistema de gamificação não está sendo chamado corretamente\n\n";
        
        echo "🔧 SOLUÇÕES:\n";
        echo "   1. Executar script de inicialização de conquistas\n";
        echo "   2. Verificar se gamificação está sendo chamada nas respostas\n";
        echo "   3. Executar correção manual das conquistas\n";
    } else {
        echo "✅ Sistema parece estar funcionando corretamente\n";
    }
    
    echo "\n✅ Diagnóstico concluído!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
