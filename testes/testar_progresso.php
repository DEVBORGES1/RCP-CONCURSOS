<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

echo "<h2>=== TESTE DO SISTEMA DE PROGRESSO ===</h2>";

try {
    $gamificacao = new Gamificacao($pdo);
    
    // Simular um usuário (você pode alterar o ID aqui)
    $usuario_teste = 1;
    
    echo "<h3>1. Testando inicialização do progresso...</h3>";
    
    // Obter dados antes
    $dados_antes = $gamificacao->obterDadosUsuario($usuario_teste);
    echo "<p>Dados antes: Nível {$dados_antes['nivel']}, Pontos: {$dados_antes['pontos_total']}</p>";
    
    echo "<h3>2. Adicionando pontos de teste...</h3>";
    
    // Adicionar alguns pontos
    $resultado = $gamificacao->adicionarPontos($usuario_teste, 50, 'questao');
    if ($resultado) {
        echo "<p style='color: green;'>✓ Pontos adicionados com sucesso!</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao adicionar pontos!</p>";
    }
    
    // Obter dados depois
    $dados_depois = $gamificacao->obterDadosUsuario($usuario_teste);
    echo "<p>Dados depois: Nível {$dados_depois['nivel']}, Pontos: {$dados_depois['pontos_total']}</p>";
    
    echo "<h3>3. Testando conquistas...</h3>";
    $conquistas = $gamificacao->obterConquistasUsuario($usuario_teste);
    echo "<p>Total de conquistas disponíveis: " . count($conquistas) . "</p>";
    
    $conquistas_desbloqueadas = 0;
    foreach ($conquistas as $conquista) {
        if ($conquista['data_conquista']) {
            $conquistas_desbloqueadas++;
        }
    }
    echo "<p>Conquistas desbloqueadas: $conquistas_desbloqueadas</p>";
    
    echo "<h3>4. Testando ranking...</h3>";
    $ranking = $gamificacao->obterRankingMensal(5);
    echo "<p>Usuários no ranking: " . count($ranking) . "</p>";
    
    $posicao = $gamificacao->obterPosicaoUsuario($usuario_teste);
    if ($posicao) {
        echo "<p>Sua posição no ranking: $posicaoº</p>";
    } else {
        echo "<p>Você ainda não está no ranking mensal</p>";
    }
    
    echo "<h3>5. Testando streak...</h3>";
    $gamificacao->atualizarStreak($usuario_teste);
    $dados_final = $gamificacao->obterDadosUsuario($usuario_teste);
    echo "<p>Streak atual: {$dados_final['streak_dias']} dias</p>";
    
    echo "<h3 style='color: green;'>✅ Teste concluído com sucesso!</h3>";
    echo "<p><a href='dashboard.php'>→ Ir para o Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro durante o teste: " . $e->getMessage() . "</p>";
}
?>
