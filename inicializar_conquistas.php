<?php
require 'conexao.php';

try {
    // Verificar se a tabela conquistas existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'conquistas'");
    if($stmt->rowCount() == 0) {
        echo "Criando tabela conquistas...\n";
        
        $sql = "CREATE TABLE conquistas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100),
            descricao TEXT,
            icone VARCHAR(50),
            pontos_necessarios INT,
            tipo VARCHAR(50)
        )";
        $pdo->exec($sql);
        echo "Tabela conquistas criada!\n";
    }
    
    // Verificar se a tabela usuarios_conquistas existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios_conquistas'");
    if($stmt->rowCount() == 0) {
        echo "Criando tabela usuarios_conquistas...\n";
        
        $sql = "CREATE TABLE usuarios_conquistas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT,
            conquista_id INT,
            data_conquista TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
            FOREIGN KEY (conquista_id) REFERENCES conquistas(id)
        )";
        $pdo->exec($sql);
        echo "Tabela usuarios_conquistas criada!\n";
    }
    
    // Verificar se há conquistas na tabela
    $stmt = $pdo->query("SELECT COUNT(*) FROM conquistas");
    $count = $stmt->fetchColumn();
    
    if($count == 0) {
        echo "Inserindo conquistas padrão...\n";
        
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
        
        foreach($conquistas as $conquista) {
            $stmt->execute($conquista);
        }
        
        echo "Conquistas inseridas com sucesso!\n";
    } else {
        echo "Conquistas já existem na tabela ($count conquistas)\n";
    }
    
    echo "Sistema de conquistas inicializado com sucesso!\n";
    
} catch(Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
