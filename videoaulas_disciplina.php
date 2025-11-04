<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$categoria_id = $_GET['categoria_id'] ?? 0;
$tema = $_GET['tema'] ?? '';

// Obter dados da categoria
$sql = "SELECT * FROM videoaulas_categorias WHERE id = ? AND ativo = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$categoria_id]);
$categoria = $stmt->fetch();

if (!$categoria) {
    header("Location: videoaulas.php");
    exit;
}

// Obter videoaulas do tema
$sql = "SELECT 
            v.*,
            vp.tempo_assistido,
            vp.concluida,
            vp.data_inicio,
            vp.data_conclusao,
            CASE 
                WHEN vp.concluida = 1 THEN 100
                WHEN vp.tempo_assistido > 0 AND v.duracao > 0 THEN 
                    ROUND((vp.tempo_assistido / (v.duracao * 60)) * 100, 1)
                ELSE 0 
            END as progresso_percentual
        FROM videoaulas v
        LEFT JOIN videoaulas_progresso vp ON v.id = vp.videoaula_id AND vp.usuario_id = ?
        WHERE v.categoria_id = ? AND v.ativo = 1 AND v.titulo LIKE ?
        ORDER BY v.ordem, v.titulo";

$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id, $categoria_id, $tema . '%']);
$videoaulas = $stmt->fetchAll();

// Se só tiver uma, redirecionar direto
if (count($videoaulas) == 1) {
    header("Location: videoaula_individual.php?id=" . $videoaulas[0]['id']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tema) ?> - <?= htmlspecialchars($categoria['nome']) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .videoaulas-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .breadcrumb {
            margin-bottom: 20px;
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .breadcrumb a {
            color: <?= $categoria['cor'] ?>;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .disciplina-header {
            background: linear-gradient(135deg, <?= $categoria['cor'] ?> 0%, <?= $categoria['cor'] ?>dd 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .disciplina-header h1 {
            margin: 0;
            font-size: 2em;
        }
        
        .videoaulas-list {
            display: grid;
            gap: 20px;
        }
        
        .videoaula-item {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border-left: 4px solid <?= $categoria['cor'] ?>;
        }
        
        .videoaula-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        .videoaula-item.completa {
            border-left-color: #27ae60;
            background: linear-gradient(90deg, #f0f9f4 0%, #ffffff 100%);
        }
        
        .videoaula-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .videoaula-info h3 {
            margin: 0 0 5px 0;
            color: #2c3e50;
            font-size: 1.2em;
        }
        
        .videoaula-info p {
            margin: 0;
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .videoaula-meta {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .status-icon {
            font-size: 1.5em;
        }
        
        .status-icon.not-started {
            color: #bdc3c7;
        }
        
        .status-icon.in-progress {
            color: #f39c12;
        }
        
        .status-icon.completed {
            color: #27ae60;
        }
        
        .videoaula-progress {
            margin-top: 15px;
        }
        
        .progress-mini {
            width: 100%;
            height: 6px;
            background: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 5px;
        }
        
        .progress-mini-fill {
            height: 100%;
            background: <?= $categoria['cor'] ?>;
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        
        .progress-text {
            font-size: 0.85em;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1><i class="<?= $categoria['icone'] ?>"></i> <?= htmlspecialchars($tema) ?></h1>
                <div class="user-info">
                    <a href="videoaulas_temas.php?categoria_id=<?= $categoria_id ?>" class="action-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>
        </header>

        <div class="videoaulas-container">
            <div class="breadcrumb">
                <a href="videoaulas.php">Videoaulas</a> > 
                <a href="videoaulas_temas.php?categoria_id=<?= $categoria_id ?>"><?= htmlspecialchars($categoria['nome']) ?></a> > 
                <?= htmlspecialchars($tema) ?>
            </div>

            <div class="disciplina-header">
                <h1><?= htmlspecialchars($tema) ?></h1>
            </div>

            <?php if (empty($videoaulas)): ?>
                <div class="empty-state">
                    <i class="fas fa-video"></i>
                    <h3>Aguardando videoaulas</h3>
                    <p>As videoaulas desta disciplina serão disponibilizadas em breve.</p>
                    <p style="font-size: 0.9em; margin-top: 15px; color: #95a5a6;">
                        <i class="fas fa-info-circle"></i> 
                        As videoaulas podem ser do YouTube ou geradas por IA.
                    </p>
                </div>
            <?php else: ?>
                <div class="videoaulas-list">
                    <?php foreach ($videoaulas as $videoaula): ?>
                        <div class="videoaula-item <?= $videoaula['concluida'] ? 'completa' : '' ?>" 
                             onclick="window.location.href='videoaula_individual.php?id=<?= $videoaula['id'] ?>'">
                            
                            <div class="videoaula-header">
                                <div class="videoaula-info">
                                    <h3><?= htmlspecialchars($videoaula['titulo']) ?></h3>
                                    <?php if ($videoaula['descricao']): ?>
                                        <p><?= htmlspecialchars(substr($videoaula['descricao'], 0, 100)) ?><?= strlen($videoaula['descricao']) > 100 ? '...' : '' ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="videoaula-meta">
                                    <div class="status-icon <?= $videoaula['concluida'] ? 'completed' : ($videoaula['tempo_assistido'] > 0 ? 'in-progress' : 'not-started') ?>">
                                        <?php if ($videoaula['concluida']): ?>
                                            <i class="fas fa-check-circle"></i>
                                        <?php elseif ($videoaula['tempo_assistido'] > 0): ?>
                                            <i class="fas fa-play-circle"></i>
                                        <?php else: ?>
                                            <i class="far fa-circle"></i>
                                        <?php endif; ?>
                                    </div>
                                    <span style="color: #7f8c8d; font-size: 0.9em;">
                                        <i class="fas fa-clock"></i> <?= $videoaula['duracao'] ?> min
                                    </span>
                                </div>
                            </div>
                            
                            <?php if ($videoaula['tempo_assistido'] > 0 || $videoaula['concluida']): ?>
                                <div class="videoaula-progress">
                                    <div class="progress-mini">
                                        <div class="progress-mini-fill" style="width: <?= $videoaula['progresso_percentual'] ?>%"></div>
                                    </div>
                                    <div class="progress-text">
                                        <?= $videoaula['progresso_percentual'] ?>% assistido
                                        <?php if ($videoaula['concluida']): ?>
                                            • Concluída em <?= date('d/m/Y', strtotime($videoaula['data_conclusao'])) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

