<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

// Simular usuário logado para teste
$_SESSION["usuario_id"] = 1;

echo "=== DIAGNÓSTICO COMPLETO DO SISTEMA DE PROGRESSO ===\n";

$gamificacao = new Gamificacao($pdo);

echo "\n1. VERIFICANDO DADOS DO USUÁRIO:\n";
$dados_usuario = $gamificacao->obterDadosUsuario($_SESSION["usuario_id"]);
foreach ($dados_usuario as $key => $value) {
    echo "- $key: $value\n";
}

echo "\n2. VERIFICANDO TABELA usuarios_progresso:\n";
$sql = "SELECT * FROM usuarios_progresso WHERE usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$progresso = $stmt->fetch();

if ($progresso) {
    foreach ($progresso as $key => $value) {
        echo "- $key: $value\n";
    }
} else {
    echo "ERRO: Usuário não tem registro na tabela usuarios_progresso!\n";
}

echo "\n3. VERIFICANDO RESPOSTAS DO USUÁRIO:\n";
$sql = "SELECT COUNT(*) as total, 
               COUNT(DISTINCT questao_id) as questoes_unicas,
               SUM(pontos_ganhos) as pontos_totais
        FROM respostas_usuario WHERE usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$respostas = $stmt->fetch();

foreach ($respostas as $key => $value) {
    echo "- $key: $value\n";
}

echo "\n4. VERIFICANDO SIMULADOS:\n";
$sql = "SELECT id, nome, questoes_total, questoes_corretas, pontuacao_final 
        FROM simulados WHERE usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$simulados = $stmt->fetchAll();

foreach ($simulados as $simulado) {
    echo "- Simulado {$simulado['id']}: {$simulado['nome']} - {$simulado['questoes_corretas']}/{$simulado['questoes_total']} questões, {$simulado['pontuacao_final']} pontos\n";
}

echo "\n5. TESTANDO ADIÇÃO DE PONTOS:\n";
echo "Tentando adicionar 50 pontos de teste...\n";
$resultado = $gamificacao->adicionarPontos($_SESSION["usuario_id"], 50, 'teste');
echo "Resultado: " . ($resultado ? 'SUCESSO' : 'FALHA') . "\n";

echo "\n6. VERIFICANDO PROGRESSO APÓS TESTE:\n";
$dados_apos_teste = $gamificacao->obterDadosUsuario($_SESSION["usuario_id"]);
echo "Pontos após teste: {$dados_apos_teste['pontos_total']}\n";
echo "Nível após teste: {$dados_apos_teste['nivel']}\n";

echo "\n7. VERIFICANDO TABELA usuarios_progresso APÓS TESTE:\n";
$sql = "SELECT * FROM usuarios_progresso WHERE usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$progresso_apos = $stmt->fetch();

if ($progresso_apos) {
    foreach ($progresso_apos as $key => $value) {
        echo "- $key: $value\n";
    }
} else {
    echo "ERRO: Ainda não tem registro!\n";
}

echo "\n=== CORREÇÃO AUTOMÁTICA ===\n";

// Calcular pontos corretos baseado nas respostas
$pontos_corretos = $respostas['questoes_unicas'] * 10; // Assumindo que todas foram corretas
echo "Pontos corretos calculados: $pontos_corretos\n";

// Garantir que o usuário tenha registro de progresso
$gamificacao->garantirProgressoUsuario($_SESSION["usuario_id"]);

// Atualizar pontos manualmente
$sql = "UPDATE usuarios_progresso SET pontos_total = ? WHERE usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$pontos_corretos, $_SESSION["usuario_id"]]);

// Recalcular nível
$novo_nivel = floor(sqrt($pontos_corretos / 100)) + 1;
$sql = "UPDATE usuarios_progresso SET nivel = ? WHERE usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$novo_nivel, $_SESSION["usuario_id"]]);

echo "Pontos atualizados para: $pontos_corretos\n";
echo "Nível atualizado para: $novo_nivel\n";

echo "\n=== VERIFICAÇÃO FINAL ===\n";
$dados_finais = $gamificacao->obterDadosUsuario($_SESSION["usuario_id"]);
echo "Dados finais:\n";
foreach ($dados_finais as $key => $value) {
    echo "- $key: $value\n";
}

echo "\nDiagnóstico concluído!\n";
