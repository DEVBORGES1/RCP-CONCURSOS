<?php
require 'conexao.php';
require 'classes/Gamificacao.php';

echo "<h2>=== INICIALIZAÇÃO DO PROGRESSO DOS USUÁRIOS ===</h2>";

try {
    // Verificar se as tabelas existem
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios_progresso'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Tabela 'usuarios_progresso' não existe! Execute primeiro o script banco.sql</p>";
        exit;
    }
    
    // Obter todos os usuários que não têm progresso
    $sql = "SELECT u.id, u.nome, u.email 
            FROM usuarios u 
            LEFT JOIN usuarios_progresso p ON u.id = p.usuario_id 
            WHERE p.usuario_id IS NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios_sem_progresso = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Usuários sem progresso:</strong> " . count($usuarios_sem_progresso) . "</p>";
    
    if (count($usuarios_sem_progresso) > 0) {
        echo "<h3>Inicializando progresso para os usuários:</h3>";
        
        foreach ($usuarios_sem_progresso as $usuario) {
            // Inserir registro inicial de progresso
            $sql = "INSERT INTO usuarios_progresso (usuario_id, nivel, pontos_total, streak_dias, ultimo_login) 
                    VALUES (?, 1, 0, 0, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario['id'], date('Y-m-d')]);
            
            echo "<p>✓ Progresso inicializado para: <strong>{$usuario['nome']}</strong> ({$usuario['email']})</p>";
        }
        
        echo "<h3 style='color: green;'>✅ Progresso inicializado com sucesso!</h3>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Todos os usuários já possuem progresso registrado.</p>";
    }
    
    // Verificar conquistas existentes
    $stmt = $pdo->query("SELECT COUNT(*) FROM conquistas");
    $total_conquistas = $stmt->fetchColumn();
    
    if ($total_conquistas == 0) {
        echo "<h3>Inserindo conquistas padrão...</h3>";
        
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
        
        $sql = "INSERT INTO conquistas (nome, descricao, icone, pontos_necessarios, tipo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        foreach ($conquistas as $conquista) {
            $stmt->execute($conquista);
        }
        
        echo "<p>✓ Conquistas padrão inseridas com sucesso!</p>";
    }
    
    // Verificar se há dados de progresso para testar
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios_progresso");
    $total_progresso = $stmt->fetchColumn();
    
    echo "<h3>Resumo Final:</h3>";
    echo "<p><strong>Total de usuários com progresso:</strong> $total_progresso</p>";
    echo "<p><strong>Total de conquistas:</strong> " . $total_conquistas . "</p>";
    
    if ($total_progresso > 0) {
        echo "<p style='color: green;'>✅ Sistema de progresso está funcionando corretamente!</p>";
        echo "<p><a href='dashboard.php'>→ Ir para o Dashboard</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>
