<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/GamificacaoRefatorada.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$gamificacao = new GamificacaoRefatorada();
$gamificacao->atualizarStreak($_SESSION["usuario_id"]);
$gamificacao->verificarTodasConquistas($_SESSION["usuario_id"]);

// Obter dados do usuário
$dados_usuario = $gamificacao->obterDadosUsuario($_SESSION["usuario_id"]);
$conquistas = $gamificacao->obterConquistasUsuario($_SESSION["usuario_id"]);
$ranking = $gamificacao->obterRankingMensal(5);
$posicao_usuario = $gamificacao->obterPosicaoUsuario($_SESSION["usuario_id"]);

// Calcular estatísticas com valores padrão seguros
$total_questoes = isset($dados_usuario['questoes_respondidas']) ? (int)$dados_usuario['questoes_respondidas'] : 0;
$questoes_corretas = isset($dados_usuario['questoes_corretas']) ? (int)$dados_usuario['questoes_corretas'] : 0;
$percentual_acerto = 0;
if ($total_questoes > 0) {
    $percentual_acerto = round(($questoes_corretas / $total_questoes) * 100, 1);
    // Garantir que não passe de 100%
    if ($percentual_acerto > 100) {
        $percentual_acerto = 100;
    }
}

// Garantir que os dados do usuário tenham valores padrão
$nome_usuario = isset($dados_usuario['nome']) ? $dados_usuario['nome'] : 'Usuário';
$nivel_usuario = isset($dados_usuario['nivel']) ? (int)$dados_usuario['nivel'] : 1;
$pontos_usuario = isset($dados_usuario['pontos_total']) ? (int)$dados_usuario['pontos_total'] : 0;
$streak_usuario = isset($dados_usuario['streak_dias']) ? (int)$dados_usuario['streak_dias'] : 0;

// Obter editais e simulados usando Database
$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM editais WHERE usuario_id = ?");
$stmt->execute([$_SESSION["usuario_id"]]);
$total_editais = $stmt->fetchColumn();

// Obter simulados concluídos do usuário
$stmt = $pdo->prepare("SELECT COUNT(*) FROM simulados WHERE usuario_id = ? AND questoes_corretas IS NOT NULL");
$stmt->execute([$_SESSION["usuario_id"]]);
$total_simulados = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Concursos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="/css/concurso.ico" type="image/png">
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
                <a href="dashboard.php" class="nav-item active">
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
                <a href="dashboard_avancado.php" class="nav-item">
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
                <h1><i class="fas fa-graduation-cap"></i> RCP - Sistema de Concursos</h1>
                <div class="user-info">
                    <div class="user-level">
                        <span class="level-badge">Nível <?= $nivel_usuario ?></span>
                        <span class="points"><?= $pontos_usuario ?> pts</span>
                    </div>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>
        </header>

        <!-- Welcome Section -->
        <section class="welcome-section">
            <div class="welcome-card">
                <h2>Olá, <?= htmlspecialchars($nome_usuario) ?>! 👋</h2>
                <p>Continue estudando para alcançar seus objetivos!</p>
                <div class="streak-info">
                    <i class="fas fa-fire"></i>
                    <span><?= $streak_usuario ?> dias seguidos</span>
                </div>
            </div>
        </section>

        <!-- Stats Grid -->
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $total_questoes ?></h3>
                    <p>Questões Respondidas</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $percentual_acerto ?>%</h3>
                    <p>Taxa de Acerto</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $total_editais ?></h3>
                    <p>Editais Enviados</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $total_simulados ?></h3>
                    <p>Simulados Realizados</p>
                </div>
            </div>
        </section>

        <!-- Progress Section -->
        <section class="progress-section">
            <div class="progress-card">
                <h3><i class="fas fa-chart-line"></i> Seu Progresso</h3>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= min(100, $pontos_usuario / 10) ?>%"></div>
                </div>
                <p><?= $pontos_usuario ?> pontos para o próximo nível</p>
            </div>
        </section>

        <!-- Ranking Section -->
        <section class="ranking-section">
            <div class="ranking-card">
                <h3><i class="fas fa-trophy"></i> Ranking Mensal</h3>
                <div class="ranking-list">
                    <?php if (!empty($ranking)): ?>
                        <?php foreach ($ranking as $index => $user): ?>
                            <div class="ranking-item <?= $user['posicao'] == $posicao_usuario ? 'current-user' : '' ?>">
                                <span class="position"><?= $user['posicao'] ?>º</span>
                                <span class="name"><?= htmlspecialchars($user['nome']) ?></span>
                                <span class="points"><?= $user['pontos_mes'] ?> pts</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Nenhum ranking disponível ainda.</p>
                    <?php endif; ?>
                </div>
                <?php if ($posicao_usuario): ?>
                    <div class="user-position">
                        <strong>Sua posição: <?= $posicao_usuario ?>º</strong>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Achievements Section -->
        <section class="achievements-section">
            <div class="achievements-card">
                <h3><i class="fas fa-medal"></i> Conquistas</h3>
                <?php if (empty($conquistas)): ?>
                    <div class="empty-state">
                        <i class="fas fa-medal"></i>
                        <h3>Nenhuma conquista disponível</h3>
                        <p>As conquistas serão exibidas aqui conforme você progride no sistema.</p>
                    </div>
                <?php else: ?>
                    <div class="achievements-grid">
                        <?php foreach ($conquistas as $conquista): ?>
                            <div class="achievement-item <?= $conquista['data_conquista'] ? 'unlocked' : 'locked' ?>">
                                <div class="achievement-icon"><?= htmlspecialchars($conquista['icone']) ?></div>
                                <div class="achievement-info">
                                    <h4><?= htmlspecialchars($conquista['nome']) ?></h4>
                                    <p><?= htmlspecialchars($conquista['descricao']) ?></p>
                                    <?php if ($conquista['data_conquista']): ?>
                                        <small>Conquistada em <?= date('d/m/Y', strtotime($conquista['data_conquista'])) ?></small>
                                    <?php else: ?>
                                        <small><?= $conquista['pontos_necessarios'] ?> pontos necessários</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="quick-actions">
            <h3><i class="fas fa-bolt"></i> Ações Rápidas</h3>
            <div class="actions-grid">
                <a href="upload_edital.php" class="action-btn">
                    <i class="fas fa-upload"></i>
                    <span>Upload Edital</span>
                </a>
                <a href="editais.php" class="action-btn">
                    <i class="fas fa-file-alt"></i>
                    <span>Meus Editais</span>
                </a>
                <a href="gerar_cronograma.php" class="action-btn">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Gerar Cronograma</span>
                </a>
                <a href="questoes.php" class="action-btn">
                    <i class="fas fa-question-circle"></i>
                    <span>Banco de Questões</span>
                </a>
                <a href="simulados.php" class="action-btn">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Simulados</span>
                </a>
                <a href="videoaulas.php" class="action-btn">
                    <i class="fas fa-play-circle"></i>
                    <span>Videoaulas</span>
                </a>
                <a href="dashboard_avancado.php" class="action-btn">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard Avançado</span>
                </a>
            </div>
        </section>
        </div>
    </div>

    <script>
        // Animar progresso
        document.addEventListener('DOMContentLoaded', function() {
            const progressFill = document.querySelector('.progress-fill');
            if (progressFill) {
                setTimeout(() => {
                    progressFill.style.transition = 'width 1s ease-in-out';
                }, 500);
            }
        });
        
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
</body>

</html>