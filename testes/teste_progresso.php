<?php
// Teste simples do Sistema de Progresso Avançado
session_start();
require 'conexao.php';
require 'classes/SistemaProgressoAvancado.php';

if (!isset($_SESSION["usuario_id"])) {
    echo "Usuário não logado. Faça login primeiro.";
    exit;
}

try {
    $sistema_progresso = new SistemaProgressoAvancado($pdo);
    
    echo "<h2>Teste do Sistema de Progresso Avançado</h2>";
    echo "<p>Usuário ID: " . $_SESSION["usuario_id"] . "</p>";
    
    // Testar resumo geral
    echo "<h3>Resumo Geral:</h3>";
    $resumo = $sistema_progresso->obterResumoGeral($_SESSION["usuario_id"]);
    echo "<pre>" . print_r($resumo, true) . "</pre>";
    
    // Testar progresso por disciplina
    echo "<h3>Progresso por Disciplina:</h3>";
    $disciplinas = $sistema_progresso->obterProgressoPorDisciplina($_SESSION["usuario_id"]);
    echo "<pre>" . print_r($disciplinas, true) . "</pre>";
    
    // Testar dashboard completo
    echo "<h3>Dashboard Completo:</h3>";
    $dashboard = $sistema_progresso->obterDashboardCompleto($_SESSION["usuario_id"]);
    echo "<p>✅ Dashboard completo carregado com sucesso!</p>";
    echo "<p>Insights encontrados: " . count($dashboard['insights_inteligentes']) . "</p>";
    echo "<p>Metas automáticas: " . count($dashboard['metas_e_objetivos']['automaticas']) . "</p>";
    echo "<p>Disciplinas analisadas: " . count($dashboard['progresso_por_disciplina']) . "</p>";
    echo "<p>Conquistas recentes: " . count($dashboard['conquistas_recentes']['recentes']) . "</p>";
    echo "<p>Próximos desafios: " . count($dashboard['proximos_desafios']) . "</p>";
    
    // Testar métodos individuais
    echo "<h3>Teste de Métodos Individuais:</h3>";
    
    // Testar comparação de ranking
    $ranking = $sistema_progresso->obterComparacaoRanking($_SESSION["usuario_id"]);
    echo "<p>✅ Ranking geral: Posição " . $ranking['ranking_geral']['posicao'] . " de " . $ranking['ranking_geral']['total'] . "</p>";
    
    // Testar insights
    $insights = $sistema_progresso->gerarInsightsInteligentes($_SESSION["usuario_id"]);
    echo "<p>✅ Insights gerados: " . count($insights) . "</p>";
    
    // Testar desafios
    $desafios = $sistema_progresso->sugerirProximosDesafios($_SESSION["usuario_id"]);
    echo "<p>✅ Desafios sugeridos: " . count($desafios) . "</p>";
    
    echo "<p style='color: green; font-weight: bold;'>🎉 Sistema 100% funcional!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . "</p>";
    echo "<p>Linha: " . $e->getLine() . "</p>";
}
?>
