<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';
require_once 'classes/SistemaProgressoAvancado.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$sistema_progresso = new SistemaProgressoAvancado($pdo);
$dashboard_completo = $sistema_progresso->obterDashboardCompleto($_SESSION["usuario_id"]);

// Obter dados básicos para compatibilidade
$gamificacao = new Gamificacao($pdo);
$gamificacao->atualizarStreak($_SESSION["usuario_id"]);
$dados_usuario = $gamificacao->obterDadosUsuario($_SESSION["usuario_id"]);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Avançado - Sistema de Concursos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-graduation-cap"></i> RCP Concursos</h2>
            <p>Sistema de Estudos</p>
        </div>
        <div class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Navegação</div>
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="perfil.php" class="nav-item">
                    <i class="fas fa-user"></i>
                    <span>Meu Perfil</span>
                </a>
                <a href="simulados.php" class="nav-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Simulados</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Estudos</div>
                <a href="questoes.php" class="nav-item">
                    <i class="fas fa-question-circle"></i>
                    <span>Banco de Questões</span>
                </a>
                <a href="videoaulas.php" class="nav-item">
                    <i class="fas fa-play-circle"></i>
                    <span>Videoaulas</span>
                </a>
                <a href="editais.php" class="nav-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Meus Editais</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Ferramentas</div>
                <a href="upload_edital.php" class="nav-item">
                    <i class="fas fa-upload"></i>
                    <span>Upload Edital</span>
                </a>
                <a href="gerar_cronograma.php" class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Gerar Cronograma</span>
                </a>
                <a href="dashboard_avancado.php" class="nav-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard Avançado</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Conta</div>
                <a href="logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="content-with-sidebar">
        <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard Avançado</h1>
                <div class="user-info">
                    <div class="user-level">
                        <span class="level-badge">Nível <?= $dashboard_completo['resumo_geral']['nivel'] ?? 1 ?></span>
                        <span class="points"><?= $dashboard_completo['resumo_geral']['pontos_total'] ?? 0 ?> pts</span>
                    </div>
                    <div class="header-actions">
                        <a href="dashboard.php" class="nav-btn">
                            <i class="fas fa-arrow-left"></i> Dashboard
                        </a>
                        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Welcome Section -->
        <section class="welcome-section">
            <div class="welcome-card">
                <h2>Olá, <?= htmlspecialchars($dados_usuario['nome']) ?>! 👋</h2>
                <p>Seu progresso está incrível! Continue assim!</p>
                <div class="streak-info">
                    <i class="fas fa-fire"></i>
                    <span><?= $dashboard_completo['resumo_geral']['streak_dias'] ?? 0 ?> dias seguidos</span>
                </div>
            </div>
        </section>

        <!-- Insights Inteligentes -->
        <?php if (!empty($dashboard_completo['insights_inteligentes'])): ?>
            <section class="insights-section">
                <div class="card">
                    <h3><i class="fas fa-lightbulb"></i> Insights Inteligentes</h3>
                    <div class="insights-grid">
                        <?php foreach ($dashboard_completo['insights_inteligentes'] as $insight): ?>
                            <div class="insight-card insight-<?= $insight['tipo'] ?? 'info' ?>">
                                <div class="insight-icon"><?= $insight['icone'] ?? '💡' ?></div>
                                <div class="insight-content">
                                    <h4><?= htmlspecialchars($insight['titulo'] ?? 'Insight') ?></h4>
                                    <p><?= htmlspecialchars($insight['mensagem'] ?? $insight['descricao'] ?? '') ?></p>
                                    <small><?= htmlspecialchars($insight['acao_sugerida'] ?? $insight['acao'] ?? '') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Resumo Geral -->
        <section class="stats-section">
            <div class="card">
                <h3><i class="fas fa-chart-line"></i> Resumo Geral</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $dashboard_completo['resumo_geral']['questoes_unicas_respondidas'] ?? 0 ?></h3>
                            <p>Questões Respondidas</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= round($dashboard_completo['resumo_geral']['taxa_acerto'] ?? 0, 1) ?>%</h3>
                            <p>Taxa de Acerto</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $dashboard_completo['resumo_geral']['simulados_completos'] ?? 0 ?></h3>
                            <p>Simulados Completos</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $dashboard_completo['resumo_geral']['disciplinas_estudadas'] ?? 0 ?></h3>
                            <p>Disciplinas Estudadas</p>
                        </div>
                    </div>
                </div>

                <!-- Barra de Progresso do Nível -->
                <div class="level-progress">
                    <h4>Progresso para o Próximo Nível</h4>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $dashboard_completo['resumo_geral']['progresso_nivel'] ?? 0 ?>%"></div>
                    </div>
                    <p><?= round($dashboard_completo['resumo_geral']['progresso_nivel'] ?? 0, 1) ?>% completo</p>
                </div>
            </div>
        </section>

        <!-- Progresso por Disciplina -->
        <section class="disciplines-section">
            <div class="card">
                <h3><i class="fas fa-graduation-cap"></i> Progresso por Disciplina</h3>
                <div class="disciplines-grid">
                    <?php foreach ($dashboard_completo['progresso_por_disciplina'] as $disciplina): ?>
                        <div class="discipline-card">
                            <div class="discipline-header">
                                <h4><?= htmlspecialchars($disciplina['nome_disciplina']) ?></h4>
                                <span class="dominance-level level-<?= $disciplina['nivel_dominio'] ?>">
                                    Nível <?= $disciplina['nivel_dominio'] ?>
                                </span>
                            </div>

                            <div class="discipline-stats">
                                <div class="stat">
                                    <i class="fas fa-question-circle"></i>
                                    <span><?= $disciplina['questoes_respondidas'] ?> questões</span>
                                </div>
                                <div class="stat">
                                    <i class="fas fa-percentage"></i>
                                    <span><?= round($disciplina['taxa_acerto'] ?? 0, 1) ?>% acerto</span>
                                </div>
                                <div class="stat">
                                    <i class="fas fa-star"></i>
                                    <span><?= $disciplina['pontos_disciplina'] ?> pts</span>
                                </div>
                            </div>

                            <div class="discipline-progress">
                                <div class="progress-bar-small">
                                    <div class="progress-fill" style="width: <?= $disciplina['taxa_acerto'] ?>%"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Metas e Objetivos -->
        <section class="goals-section">
            <div class="card">
                <h3><i class="fas fa-target"></i> Metas e Objetivos</h3>

                <!-- Metas Automáticas -->
                <?php if (!empty($dashboard_completo['metas_e_objetivos']['automaticas'])): ?>
                    <div class="goals-category">
                        <h4>Metas Automáticas</h4>
                        <div class="goals-grid">
                            <?php foreach ($dashboard_completo['metas_e_objetivos']['automaticas'] as $meta): ?>
                                <div class="goal-card">
                                    <div class="goal-header">
                                        <h5><?= $meta['titulo'] ?></h5>
                                        <span class="goal-reward"><?= $meta['pontos_recompensa'] ?> pts</span>
                                    </div>
                                    <p><?= $meta['descricao'] ?></p>
                                    <div class="goal-progress">
                                        <div class="progress-bar-small">
                                            <div class="progress-fill" style="width: <?= ($meta['progresso_atual'] / $meta['meta_final']) * 100 ?>%"></div>
                                        </div>
                                        <span><?= $meta['progresso_atual'] ?> / <?= $meta['meta_final'] ?></span>
                                    </div>
                                    <small>Prazo: <?= $meta['prazo_sugerido'] ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Conquistas Recentes -->
        <section class="achievements-section">
            <div class="card">
                <h3><i class="fas fa-medal"></i> Conquistas</h3>

                <!-- Conquistas Recentes -->
                <?php if (!empty($dashboard_completo['conquistas_recentes']['recentes'] ?? [])): ?>
                    <div class="achievements-category">
                        <h4>Conquistas Recentes</h4>
                        <div class="achievements-grid">
                            <?php foreach ($dashboard_completo['conquistas_recentes']['recentes'] as $conquista): ?>
                                <div class="achievement-item unlocked">
                                    <div class="achievement-icon"><?= htmlspecialchars($conquista['icone'] ?? '🏆') ?></div>
                                    <div class="achievement-info">
                                        <h4><?= htmlspecialchars($conquista['nome'] ?? '') ?></h4>
                                        <p><?= htmlspecialchars($conquista['descricao'] ?? '') ?></p>
                                        <small>Conquistada em <?= isset($conquista['data_conquista']) ? date('d/m/Y', strtotime($conquista['data_conquista'])) : date('d/m/Y') ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Próximas Conquistas -->
                <?php if (!empty($dashboard_completo['conquistas_recentes']['proximas'] ?? [])): ?>
                    <div class="achievements-category">
                        <h4>Próximas Conquistas</h4>
                        <div class="achievements-grid">
                            <?php foreach ($dashboard_completo['conquistas_recentes']['proximas'] as $conquista): ?>
                                <div class="achievement-item locked">
                                    <div class="achievement-icon"><?= htmlspecialchars($conquista['icone'] ?? '🔒') ?></div>
                                    <div class="achievement-info">
                                        <h4><?= htmlspecialchars($conquista['nome'] ?? '') ?></h4>
                                        <p><?= htmlspecialchars($conquista['descricao'] ?? '') ?></p>
                                        <small><?= $conquista['pontos_necessarios'] ?> pontos necessários</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Próximos Desafios -->
        <?php if (!empty($dashboard_completo['proximos_desafios'])): ?>
            <section class="challenges-section">
                <div class="card">
                    <h3><i class="fas fa-rocket"></i> Próximos Desafios</h3>
                    <div class="challenges-grid">
                        <?php foreach ($dashboard_completo['proximos_desafios'] as $desafio): ?>
                            <div class="challenge-card">
                                <div class="challenge-header">
                                    <h4><?= $desafio['titulo'] ?></h4>
                                    <span class="difficulty difficulty-<?= $desafio['dificuldade'] ?>">
                                        <?= ucfirst($desafio['dificuldade']) ?>
                                    </span>
                                </div>
                                <p><?= $desafio['descricao'] ?></p>
                                <div class="challenge-rewards">
                                    <span class="reward-points"><?= $desafio['pontos_recompensa'] ?> pts</span>
                                    <span class="reward-time"><?= $desafio['tempo_estimado'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Gráfico de Progresso Temporal -->
        <section class="chart-section">
            <div class="card">
                <h3><i class="fas fa-chart-area"></i> Progresso dos Últimos 30 Dias</h3>
                <canvas id="progressChart" width="400" height="200"></canvas>
            </div>
        </section>
        </div>
    </div>

    <script>
        // Sidebar mobile toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                });
                
                // Fechar sidebar ao clicar fora dela em mobile
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                            sidebar.classList.remove('open');
                        }
                    }
                });
            }
        });
    </script>

    <style>
        .insights-section {
            margin-bottom: 30px;
        }

        .insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .insight-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid;
        }

        .insight-sucesso {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            border-left-color: #ff4444;
            color: white;
        }

        .insight-atencao {
            background: linear-gradient(45deg, #ff6666, #ff3333);
            border-left-color: #ff4444;
            color: white;
        }

        .insight-info {
            background: linear-gradient(45deg, #ff8888, #ff5555);
            border-left-color: #ff4444;
            color: white;
        }

        .insight-icon {
            font-size: 2rem;
        }

        .insight-content h4 {
            margin: 0 0 5px 0;
            font-size: 1.1rem;
        }

        .insight-content p {
            margin: 0 0 5px 0;
            font-size: 0.95rem;
        }

        .insight-content small {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .level-progress {
            margin-top: 20px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .level-progress h4 {
            margin: 0 0 10px 0;
            color: white;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(45deg, #ff4444, #cc0000);
            transition: width 0.3s ease;
        }

        .disciplines-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .discipline-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            color: white;
        }

        .discipline-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            background: rgba(255, 255, 255, 0.15);
        }

        .discipline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .discipline-header h4 {
            margin: 0;
            color: white;
            font-weight: 600;
        }

        .dominance-level {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .level-1 {
            background: #ff6b6b;
            color: white;
        }

        .level-2 {
            background: #ffa726;
            color: white;
        }

        .level-3 {
            background: #ffeb3b;
            color: #333;
        }

        .level-4 {
            background: #66bb6a;
            color: white;
        }

        .level-5 {
            background: #42a5f5;
            color: white;
        }

        .discipline-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }

        .stat {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .stat i {
            color: #ff4444;
        }

        .progress-bar-small {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar-small .progress-fill {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            height: 100%;
            transition: width 0.3s ease;
        }

        .goals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .goal-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 15px;
            border-left: 4px solid #ff4444;
            color: white;
            transition: all 0.3s ease;
        }

        .goal-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .goal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .goal-header h5 {
            margin: 0;
            color: white;
            font-weight: 600;
        }

        .goal-reward {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .goal-progress {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }

        .goal-progress span {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .challenges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .challenge-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s ease;
            color: white;
        }

        .challenge-card:hover {
            border-color: #ff4444;
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .challenge-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .challenge-header h4 {
            margin: 0;
            color: white;
            font-weight: 600;
        }

        .difficulty {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .difficulty-baixa {
            background: #4caf50;
            color: white;
        }

        .difficulty-media {
            background: #ff9800;
            color: white;
        }

        .difficulty-alta {
            background: #f44336;
            color: white;
        }

        .challenge-rewards {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .reward-points,
        .reward-time {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .chart-section {
            margin-bottom: 30px;
        }

        #progressChart {
            max-height: 300px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            padding: 20px;
        }

        /* Cards principais */
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .card h3 {
            color: white;
            margin-bottom: 20px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card h3 i {
            color: #ff4444;
        }

        .card h4 {
            color: white;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        /* Animações */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeInUp 0.6s ease-out;
        }

        .card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .card:nth-child(4) {
            animation-delay: 0.4s;
        }

        .card:nth-child(5) {
            animation-delay: 0.5s;
        }

        /* Melhorias de responsividade */
        @media (max-width: 768px) {

            .insights-grid,
            .disciplines-grid,
            .goals-grid,
            .challenges-grid {
                grid-template-columns: 1fr;
            }

            .discipline-stats {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .card {
                padding: 20px;
                margin-bottom: 20px;
            }

            .challenge-rewards {
                flex-direction: column;
                gap: 5px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }

            .card {
                padding: 15px;
            }

            .discipline-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        /* Estilos para navegação */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-btn {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
    </style>

    <script>
        // Gráfico de progresso temporal
        const ctx = document.getElementById('progressChart').getContext('2d');

        // Dados do PHP (simulados para demonstração)
        const dadosTemporais = <?= json_encode($dashboard_completo['estatisticas_temporais']['dados_diarios'] ?? []) ?>;

        const labels = dadosTemporais.map(d => d.data_estudo || '').reverse();
        const questoesData = dadosTemporais.map(d => d.questoes_respondidas || 0).reverse();
        const pontosData = dadosTemporais.map(d => d.pontos_dia || 0).reverse();

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Questões Respondidas',
                    data: questoesData,
                    borderColor: '#ff4444',
                    backgroundColor: 'rgba(255, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Pontos Ganhos',
                    data: pontosData,
                    borderColor: '#cc0000',
                    backgroundColor: 'rgba(204, 0, 0, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: 'white',
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: 'white'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    },
                    y: {
                        ticks: {
                            color: 'white'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    },
                    y1: {
                        ticks: {
                            color: 'white'
                        },
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>