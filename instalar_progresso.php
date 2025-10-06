<?php
// Script para criar as tabelas do sistema de progresso avançado
require 'conexao.php';

echo "<h2>Criando Tabelas do Sistema de Progresso Avançado</h2>";

try {
    // Ler o arquivo SQL
    $sql_content = file_get_contents('criar_tabelas_progresso.sql');
    
    // Dividir em comandos individuais
    $commands = explode(';', $sql_content);
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($commands as $command) {
        $command = trim($command);
        
        // Pular comandos vazios ou comentários
        if (empty($command) || strpos($command, '--') === 0 || strpos($command, 'USE') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($command);
            $success_count++;
            
            // Mostrar progresso para comandos importantes
            if (strpos($command, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $command, $matches);
                if (isset($matches[1])) {
                    echo "<p style='color: green;'>✅ Tabela '{$matches[1]}' criada com sucesso!</p>";
                }
            } elseif (strpos($command, 'INSERT') !== false) {
                echo "<p style='color: blue;'>📊 Dados iniciais inseridos!</p>";
            }
            
        } catch (PDOException $e) {
            $error_count++;
            echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Resumo da Execução:</h3>";
    echo "<p style='color: green;'>✅ Comandos executados com sucesso: $success_count</p>";
    echo "<p style='color: red;'>❌ Comandos com erro: $error_count</p>";
    
    if ($error_count == 0) {
        echo "<p style='color: green; font-weight: bold;'>🎉 Todas as tabelas foram criadas com sucesso!</p>";
        echo "<p>Você pode agora acessar o <a href='dashboard_avancado.php'>Dashboard Avançado</a></p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Alguns erros ocorreram, mas o sistema deve funcionar parcialmente.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro geral: " . $e->getMessage() . "</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: #f5f5f5;
}

h2, h3 {
    color: #333;
}

p {
    margin: 5px 0;
    padding: 5px;
    border-radius: 3px;
}

hr {
    margin: 20px 0;
    border: 1px solid #ddd;
}
</style>
