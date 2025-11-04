<?php
session_start();
require 'conexao.php';
require_once 'classes/GamificacaoRefatorada.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$videoaula_id = $_GET['id'] ?? 0;
$gamificacao = new GamificacaoRefatorada();

// Obter dados da videoaula
$sql = "SELECT 
            v.*,
            vc.nome as categoria_nome,
            vc.cor as categoria_cor,
            vc.icone as categoria_icone,
            vp.tempo_assistido,
            vp.concluida,
            vp.data_inicio,
            vp.data_conclusao
        FROM videoaulas v
        JOIN videoaulas_categorias vc ON v.categoria_id = vc.id
        LEFT JOIN videoaulas_progresso vp ON v.id = vp.videoaula_id AND vp.usuario_id = ?
        WHERE v.id = ? AND v.ativo = 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id, $videoaula_id]);
$videoaula = $stmt->fetch();

if (!$videoaula) {
    header("Location: videoaulas.php");
    exit;
}

// Processar atualização de progresso via AJAX
if ($_POST['action'] ?? '' === 'update_progress') {
    $tempo_assistido = $_POST['tempo_assistido'] ?? 0;
    $concluida = $_POST['concluida'] ?? 0;
    
    // Verificar se estava concluída antes
    $sql = "SELECT concluida FROM videoaulas_progresso WHERE usuario_id = ? AND videoaula_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $videoaula_id]);
    $progresso_anterior = $stmt->fetch();
    $estava_concluida = $progresso_anterior && $progresso_anterior['concluida'];
    
    $sql = "INSERT INTO videoaulas_progresso (usuario_id, videoaula_id, tempo_assistido, concluida, data_conclusao) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            tempo_assistido = VALUES(tempo_assistido),
            concluida = VALUES(concluida),
            data_conclusao = CASE WHEN VALUES(concluida) = 1 AND concluida = 0 THEN NOW() ELSE data_conclusao END";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $videoaula_id, $tempo_assistido, $concluida, $concluida ? date('Y-m-d H:i:s') : null]);
    
    // Se acabou de concluir (não estava concluída antes e agora está), adicionar pontos
    if ($concluida && !$estava_concluida) {
        $gamificacao->adicionarPontosVideoaula($usuario_id, $videoaula_id);
        
        // Verificar se categoria está 100% completa para gerar certificado
        $sql = "SELECT categoria_id FROM videoaulas WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$videoaula_id]);
        $videoaula_info = $stmt->fetch();
        
        if ($videoaula_info && $gamificacao->verificarCategoriaCompleta($usuario_id, $videoaula_info['categoria_id'])) {
            // Marcar que certificado está disponível (será gerado ao acessar)
            $_SESSION['certificado_disponivel'] = $videoaula_info['categoria_id'];
        }
    }
    
    // Retornar JSON para AJAX
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// Calcular progresso
$progresso_percentual = 0;
if ($videoaula['concluida']) {
    $progresso_percentual = 100;
} elseif ($videoaula['tempo_assistido'] > 0 && $videoaula['duracao'] > 0) {
    $progresso_percentual = round(($videoaula['tempo_assistido'] / ($videoaula['duracao'] * 60)) * 100, 1);
}

// Obter videoaulas relacionadas
$sql = "SELECT id, titulo, duracao 
        FROM videoaulas 
        WHERE categoria_id = ? AND id != ? AND ativo = 1 
        ORDER BY ordem, titulo 
        LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute([$videoaula['categoria_id'], $videoaula_id]);
$videoaulas_relacionadas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($videoaula['titulo']) ?> - Videoaula</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .videoaula-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .videoaula-header {
            background: linear-gradient(135deg, <?= $videoaula['categoria_cor'] ?> 0%, <?= $videoaula['categoria_cor'] ?>dd 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .videoaula-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.2em;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .videoaula-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .videoaula-meta {
            display: flex;
            gap: 20px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9em;
        }
        
        .videoaula-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .videoaula-player {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .videoaula-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .progress-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .progress-label {
            font-size: 1.1em;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .progress-percentage {
            font-size: 1.3em;
            font-weight: bold;
            color: <?= $videoaula['categoria_cor'] ?>;
        }
        
        .progress-bar {
            width: 100%;
            height: 12px;
            background: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, <?= $videoaula['categoria_cor'] ?>, <?= $videoaula['categoria_cor'] ?>88);
            border-radius: 10px;
            transition: width 0.8s ease;
        }
        
        .progress-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid <?= $videoaula['categoria_cor'] ?>;
        }
        
        .stat-item h4 {
            margin: 0;
            color: <?= $videoaula['categoria_cor'] ?>;
            font-size: 1.5em;
            font-weight: bold;
        }
        
        .stat-item p {
            margin: 5px 0 0 0;
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .video-player {
            width: 100%;
            height: 500px;
            border-radius: 10px;
            margin-bottom: 20px;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2em;
            overflow: hidden;
            position: relative;
        }
        
        .videoaula-info {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .videoaula-info h3 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 1.3em;
        }
        
        .videoaula-info p {
            margin: 0 0 15px 0;
            color: #7f8c8d;
            line-height: 1.6;
        }
        
        .videoaula-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .btn-videoaula {
            flex: 1;
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 1em;
        }
        
        .btn-primary {
            background: <?= $videoaula['categoria_cor'] ?>;
            color: white;
        }
        
        .btn-primary:hover {
            background: <?= $videoaula['categoria_cor'] ?>dd;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .related-videoaulas {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .related-videoaulas h3 {
            margin: 0 0 20px 0;
            color: #2c3e50;
            font-size: 1.3em;
        }
        
        .related-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .related-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            text-decoration: none;
            color: #2c3e50;
            transition: all 0.3s ease;
        }
        
        .related-item:hover {
            background: <?= $videoaula['categoria_cor'] ?>22;
            transform: translateX(5px);
        }
        
        .related-item-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: <?= $videoaula['categoria_cor'] ?>;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2em;
        }
        
        .related-item-info h4 {
            margin: 0;
            font-size: 1em;
            color: #2c3e50;
        }
        
        .related-item-info p {
            margin: 5px 0 0 0;
            color: #7f8c8d;
            font-size: 0.8em;
        }
        
        .nivel-badge {
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 0.7em;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .nivel-iniciante {
            background: #d4edda;
            color: #155724;
        }
        
        .nivel-intermediario {
            background: #fff3cd;
            color: #856404;
        }
        
        .nivel-avancado {
            background: #f8d7da;
            color: #721c24;
        }
        
        .hidden {
            display: none;
        }
        
        @media (max-width: 768px) {
            .videoaula-content {
                grid-template-columns: 1fr;
            }
            
            .videoaula-meta {
                flex-direction: column;
                gap: 10px;
            }
            
            .progress-stats {
                grid-template-columns: 1fr;
            }
            
            .videoaula-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="<?= $videoaula['categoria_icone'] ?>"></i> <?= htmlspecialchars($videoaula['titulo']) ?></h1>
                <div class="user-info">
                    <a href="videoaulas_categoria.php?id=<?= $videoaula['categoria_id'] ?>" class="action-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>
        </header>

        <div class="videoaula-container">
            <!-- Cabeçalho da Videoaula -->
            <div class="videoaula-header">
                <h1>
                    <i class="fas fa-play-circle"></i>
                    <?= htmlspecialchars($videoaula['titulo']) ?>
                </h1>
                <p><?= htmlspecialchars($videoaula['descricao']) ?></p>
                
                <div class="videoaula-meta">
                    <div class="meta-item">
                        <i class="fas fa-layer-group"></i>
                        <span><?= htmlspecialchars($videoaula['categoria_nome']) ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?= $videoaula['duracao'] ?> minutos</span>
                    </div>
                    <?php if ($videoaula['concluida']): ?>
                        <div class="meta-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Concluída</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="videoaula-content">
                <!-- Player e Informações -->
                <div class="videoaula-player">
                    <div class="video-player" id="videoPlayer">
                        <?php
                        // Extrair ID do vídeo do YouTube
                        $url_video = $videoaula['url_video'];
                        $video_id = null;
                        
                        // Verificar se já está em formato embed
                        if (strpos($url_video, 'youtube.com/embed/') !== false) {
                            preg_match('/embed\/([a-zA-Z0-9_-]{11})/', $url_video, $matches);
                            $video_id = $matches[1] ?? null;
                        } elseif (strpos($url_video, 'youtu.be/') !== false) {
                            preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $url_video, $matches);
                            $video_id = $matches[1] ?? null;
                        } elseif (strpos($url_video, 'youtube.com/watch') !== false) {
                            preg_match('/[?&]v=([a-zA-Z0-9_-]{11})/', $url_video, $matches);
                            $video_id = $matches[1] ?? null;
                        }
                        
                        if ($video_id):
                        ?>
                            <iframe id="ytplayer" width="100%" height="100%" 
                                src="https://www.youtube.com/embed/<?= $video_id ?>?rel=0&modestbranding=1&enablejsapi=1" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen
                                style="border-radius: 10px;">
                            </iframe>
                        <?php else: ?>
                            <div style="text-align: center; padding: 50px;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 3em; margin-bottom: 15px; color: #f39c12;"></i>
                                <p>URL do vídeo inválida ou não reconhecida</p>
                                <p style="font-size: 0.8em; opacity: 0.7;">URL: <?= htmlspecialchars(substr($url_video, 0, 100)) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="videoaula-info">
                        <h3><i class="fas fa-info-circle"></i> Sobre esta videoaula</h3>
                        <p><?= htmlspecialchars($videoaula['descricao']) ?></p>
                        
                        <div class="videoaula-actions">
                            <?php if ($videoaula['concluida']): ?>
                                <button class="btn-videoaula btn-secondary" disabled>
                                    <i class="fas fa-check"></i> Concluída
                                </button>
                                <button class="btn-videoaula btn-primary" onclick="marcarComoNaoConcluida()">
                                    <i class="fas fa-redo"></i> Reassistir
                                </button>
                            <?php else: ?>
                                <button class="btn-videoaula btn-primary" onclick="marcarComoConcluida()">
                                    <i class="fas fa-check"></i> Marcar como Concluída
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="videoaula-sidebar">
                    <!-- Progresso -->
                    <div class="progress-card">
                        <div class="progress-header">
                            <span class="progress-label">Seu Progresso</span>
                            <span class="progress-percentage"><?= $progresso_percentual ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $progresso_percentual ?>%"></div>
                        </div>
                        
                        <div class="progress-stats">
                            <div class="stat-item">
                                <h4><?= $videoaula['duracao'] ?></h4>
                                <p>Minutos</p>
                            </div>
                            <div class="stat-item">
                                <h4><?= round($videoaula['tempo_assistido'] / 60, 1) ?></h4>
                                <p>Assistidos</p>
                            </div>
                        </div>
                    </div>

                    <!-- Videoaulas Relacionadas -->
                    <?php if (!empty($videoaulas_relacionadas)): ?>
                        <div class="related-videoaulas">
                            <h3><i class="fas fa-list"></i> Videoaulas Relacionadas</h3>
                            <div class="related-list">
                                <?php foreach ($videoaulas_relacionadas as $relacionada): ?>
                                    <a href="videoaula_individual.php?id=<?= $relacionada['id'] ?>" class="related-item">
                                        <div class="related-item-icon">
                                            <i class="fas fa-play"></i>
                                        </div>
                                        <div class="related-item-info">
                                            <h4><?= htmlspecialchars($relacionada['titulo']) ?></h4>
                                            <p><?= $relacionada['duracao'] ?> min</p>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function marcarComoConcluida() {
            if (confirm('Deseja marcar esta videoaula como concluída?')) {
                atualizarProgresso(<?= $videoaula['duracao'] * 60 ?>, 1);
            }
        }
        
        function marcarComoNaoConcluida() {
            if (confirm('Deseja marcar esta videoaula como não concluída?')) {
                atualizarProgresso(0, 0);
            }
        }
        
        function atualizarProgresso(tempoAssistido, concluida) {
            fetch('videoaula_individual.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_progress&videoaula_id=<?= $videoaula_id ?>&tempo_assistido=${tempoAssistido}&concluida=${concluida}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao atualizar progresso');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar progresso');
            });
        }
        
        // Rastrear progresso do vídeo do YouTube
        <?php if ($video_id): ?>
        let tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        let firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        
        let ytPlayer;
        let tempoInicial = <?= $videoaula['tempo_assistido'] ?>;
        let ultimaAtualizacao = Date.now();
        
        function onYouTubeIframeAPIReady() {
            ytPlayer = new YT.Player('ytplayer', {
                events: {
                    'onStateChange': onPlayerStateChange,
                    'onReady': onPlayerReady
                }
            });
        }
        
        function onPlayerReady(event) {
            // Se houver tempo assistido, tentar retomar de onde parou
            if (tempoInicial > 0 && !<?= $videoaula['concluida'] ? 'true' : 'false' ?>) {
                event.target.seekTo(tempoInicial, true);
            }
        }
        
        function onPlayerStateChange(event) {
            if (event.data == YT.PlayerState.ENDED) {
                // Vídeo concluído
                const duracaoTotal = ytPlayer.getDuration();
                atualizarProgresso(Math.floor(duracaoTotal), 1);
            }
        }
        
        // Atualizar progresso periodicamente
        if (!<?= $videoaula['concluida'] ? 'true' : 'false' ?>) {
            setInterval(() => {
                if (ytPlayer && ytPlayer.getCurrentTime) {
                    try {
                        const tempoAtual = Math.floor(ytPlayer.getCurrentTime());
                        const duracaoTotal = ytPlayer.getDuration();
                        const estado = ytPlayer.getPlayerState();
                        
                        // Só atualizar se o vídeo estiver sendo reproduzido
                        if (estado == YT.PlayerState.PLAYING && tempoAtual > 0) {
                            // Atualizar a cada 30 segundos
                            if (Date.now() - ultimaAtualizacao > 30000) {
                                const concluida = tempoAtual >= (duracaoTotal * 0.95); // 95% = concluído
                                atualizarProgresso(tempoAtual, concluida ? 1 : 0);
                                ultimaAtualizacao = Date.now();
                            }
                        }
                    } catch(e) {
                        // Erro silencioso - pode acontecer se o iframe ainda não estiver pronto
                    }
                }
            }, 5000); // Verificar a cada 5 segundos
        }
        <?php endif; ?>
    </script>
</body>
</html>
