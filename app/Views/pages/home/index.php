<?php
/**
 * Página Inicial
 * 
 * View da homepage do sistema
 */

$titulo = 'RCP - Sistema de Concursos - Plataforma de Estudos';
?>

<div class="container">
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="nav-brand">
            <i class="fas fa-graduation-cap"></i>
            <span>RCP - Sistema de Concursos</span>
        </div>
        <div class="nav-actions">
            <a href="/login" class="nav-link">Entrar</a>
            <a href="/register" class="nav-btn">Cadastrar</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-star"></i>
                <span>Plataforma #1 em Gamificação de Estudos</span>
            </div>
            <h1>Transforme seus estudos em uma <span class="gradient-text">jornada épica</span></h1>
            <p class="hero-subtitle">
                A única plataforma que combina inteligência artificial, gamificação e
                análise de dados para maximizar seu desempenho em concursos públicos.
            </p>

            <div class="hero-actions">
                <a href="/register" class="btn-primary btn-large">
                    <i class="fas fa-rocket"></i> Começar Jornada
                </a>
                <a href="/login" class="btn-secondary btn-large">
                    <i class="fas fa-sign-in-alt"></i> Já tenho conta
                </a>
            </div>
        </div>
    </section>
</div>

