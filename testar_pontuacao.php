<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

// Simular usuário logado
$_SESSION["usuario_id"] = 1;

echo "🧪 TESTE DO SISTEMA DE PONTUAÇÃO\n";
echo "=================================\n\n";

try {
    $gamificacao = new Gamificacao($pdo);
    
    // 1. Verificar estado inicial
    echo "1. Estado inicial do usuário:\n";
    $dados_inicial = $gamificacao->obterDadosUsuario(1);
    echo "   Nível: {$dados_inicial['nivel']}\n";
    echo "   Pontos: {$dados_inicial['pontos_total']}\n";
    echo "   Questões respondidas: {$dados_inicial['questoes_respondidas']}\n\n";
    
    // 2. Simular resposta de questão
    echo "2. Simulando resposta de questão...\n";
    
    // Obter uma questão para testar
    $sql = "SELECT id FROM questoes LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $questao_id = $stmt->fetchColumn();
    
    if (!$questao_id) {
        echo "❌ Nenhuma questão disponível para teste!\n";
        exit;
    }
    
    echo "   Testando com questão ID: $questao_id\n";
    
    // Simular resposta correta
    $resposta = 'A'; // Assumindo que a resposta correta é A
    $sql = "SELECT alternativa_correta FROM questoes WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$questao_id]);
    $resposta_correta = $stmt->fetchColumn();
    
    echo "   Resposta correta: $resposta_correta\n";
    echo "   Resposta do usuário: $resposta\n";
    
    $acertou = ($resposta == $resposta_correta) ? 1 : 0;
    $pontos = $acertou ? 10 : 0;
    
    echo "   Acertou: " . ($acertou ? 'SIM' : 'NÃO') . "\n";
    echo "   Pontos: $pontos\n\n";
    
    // 3. Registrar resposta
    echo "3. Registrando resposta...\n";
    $sql = "INSERT INTO respostas_usuario (usuario_id, questao_id, resposta, correta, pontos_ganhos)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $resultado_insert = $stmt->execute([1, $questao_id, $resposta, $acertou, $pontos]);
    
    if ($resultado_insert) {
        echo "   ✅ Resposta registrada com sucesso!\n";
    } else {
        echo "   ❌ Erro ao registrar resposta!\n";
    }
    
    // 4. Adicionar pontos via gamificação
    echo "4. Adicionando pontos via gamificação...\n";
    $resultado_gamificacao = $gamificacao->adicionarPontos(1, $pontos, 'questao');
    
    if ($resultado_gamificacao) {
        echo "   ✅ Pontos adicionados via gamificação!\n";
    } else {
        echo "   ❌ Erro ao adicionar pontos via gamificação!\n";
    }
    
    // 5. Verificar estado final
    echo "5. Estado final do usuário:\n";
    $dados_final = $gamificacao->obterDadosUsuario(1);
    echo "   Nível: {$dados_final['nivel']}\n";
    echo "   Pontos: {$dados_final['pontos_total']}\n";
    echo "   Questões respondidas: {$dados_final['questoes_respondidas']}\n\n";
    
    // 6. Verificar conquistas
    echo "6. Verificando conquistas...\n";
    $conquistas = $gamificacao->obterConquistasUsuario(1);
    
    $conquistas_conquistadas = 0;
    foreach ($conquistas as $conquista) {
        if ($conquista['data_conquista']) {
            $conquistas_conquistadas++;
            echo "   ✅ {$conquista['nome']} - {$conquista['data_conquista']}\n";
        }
    }
    
    if ($conquistas_conquistadas == 0) {
        echo "   ❌ Nenhuma conquista conquistada!\n";
    } else {
        echo "   Total de conquistas: $conquistas_conquistadas\n";
    }
    
    // 7. Diagnóstico
    echo "\n7. DIAGNÓSTICO:\n";
    echo "===============\n";
    
    if ($dados_final['pontos_total'] > $dados_inicial['pontos_total']) {
        echo "✅ Sistema de pontuação funcionando!\n";
    } else {
        echo "❌ Sistema de pontuação NÃO está funcionando!\n";
    }
    
    if ($conquistas_conquistadas > 0) {
        echo "✅ Sistema de conquistas funcionando!\n";
    } else {
        echo "❌ Sistema de conquistas NÃO está funcionando!\n";
    }
    
    // 8. Verificar logs de erro
    echo "\n8. Verificando logs de erro...\n";
    $sql = "SHOW VARIABLES LIKE 'log_error'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $log_error = $stmt->fetch();
    
    if ($log_error) {
        echo "   Log de erro: {$log_error['Value']}\n";
    }
    
    echo "\n✅ Teste concluído!\n";
    
} catch (Exception $e) {
    echo "❌ Erro durante o teste: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
