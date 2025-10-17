<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

// Simular usuário logado para teste
$_SESSION["usuario_id"] = 1;

echo "=== CORREÇÃO MANUAL DO PROGRESSO ===\n";

$gamificacao = new Gamificacao($pdo);

// 1. Garantir que o usuário tenha registro de progresso
echo "1. Garantindo registro de progresso...\n";
$gamificacao->garantirProgressoUsuario($_SESSION["usuario_id"]);

// 2. Calcular pontos corretos baseado nas respostas
echo "2. Calculando pontos corretos...\n";
$sql = "SELECT COUNT(DISTINCT questao_id) as questoes_unicas,
               SUM(pontos_ganhos) as pontos_respostas
        FROM respostas_usuario WHERE usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$dados_respostas = $stmt->fetch();

echo "Questões únicas respondidas: {$dados_respostas['questoes_unicas']}\n";
echo "Pontos das respostas: {$dados_respostas['pontos_respostas']}\n";

// 3. Calcular pontos baseado nos simulados concluídos
echo "3. Calculando pontos dos simulados...\n";
$sql = "SELECT SUM(pontuacao_final) as pontos_simulados
        FROM simulados 
        WHERE usuario_id = ? AND questoes_corretas IS NOT NULL";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$pontos_simulados = $stmt->fetchColumn();

echo "Pontos dos simulados: $pontos_simulados\n";

// 4. Usar o maior valor entre respostas e simulados
$pontos_corretos = max($dados_respostas['pontos_respostas'], $pontos_simulados);
echo "Pontos corretos a usar: $pontos_corretos\n";

// 5. Atualizar progresso manualmente
echo "4. Atualizando progresso...\n";
$sql = "UPDATE usuarios_progresso SET pontos_total = ? WHERE usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$pontos_corretos, $_SESSION["usuario_id"]]);

if ($stmt->rowCount() > 0) {
    echo "Progresso atualizado com sucesso!\n";
} else {
    echo "ERRO: Falha ao atualizar progresso!\n";
}

// 6. Recalcular nível
echo "5. Recalculando nível...\n";
$novo_nivel = floor(sqrt($pontos_corretos / 100)) + 1;
$sql = "UPDATE usuarios_progresso SET nivel = ? WHERE usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$novo_nivel, $_SESSION["usuario_id"]]);

echo "Nível atualizado para: $novo_nivel\n";

// 7. Verificar resultado final
echo "6. Verificando resultado final...\n";
$dados_finais = $gamificacao->obterDadosUsuario($_SESSION["usuario_id"]);
echo "Dados finais:\n";
foreach ($dados_finais as $key => $value) {
    echo "- $key: $value\n";
}

// 8. Testar adição de pontos
echo "7. Testando adição de pontos...\n";
$resultado_teste = $gamificacao->adicionarPontos($_SESSION["usuario_id"], 10, 'teste');
echo "Resultado do teste: " . ($resultado_teste ? 'SUCESSO' : 'FALHA') . "\n";

$dados_apos_teste = $gamificacao->obterDadosUsuario($_SESSION["usuario_id"]);
echo "Pontos após teste: {$dados_apos_teste['pontos_total']}\n";

echo "\nCorreção concluída!\n";
