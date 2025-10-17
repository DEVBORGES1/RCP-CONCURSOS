<?php
require 'conexao.php';

echo "=== INICIALIZAÇÃO DO SISTEMA DE PROGRESSO ===\n";

try {
    // Verificar se a tabela usuarios_progresso existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios_progresso'");
    if($stmt->rowCount() == 0) {
        echo "Criando tabela usuarios_progresso...\n";
        
        $sql = "CREATE TABLE usuarios_progresso (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT,
            nivel INT DEFAULT 1,
            pontos_total INT DEFAULT 0,
            streak_dias INT DEFAULT 0,
            ultimo_login DATE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        )";
        $pdo->exec($sql);
        echo "Tabela usuarios_progresso criada!\n";
    }
    
    // Verificar se a tabela ranking_mensal existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'ranking_mensal'");
    if($stmt->rowCount() == 0) {
        echo "Criando tabela ranking_mensal...\n";
        
        $sql = "CREATE TABLE ranking_mensal (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT,
            mes_ano VARCHAR(7),
            pontos_mes INT DEFAULT 0,
            posicao INT DEFAULT 0,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        )";
        $pdo->exec($sql);
        echo "Tabela ranking_mensal criada!\n";
    }
    
    // Obter todos os usuários
    $sql = "SELECT id FROM usuarios";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    echo "Inicializando progresso para " . count($usuarios) . " usuários...\n";
    
    foreach ($usuarios as $usuario) {
        $usuario_id = $usuario['id'];
        
        // Verificar se já tem progresso
        $sql = "SELECT COUNT(*) FROM usuarios_progresso WHERE usuario_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        
        if ($stmt->fetchColumn() == 0) {
            // Calcular pontos baseado nas respostas
            $sql = "SELECT COUNT(DISTINCT questao_id) as questoes_unicas,
                           SUM(pontos_ganhos) as pontos_respostas
                    FROM respostas_usuario WHERE usuario_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id]);
            $dados_respostas = $stmt->fetch();
            
            $pontos_iniciais = $dados_respostas['pontos_respostas'] ?? 0;
            $nivel_inicial = floor(sqrt($pontos_iniciais / 100)) + 1;
            
            // Inserir progresso
            $sql = "INSERT INTO usuarios_progresso (usuario_id, nivel, pontos_total, streak_dias, ultimo_login) 
                    VALUES (?, ?, ?, 0, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario_id, $nivel_inicial, $pontos_iniciais, date('Y-m-d')]);
            
            echo "Usuário $usuario_id: $pontos_iniciais pontos, nível $nivel_inicial\n";
        } else {
            echo "Usuário $usuario_id: já tem progresso\n";
        }
    }
    
    echo "\n=== VERIFICAÇÃO FINAL ===\n";
    
    // Verificar estatísticas
    $sql = "SELECT COUNT(*) as usuarios_com_progresso,
                   SUM(pontos_total) as pontos_totais,
                   AVG(nivel) as nivel_medio
            FROM usuarios_progresso";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $stats = $stmt->fetch();
    
    echo "Usuários com progresso: {$stats['usuarios_com_progresso']}\n";
    echo "Pontos totais: {$stats['pontos_totais']}\n";
    echo "Nível médio: " . round($stats['nivel_medio'], 1) . "\n";
    
    echo "\nSistema de progresso inicializado com sucesso!\n";
    
} catch(Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>