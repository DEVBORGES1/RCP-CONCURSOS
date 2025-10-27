<?php
/**
 * Dashboard Principal
 * 
 * View do dashboard com estatísticas e gamificação
 */

$usuario = $usuario ?? [];
$estatisticas = $estatisticas ?? [];
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>
            <i class="fas fa-home"></i> 
            Bem-vindo, <?= htmlspecialchars($usuario['nome'] ?? 'Usuário') ?>!
        </h1>
    </div>

    <!-- Cards de Gamificação -->
    <div class="gamification-cards">
        <div class="card">
            <div class="card-icon">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="card-content">
                <h3>Nível <?= htmlspecialchars($usuario['nivel'] ?? 1) ?></h3>
                <p>Continue estudando para subir de nível!</p>
            </div>
        </div>

        <div class="card">
            <div class="card-icon">
                <i class="fas fa-star"></i>
            </div>
            <div class="card-content">
                <h3><?= number_format($usuario['pontos'] ?? 0) ?> Pontos</h3>
                <p>Total de pontos acumulados</p>
            </div>
        </div>

        <div class="card">
            <div class="card-icon">
                <i class="fas fa-fire"></i>
            </div>
            <div class="card-content">
                <h3><?= $usuario['streak'] ?? 0 ?> Dias</h3>
                <p>Sequência de estudos</p>
            </div>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="stats-section">
        <h2><i class="fas fa-chart-bar"></i> Estatísticas</h2>
        
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value"><?= htmlspecialchars($estatisticas['questoes_respondidas'] ?? 0) ?></div>
                <div class="stat-label">Questões Respondidas</div>
            </div>

            <div class="stat-item">
                <div class="stat-value"><?= htmlspecialchars($estatisticas['percentual_acerto'] ?? 0) ?>%</div>
                <div class="stat-label">Taxa de Acerto</div>
            </div>

            <div class="stat-item">
                <div class="stat-value"><?= htmlspecialchars($estatisticas['total_simulados'] ?? 0) ?></div>
                <div class="stat-label">Simulados Completados</div>
            </div>

            <div class="stat-item">
                <div class="stat-value"><?= htmlspecialchars($estatisticas['total_editais'] ?? 0) ?></div>
                <div class="stat-label">Editais Cadastrados</div>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="quick-actions">
        <h2><i class="fas fa-bolt"></i> Ações Rápidas</h2>
        
        <div class="actions-grid">
            <a href="/questoes" class="action-card">
                <i class="fas fa-question-circle"></i>
                <span>Responder Questões</span>
            </a>

            <a href="/simulados" class="action-card">
                <i class="fas fa-clipboard-list"></i>
                <span>Fazer Simulado</span>
            </a>

            <a href="/editais" class="action-card">
                <i class="fas fa-file-alt"></i>
                <span>Meus Editais</span>
            </a>

            <a href="/perfil" class="action-card">
                <i class="fas fa-user"></i>
                <span>Meu Perfil</span>
            </a>
        </div>
    </div>
</div>

