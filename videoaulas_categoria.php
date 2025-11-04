<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$categoria_id = $_GET['id'] ?? 0;
$view = $_GET['view'] ?? 'videos';

// Obter dados da categoria
$sql = "SELECT * FROM videoaulas_categorias WHERE id = ? AND ativo = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$categoria_id]);
$categoria = $stmt->fetch();

if (!$categoria) {
    header("Location: videoaulas.php");
    exit;
}

// Obter videoaulas da categoria
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
        WHERE v.categoria_id = ? AND v.ativo = 1
        ORDER BY v.ordem, v.titulo";

$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id, $categoria_id]);
$videoaulas = $stmt->fetchAll();

// Calcular estatísticas da categoria
$total_videoaulas = count($videoaulas);
$videoaulas_concluidas = count(array_filter($videoaulas, function($v) { return $v['concluida']; }));
$videoaulas_iniciadas = count(array_filter($videoaulas, function($v) { return $v['tempo_assistido'] > 0; }));
$porcentagem_concluida = $total_videoaulas > 0 ? round(($videoaulas_concluidas / $total_videoaulas) * 100, 1) : 0;

// Processar atualização de progresso
if ($_POST['action'] ?? '' === 'update_progress') {
    $videoaula_id = $_POST['videoaula_id'] ?? 0;
    $tempo_assistido = $_POST['tempo_assistido'] ?? 0;
    $concluida = $_POST['concluida'] ?? 0;
    
    if (!$videoaula_id) {
        header("Location: videoaulas_categoria.php?id=$categoria_id");
        exit;
    }
    
    $sql = "INSERT INTO videoaulas_progresso (usuario_id, videoaula_id, tempo_assistido, concluida, data_conclusao) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            tempo_assistido = VALUES(tempo_assistido),
            concluida = VALUES(concluida),
            data_conclusao = CASE WHEN VALUES(concluida) = 1 AND concluida = 0 THEN NOW() ELSE data_conclusao END";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $videoaula_id, $tempo_assistido, $concluida, $concluida ? date('Y-m-d H:i:s') : null]);
    
    header("Location: videoaulas_categoria.php?id=$categoria_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($categoria['nome']) ?> - Videoaulas</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .categoria-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .categoria-header {
            background: linear-gradient(135deg, <?= $categoria['cor'] ?> 0%, <?= $categoria['cor'] ?>dd 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .categoria-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .categoria-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.2em;
        }
        
        .progress-overview {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .progress-label {
            font-size: 1.2em;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .progress-percentage {
            font-size: 1.5em;
            font-weight: bold;
            color: <?= $categoria['cor'] ?>;
        }
        
        .progress-bar {
            width: 100%;
            height: 15px;
            background: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, <?= $categoria['cor'] ?>, <?= $categoria['cor'] ?>88);
            border-radius: 10px;
            transition: width 0.8s ease;
            position: relative;
        }
        
        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid <?= $categoria['cor'] ?>;
        }
        
        .stat-card h3 {
            margin: 0;
            color: <?= $categoria['cor'] ?>;
            font-size: 2em;
            font-weight: bold;
        }
        
        .stat-card p {
            margin: 5px 0 0 0;
            color: #7f8c8d;
        }
        
        .videoaulas-list {
            display: grid;
            gap: 20px;
        }
        
        .videoaula-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: 2px solid transparent;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .videoaula-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            border-color: <?= $categoria['cor'] ?>;
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
            font-size: 1.3em;
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
        
        .nivel-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8em;
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
        
        .duracao {
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .videoaula-progress {
            margin: 15px 0;
        }
        
        .progress-mini {
            width: 100%;
            height: 8px;
            background: #ecf0f1;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-mini-fill {
            height: 100%;
            background: <?= $categoria['cor'] ?>;
            border-radius: 5px;
            transition: width 0.5s ease;
        }
        
        .progress-text {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
            font-size: 0.9em;
            color: #7f8c8d;
        }
        
        .videoaula-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-videoaula {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-play {
            background: <?= $categoria['cor'] ?>;
            color: white;
        }
        
        .btn-play:hover {
            background: <?= $categoria['cor'] ?>dd;
            transform: translateY(-2px);
        }
        
        .btn-continue {
            background: #28a745;
            color: white;
        }
        
        .btn-continue:hover {
            background: #218838;
        }
        
        .btn-completed {
            background: #6c757d;
            color: white;
            cursor: not-allowed;
        }
        
        .status-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1.5em;
        }
        
        .status-not-started {
            color: #bdc3c7;
        }
        
        .status-in-progress {
            color: #f39c12;
        }
        
        .status-completed {
            color: #27ae60;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 30px;
            background: white;
            border-radius: 10px;
            padding: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .tab {
            flex: 1;
            padding: 15px 20px;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            color: #7f8c8d;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .tab.active {
            background: <?= $categoria['cor'] ?>;
            color: white;
        }
        
        .tab:hover:not(.active) {
            background: #f8f9fa;
            color: #2c3e50;
        }
        
        @media (max-width: 768px) {
            .videoaula-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .videoaula-meta {
                flex-wrap: wrap;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="<?= $categoria['icone'] ?>"></i> <?= htmlspecialchars($categoria['nome']) ?></h1>
                <div class="user-info">
                    <a href="videoaulas.php" class="action-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>
        </header>

        <div class="categoria-container">
            <!-- Cabeçalho da Categoria -->
            <div class="categoria-header">
                <h1>
                    <i class="<?= $categoria['icone'] ?>"></i>
                    <?= htmlspecialchars($categoria['nome']) ?>
                </h1>
                <p><?= htmlspecialchars($categoria['descricao']) ?></p>
            </div>

            <!-- Progresso Geral -->
            <div class="progress-overview">
                <div class="progress-header">
                    <span class="progress-label">Progresso Geral</span>
                    <span class="progress-percentage"><?= $porcentagem_concluida ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $porcentagem_concluida ?>%"></div>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?= $total_videoaulas ?></h3>
                        <p>Total de Videoaulas</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= $videoaulas_iniciadas ?></h3>
                        <p>Iniciadas</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= $videoaulas_concluidas ?></h3>
                        <p>Concluídas</p>
                    </div>
                    <div class="stat-card">
                        <h3><?= $total_videoaulas - $videoaulas_iniciadas ?></h3>
                        <p>Não Iniciadas</p>
                    </div>
                </div>
            </div>

            <!-- Abas -->
            <div class="tabs">
                <a href="videoaulas_categoria.php?id=<?= $categoria_id ?>&view=videos" class="tab <?= $view === 'videos' ? 'active' : '' ?>">
                    <i class="fas fa-play"></i> Videoaulas
                </a>
                <a href="videoaulas_categoria.php?id=<?= $categoria_id ?>&view=stats" class="tab <?= $view === 'stats' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i> Estatísticas
                </a>
                <?php if ($porcentagem_concluida == 100): ?>
                    <a href="gerar_certificado.php?categoria_id=<?= $categoria_id ?>&acao=visualizar" class="tab" style="background: #27ae60; color: white;">
                        <i class="fas fa-certificate"></i> Ver Certificado
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($view === 'videos'): ?>
                <!-- Lista de Videoaulas -->
                <?php if (empty($videoaulas)): ?>
                    <div class="empty-state">
                        <i class="fas fa-video"></i>
                        <h3>Nenhuma videoaula encontrada</h3>
                        <p>As videoaulas desta categoria ainda não foram configuradas.</p>
                    </div>
                <?php else: ?>
                    <div class="videoaulas-list">
                        <?php foreach ($videoaulas as $videoaula): ?>
                            <div class="videoaula-card">
                                <div class="status-icon">
                                    <?php if ($videoaula['concluida']): ?>
                                        <i class="fas fa-check-circle status-completed"></i>
                                    <?php elseif ($videoaula['tempo_assistido'] > 0): ?>
                                        <i class="fas fa-play-circle status-in-progress"></i>
                                    <?php else: ?>
                                        <i class="fas fa-circle status-not-started"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="videoaula-header">
                                    <div class="videoaula-info">
                                        <h3><?= htmlspecialchars($videoaula['titulo']) ?></h3>
                                        <p><?= htmlspecialchars($videoaula['descricao']) ?></p>
                                    </div>
                                    <div class="videoaula-meta">
                                        <span class="duracao">
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
                                            <span><?= $videoaula['progresso_percentual'] ?>% assistido</span>
                                            <span>
                                                <?php if ($videoaula['concluida']): ?>
                                                    Concluída em <?= date('d/m/Y', strtotime($videoaula['data_conclusao'])) ?>
                                                <?php else: ?>
                                                    Iniciada em <?= date('d/m/Y', strtotime($videoaula['data_inicio'])) ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="videoaula-actions">
                                    <?php if ($videoaula['concluida']): ?>
                                        <button class="btn-videoaula btn-completed" disabled>
                                            <i class="fas fa-check"></i> Concluída
                                        </button>
                                        <a href="videoaula_individual.php?id=<?= $videoaula['id'] ?>" class="btn-videoaula btn-play">
                                            <i class="fas fa-redo"></i> Reassistir
                                        </a>
                                    <?php elseif ($videoaula['tempo_assistido'] > 0): ?>
                                        <a href="videoaula_individual.php?id=<?= $videoaula['id'] ?>" class="btn-videoaula btn-continue">
                                            <i class="fas fa-play"></i> Continuar
                                        </a>
                                    <?php else: ?>
                                        <a href="videoaula_individual.php?id=<?= $videoaula['id'] ?>" class="btn-videoaula btn-play">
                                            <i class="fas fa-play"></i> Assistir
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php elseif ($view === 'stats'): ?>
                <!-- Estatísticas Detalhadas -->
                <div class="stats-detailed">
                    <h2><i class="fas fa-chart-bar"></i> Estatísticas Detalhadas</h2>
                    <p>Análise completa do seu progresso nesta categoria.</p>
                    
                    <!-- Aqui você pode adicionar gráficos e estatísticas mais detalhadas -->
                    <div class="empty-state">
                        <i class="fas fa-chart-line"></i>
                        <h3>Estatísticas em Desenvolvimento</h3>
                        <p>Em breve você terá acesso a gráficos detalhados do seu progresso.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
