
<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$gamificacao = new Gamificacao($pdo);
$gamificacao->atualizarStreak($_SESSION["usuario_id"]);

// Obter dados do usuário
$dados_usuario = $gamificacao->obterDadosUsuario($_SESSION["usuario_id"]);
$conquistas = $gamificacao->obterConquistasUsuario($_SESSION["usuario_id"]);
$ranking = $gamificacao->obterRankingMensal(5);
$posicao_usuario = $gamificacao->obterPosicaoUsuario($_SESSION["usuario_id"]);

// Calcular estatísticas
$total_questoes = $dados_usuario['questoes_respondidas'] ?? 0;
$questoes_corretas = $dados_usuario['questoes_corretas'] ?? 0;
$percentual_acerto = $total_questoes > 0 ? round(($questoes_corretas / $total_questoes) * 100, 1) : 0;

// Obter editais do usuário
$stmt = $pdo->prepare("SELECT COUNT(*) FROM editais WHERE usuario_id = ?");
$stmt->execute([$_SESSION["usuario_id"]]);
$total_editais = $stmt->fetchColumn();

// Obter simulados do usuário
$stmt = $pdo->prepare("SELECT COUNT(*) FROM simulados WHERE usuario_id = ?");
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
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-graduation-cap"></i> Sistema de Concursos</h1>
                <div class="user-info">
                    <div class="user-level">
                        <span class="level-badge">Nível <?= $dados_usuario['nivel'] ?? 1 ?></span>
                        <span class="points"><?= $dados_usuario['pontos_total'] ?? 0 ?> pts</span>
                    </div>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </div>
        </header>

        <!-- Welcome Section -->
        <section class="welcome-section">
            <div class="welcome-card">
                <h2>Olá, <?= htmlspecialchars($dados_usuario['nome']) ?>! 👋</h2>
                <p>Continue estudando para alcançar seus objetivos!</p>
                <div class="streak-info">
                    <i class="fas fa-fire"></i>
                    <span><?= $dados_usuario['streak_dias'] ?? 0 ?> dias seguidos</span>
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
                    <div class="progress-fill" style="width: <?= min(100, ($dados_usuario['pontos_total'] ?? 0) / 10) ?>%"></div>
                </div>
                <p><?= $dados_usuario['pontos_total'] ?? 0 ?> pontos para o próximo nível</p>
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
                <div class="achievements-grid">
                    <?php foreach ($conquistas as $conquista): ?>
                        <div class="achievement-item <?= $conquista['data_conquista'] ? 'unlocked' : 'locked' ?>">
                            <div class="achievement-icon"><?= $conquista['icone'] ?></div>
                            <div class="achievement-info">
                                <h4><?= $conquista['nome'] ?></h4>
                                <p><?= $conquista['descricao'] ?></p>
                                <?php if ($conquista['data_conquista']): ?>
                                    <small>Conquistada em <?= date('d/m/Y', strtotime($conquista['data_conquista'])) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
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
                <a href="dashboard_avancado.php" class="action-btn">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard Avançado</span>
                </a>
            </div>
        </section>
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
    </script>
</body>
</html>