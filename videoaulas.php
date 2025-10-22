<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

// Obter categorias com progresso
$sql = "SELECT 
            vc.*,
            COUNT(v.id) as total_videoaulas,
            COUNT(vp.videoaula_id) as videoaulas_iniciadas,
            COUNT(CASE WHEN vp.concluida = 1 THEN 1 END) as videoaulas_concluidas,
            ROUND(
                CASE 
                    WHEN COUNT(v.id) > 0 THEN 
                        (COUNT(CASE WHEN vp.concluida = 1 THEN 1 END) / COUNT(v.id)) * 100 
                    ELSE 0 
                END, 1
            ) as porcentagem_concluida
        FROM videoaulas_categorias vc
        LEFT JOIN videoaulas v ON vc.id = v.categoria_id AND v.ativo = 1
        LEFT JOIN videoaulas_progresso vp ON v.id = vp.videoaula_id AND vp.usuario_id = ?
        WHERE vc.ativo = 1
        GROUP BY vc.id
        ORDER BY vc.ordem, vc.nome";

$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id]);
$categorias = $stmt->fetchAll();

// Calcular estatísticas gerais
$sql_stats = "SELECT 
                COUNT(DISTINCT v.id) as total_videoaulas,
                COUNT(DISTINCT vp.videoaula_id) as videoaulas_iniciadas,
                COUNT(CASE WHEN vp.concluida = 1 THEN 1 END) as videoaulas_concluidas,
                SUM(v.duracao) as duracao_total,
                SUM(CASE WHEN vp.concluida = 1 THEN v.duracao ELSE 0 END) as duracao_assistida
              FROM videoaulas v
              LEFT JOIN videoaulas_progresso vp ON v.id = vp.videoaula_id AND vp.usuario_id = ?
              WHERE v.ativo = 1";

$stmt = $pdo->prepare($sql_stats);
$stmt->execute([$usuario_id]);
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videoaulas - Sistema de Concursos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .videoaulas-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            font-size: 2.5em;
            margin: 0;
            font-weight: bold;
        }
        
        .stat-card p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .categorias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .categoria-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }
        
        .categoria-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .categoria-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--categoria-cor);
        }
        
        .categoria-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .categoria-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: var(--categoria-cor);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 1.5em;
        }
        
        .categoria-info h3 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.4em;
            font-weight: bold;
        }
        
        .categoria-info p {
            margin: 5px 0 0 0;
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .progress-section {
            margin: 20px 0;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .progress-label {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .progress-percentage {
            font-weight: bold;
            color: var(--categoria-cor);
            font-size: 1.1em;
        }
        
        .progress-bar {
            width: 100%;
            height: 12px;
            background: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--categoria-cor), var(--categoria-cor-light));
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
        
        .categoria-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid var(--categoria-cor);
        }
        
        .stat-item h4 {
            margin: 0;
            color: var(--categoria-cor);
            font-size: 1.5em;
            font-weight: bold;
        }
        
        .stat-item p {
            margin: 5px 0 0 0;
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .categoria-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-categoria {
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
        
        .btn-primary {
            background: var(--categoria-cor);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--categoria-cor-dark);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #ecf0f1;
            color: #2c3e50;
            border: 2px solid #bdc3c7;
        }
        
        .btn-secondary:hover {
            background: #d5dbdb;
            border-color: #95a5a6;
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
        
        .empty-state h3 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        
        @media (max-width: 768px) {
            .categorias-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .categoria-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-play-circle"></i> Videoaulas</h1>
                <div class="user-info">
                    <a href="dashboard.php" class="action-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Estatísticas Gerais -->
        <div class="stats-overview">
            <div class="stat-card">
                <h3><?= $stats['total_videoaulas'] ?></h3>
                <p><i class="fas fa-video"></i> Total de Videoaulas</p>
            </div>
            <div class="stat-card">
                <h3><?= $stats['videoaulas_concluidas'] ?></h3>
                <p><i class="fas fa-check-circle"></i> Concluídas</p>
            </div>
            <div class="stat-card">
                <h3><?= round(($stats['videoaulas_concluidas'] / max($stats['total_videoaulas'], 1)) * 100, 1) ?>%</h3>
                <p><i class="fas fa-chart-line"></i> Progresso Geral</p>
            </div>
            <div class="stat-card">
                <h3><?= round($stats['duracao_assistida'] / 60, 1) ?>h</h3>
                <p><i class="fas fa-clock"></i> Tempo Assistido</p>
            </div>
        </div>

        <!-- Categorias -->
        <?php if (empty($categorias)): ?>
            <div class="empty-state">
                <i class="fas fa-video"></i>
                <h3>Nenhuma categoria encontrada</h3>
                <p>As videoaulas ainda não foram configuradas.</p>
            </div>
        <?php else: ?>
            <div class="categorias-grid">
                <?php foreach ($categorias as $categoria): ?>
                    <div class="categoria-card" style="--categoria-cor: <?= $categoria['cor'] ?>; --categoria-cor-light: <?= $categoria['cor'] ?>88; --categoria-cor-dark: <?= $categoria['cor'] ?>dd;">
                        <div class="categoria-header">
                            <div class="categoria-icon">
                                <i class="<?= $categoria['icone'] ?>"></i>
                            </div>
                            <div class="categoria-info">
                                <h3><?= htmlspecialchars($categoria['nome']) ?></h3>
                                <p><?= htmlspecialchars($categoria['descricao']) ?></p>
                            </div>
                        </div>

                        <div class="progress-section">
                            <div class="progress-header">
                                <span class="progress-label">Progresso</span>
                                <span class="progress-percentage"><?= $categoria['porcentagem_concluida'] ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $categoria['porcentagem_concluida'] ?>%"></div>
                            </div>
                        </div>

                        <div class="categoria-stats">
                            <div class="stat-item">
                                <h4><?= $categoria['total_videoaulas'] ?></h4>
                                <p>Total</p>
                            </div>
                            <div class="stat-item">
                                <h4><?= $categoria['videoaulas_iniciadas'] ?></h4>
                                <p>Iniciadas</p>
                            </div>
                            <div class="stat-item">
                                <h4><?= $categoria['videoaulas_concluidas'] ?></h4>
                                <p>Concluídas</p>
                            </div>
                        </div>

                        <div class="categoria-actions">
                            <a href="videoaulas_categoria.php?id=<?= $categoria['id'] ?>" class="btn-categoria btn-primary">
                                <i class="fas fa-play"></i> Assistir
                            </a>
                            <a href="videoaulas_categoria.php?id=<?= $categoria['id'] ?>&view=stats" class="btn-categoria btn-secondary">
                                <i class="fas fa-chart-bar"></i> Estatísticas
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
