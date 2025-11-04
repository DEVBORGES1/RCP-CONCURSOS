<?php
session_start();
require 'conexao.php';
require_once 'classes/GeradorCertificado.php';
require_once 'classes/GamificacaoRefatorada.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$categoria_id = $_GET['categoria_id'] ?? 0;

// Verificar se categoria está completa
$gamificacao = new GamificacaoRefatorada();

if (!$gamificacao->verificarCategoriaCompleta($usuario_id, $categoria_id)) {
    header("Location: videoaulas.php?erro=categoria_incompleta");
    exit;
}

// Obter dados da categoria
$sql = "SELECT nome FROM videoaulas_categorias WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$categoria_id]);
$categoria = $stmt->fetch();

if (!$categoria) {
    header("Location: videoaulas.php?erro=categoria_nao_encontrada");
    exit;
}

// Gerar certificado
$gerador = new GeradorCertificado();
$resultado = $gerador->gerarCertificado($usuario_id, $categoria_id);

if ($resultado['sucesso']) {
    // Forçar download ou exibir
    $acao = $_GET['acao'] ?? 'download';
    
    if ($acao === 'download') {
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="Certificado_' . htmlspecialchars($categoria['nome']) . '_' . date('Y-m-d') . '.html"');
        readfile($resultado['caminho']);
        exit;
    } else {
        // Exibir no navegador
        readfile($resultado['caminho']);
        exit;
    }
} else {
    header("Location: videoaulas.php?erro=geracao_certificado");
    exit;
}
?>

