<?php
require 'conexao.php';

echo "🔍 DIAGNÓSTICO DOS SIMULADOS\n";
echo "============================\n\n";

try {
    // 1. Verificar conexão com banco
    echo "1. Verificando conexão com banco de dados...\n";
    $sql = "SELECT COUNT(*) as total FROM usuarios";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $total_usuarios = $stmt->fetchColumn();
    echo "✅ Conexão OK - $total_usuarios usuários encontrados\n\n";
    
    // 2. Verificar usuários
    echo "2. Listando usuários disponíveis:\n";
    $sql = "SELECT id, nome, email FROM usuarios";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    foreach ($usuarios as $usuario) {
        echo "   - ID: {$usuario['id']}, Nome: {$usuario['nome']}, Email: {$usuario['email']}\n";
    }
    echo "\n";
    
    // 3. Verificar editais
    echo "3. Verificando editais:\n";
    $sql = "SELECT id, usuario_id, nome_arquivo FROM editais";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $editais = $stmt->fetchAll();
    
    if (empty($editais)) {
        echo "❌ Nenhum edital encontrado!\n";
    } else {
        foreach ($editais as $edital) {
            echo "   - ID: {$edital['id']}, Usuário: {$edital['usuario_id']}, Arquivo: {$edital['nome_arquivo']}\n";
        }
    }
    echo "\n";
    
    // 4. Verificar questões
    echo "4. Verificando questões:\n";
    $sql = "SELECT COUNT(*) as total FROM questoes";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $total_questoes = $stmt->fetchColumn();
    echo "   Total de questões: $total_questoes\n";
    
    if ($total_questoes > 0) {
        $sql = "SELECT d.nome_disciplina, COUNT(q.id) as total 
                FROM questoes q 
                LEFT JOIN disciplinas d ON q.disciplina_id = d.id 
                GROUP BY d.nome_disciplina 
                ORDER BY total DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $disciplinas = $stmt->fetchAll();
        
        echo "   Questões por disciplina:\n";
        foreach ($disciplinas as $disciplina) {
            echo "     - {$disciplina['nome_disciplina']}: {$disciplina['total']} questões\n";
        }
    }
    echo "\n";
    
    // 5. Verificar simulados
    echo "5. Verificando simulados:\n";
    $sql = "SELECT s.id, s.usuario_id, s.nome, s.questoes_total, COUNT(sq.questao_id) as questoes_adicionadas 
            FROM simulados s 
            LEFT JOIN simulados_questoes sq ON s.id = sq.simulado_id 
            GROUP BY s.id, s.usuario_id, s.nome, s.questoes_total";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $simulados = $stmt->fetchAll();
    
    if (empty($simulados)) {
        echo "❌ Nenhum simulado encontrado!\n";
    } else {
        foreach ($simulados as $simulado) {
            echo "   - ID: {$simulado['id']}, Usuário: {$simulado['usuario_id']}, Nome: {$simulado['nome']}, Questões: {$simulado['questoes_adicionadas']}/{$simulado['questoes_total']}\n";
        }
    }
    echo "\n";
    
    // 6. Verificar respostas
    echo "6. Verificando respostas:\n";
    $sql = "SELECT COUNT(*) as total FROM respostas_usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $total_respostas = $stmt->fetchColumn();
    echo "   Total de respostas: $total_respostas\n\n";
    
    // 7. Diagnóstico e recomendações
    echo "7. DIAGNÓSTICO E RECOMENDAÇÕES:\n";
    echo "===============================\n";
    
    if ($total_questoes == 0) {
        echo "❌ PROBLEMA: Nenhuma questão encontrada!\n";
        echo "💡 SOLUÇÃO: Execute o script de instalação de questões\n";
        echo "   Comando: C:\\laragon\\bin\\php\\php-8.3.26-Win32-vs16-x64\\php.exe instalar_questoes_teste.php\n\n";
    } else {
        echo "✅ Questões disponíveis: $total_questoes\n";
    }
    
    if (empty($simulados)) {
        echo "❌ PROBLEMA: Nenhum simulado encontrado!\n";
        echo "💡 SOLUÇÃO: Execute o script de correção completa\n";
        echo "   Comando: C:\\laragon\\bin\\php\\php-8.3.26-Win32-vs16-x64\\php.exe corrigir_simulados_completo.php\n\n";
    } else {
        echo "✅ Simulados encontrados: " . count($simulados) . "\n";
    }
    
    // 8. Instruções para correção
    echo "8. INSTRUÇÕES PARA CORREÇÃO:\n";
    echo "============================\n";
    echo "1. Se não há questões, execute:\n";
    echo "   C:\\laragon\\bin\\php\\php-8.3.26-Win32-vs16-x64\\php.exe instalar_questoes_teste.php\n\n";
    echo "2. Para correção completa, execute:\n";
    echo "   C:\\laragon\\bin\\php\\php-8.3.26-Win32-vs16-x64\\php.exe corrigir_simulados_completo.php\n\n";
    echo "3. Depois teste acessando:\n";
    echo "   http://localhost/RCP-CONCURSOS/simulados.php\n\n";
    
    echo "✅ Diagnóstico concluído!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>
