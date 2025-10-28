<?php
session_start();
if (isset($_SESSION["usuario_id"])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCP - Sistema de Concursos - Plataforma de Estudos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="/css/concurso.png" type="image/png">
</head>

<body class="homepage">
    <div class="container">
        <!-- Navigation -->
        <nav class="main-nav">
            <div class="nav-brand">
                <i class="fas fa-graduation-cap"></i>
                <span>RCP - Sistema de Concursos</span>
            </div>
            <div class="nav-actions">
                <a href="login.php" class="nav-link">Entrar</a>
                <a href="register.php" class="nav-btn">Cadastrar</a>
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
                <div class="hero-stats">
                    <div class="stat">
                        <span class="number">95%</span>
                        <span class="label">Taxa de Aprovação</span>
                    </div>
                    <div class="stat">
                        <span class="number">50k+</span>
                        <span class="label">Questões</span>
                    </div>
                    <div class="stat">
                        <span class="number">10k+</span>
                        <span class="label">Usuários</span>
                    </div>
                </div>

                <div class="hero-actions">
                    <a href="register.php" class="btn-primary btn-large">
                        <i class="fas fa-rocket"></i> Começar Jornada
                        <span class="btn-glow"></span>
                    </a>
                    <a href="login.php" class="btn-secondary btn-large">
                        <i class="fas fa-sign-in-alt"></i> Já tenho conta
                    </a>
                </div>

                <div class="hero-trust">
                    <p>Confiança de milhares de candidatos</p>
                    <div class="trust-badges">
                        <div class="badge">
                            <i class="fas fa-shield-alt"></i>
                            <span>100% Seguro</span>
                        </div>
                        <div class="badge">
                            <i class="fas fa-clock"></i>
                            <span>24/7 Disponível</span>
                        </div>
                        <div class="badge">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Mobile First</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="floating-cards">
                    <div class="card card-1">
                        <i class="fas fa-trophy"></i>
                        <h4>Gamificação</h4>
                        <p>Pontos, níveis e conquistas</p>
                    </div>
                    <div class="card card-2">
                        <i class="fas fa-brain"></i>
                        <h4>IA Inteligente</h4>
                        <p>Cronogramas personalizados</p>
                    </div>
                    <div class="card card-3">
                        <i class="fas fa-chart-line"></i>
                        <h4>Progresso</h4>
                        <p>Acompanhe sua evolução</p>
                    </div>
                    <div class="card card-4">
                        <i class="fas fa-users"></i>
                        <h4>Ranking</h4>
                        <p>Compita com outros</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it Works Section -->
        <section class="how-it-works">
            <div class="section-header">
                <h2>Como funciona nossa plataforma?</h2>
                <p>Três passos simples para transformar seus estudos</p>
            </div>
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <i class="fas fa-upload"></i>
                        <h3>Upload do Edital</h3>
                        <p>Envie o PDF do edital e nossa IA extrai automaticamente todo o conteúdo programático</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <i class="fas fa-calendar-check"></i>
                        <h3>Cronograma Inteligente</h3>
                        <p>Receba um plano de estudo personalizado baseado no seu tempo e dificuldade</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <i class="fas fa-trophy"></i>
                        <h3>Estude e Ganhe</h3>
                        <p>Pratique questões, ganhe pontos, suba de nível e conquiste sua vaga!</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <div class="section-header">
                <h2>Por que escolher nossa plataforma?</h2>
                <p>Recursos que fazem a diferença na sua preparação</p>
            </div>

            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-upload"></i>
                    </div>
                    <h3>Upload de Editais</h3>
                    <p>Envie PDFs de editais e provas anteriores. Nossa IA extrai automaticamente o conteúdo programático e identifica as disciplinas.</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Cronograma Inteligente</h3>
                    <p>Gere planos de estudo personalizados baseados no tempo disponível, peso das disciplinas e dificuldade dos tópicos.</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h3>Banco de Questões</h3>
                    <p>Cadastre questões das provas anteriores e pratique com nosso sistema inteligente de questões personalizadas.</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3>Simulados Personalizados</h3>
                    <p>Crie simulados adaptados ao seu nível e disciplinas de interesse, com correção automática e feedback detalhado.</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <h3>Sistema Gamificado</h3>
                    <p>Ganhe pontos, suba de nível, desbloqueie conquistas e compete com outros estudantes em rankings mensais.</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Dashboard Completo</h3>
                    <p>Acompanhe seu progresso com estatísticas detalhadas, gráficos de evolução e métricas de performance.</p>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials-section">
            <div class="section-header">
                <h2>O que nossos usuários dizem</h2>
                <p>Histórias reais de quem já conquistou a vaga</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"A gamificação me manteve motivado durante toda a preparação. Consegui minha vaga no TRT em 6 meses!"</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h4>Maria Silva</h4>
                            <span>Analista Judiciário - TRT</span>
                        </div>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="testimonial-content">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"O cronograma inteligente foi fundamental. Estudei de forma organizada e eficiente."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h4>João Santos</h4>
                            <span>Auditor Fiscal - SEFAZ</span>
                        </div>
                    </div>
                </div>

                <div class="testimonial">
                    <div class="testimonial-content">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p>"A plataforma me ajudou a identificar meus pontos fracos e melhorar rapidamente."</p>
                    </div>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <h4>Ana Costa</h4>
                            <span>Procuradora - MP</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="stats-container">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="stat-number">50k+</div>
                    <div class="stat-label">Questões Cadastradas</div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number">10k+</div>
                    <div class="stat-label">Usuários Ativos</div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-number">95%</div>
                    <div class="stat-label">Taxa de Aprovação</div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Disponibilidade</div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="cta-content">
                <h2>Pronto para transformar seus estudos?</h2>
                <p>Junte-se a milhares de candidatos que já descobriram uma forma mais eficiente e divertida de estudar para concursos.</p>
                <a href="register.php" class="btn-primary btn-large">
                    <i class="fas fa-user-plus"></i> Criar Conta Gratuita
                </a>
            </div>
        </section>
    </div>

    <style>
        /* Homepage specific styles */
        body.homepage {
            background: linear-gradient(135deg, #000000 0%, #1a0000 25%, #330000 50%, #660000 75%, #cc0000 100%);
            background-attachment: fixed;
            font-family: 'Inter', sans-serif;
        }

        /* Navigation */
        .main-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 60px;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .nav-brand i {
            color: #ff4444;
            font-size: 2rem;
        }

        .nav-actions {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: white;
        }

        .nav-btn {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 68, 68, 0.3);
        }

        /* Hero Section */
        .hero-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
            margin-bottom: 120px;
            padding: 80px 0;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid rgba(255, 68, 68, 0.3);
            color: #ff4444;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            color: white;
            margin-bottom: 30px;
            font-weight: 800;
            line-height: 1.1;
        }

        .gradient-text {
            background: linear-gradient(45deg, #ff4444, #ff6666, #ff8888);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
            font-weight: 400;
            line-height: 1.6;
        }

        .hero-stats {
            display: flex;
            gap: 40px;
            margin-bottom: 50px;
        }

        .stat {
            text-align: center;
        }

        .stat .number {
            display: block;
            font-size: 2rem;
            font-weight: 800;
            color: #ff4444;
            margin-bottom: 5px;
        }

        .stat .label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
        }

        .hero-actions {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn-large {
            padding: 18px 35px;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 68, 68, 0.4);
        }

        .btn-glow {
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover .btn-glow {
            left: 100%;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-3px);
        }

        .hero-trust {
            margin-top: 60px;
        }

        .hero-trust p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .trust-badges {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 20px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge i {
            color: #ff4444;
        }

        .hero-visual {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 500px;
        }

        .floating-cards {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .card {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            color: white;
            transition: all 0.3s ease;
            animation: float 6s ease-in-out infinite;
        }

        .card:hover {
            transform: translateY(-10px) scale(1.05);
            background: rgba(255, 255, 255, 0.15);
        }

        .card-1 {
            top: 0;
            left: 0;
            animation-delay: 0s;
        }

        .card-2 {
            top: 0;
            right: 0;
            animation-delay: 1.5s;
        }

        .card-3 {
            bottom: 0;
            left: 0;
            animation-delay: 3s;
        }

        .card-4 {
            bottom: 0;
            right: 0;
            animation-delay: 4.5s;
        }

        .card i {
            font-size: 2.5rem;
            color: #ff4444;
            margin-bottom: 15px;
        }

        .card h4 {
            font-size: 1.1rem;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .card p {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        /* Section Headers */
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: white;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .section-header p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 400;
        }

        /* How it Works Section */
        .how-it-works {
            margin-bottom: 120px;
        }

        .steps-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .step {
            text-align: center;
            position: relative;
        }

        .step-number {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0 auto 30px;
            position: relative;
            z-index: 2;
        }

        .step-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px 30px;
            border-radius: 20px;
            color: white;
            transition: all 0.3s ease;
        }

        .step-content:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
        }

        .step-content i {
            font-size: 3rem;
            color: #ff4444;
            margin-bottom: 20px;
        }

        .step-content h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .step-content p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
        }

        .features-section {
            margin-bottom: 120px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .feature-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            color: white;
            transition: all 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 68, 68, 0.3);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #ff4444, #cc0000);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
        }

        .feature-icon i {
            font-size: 2rem;
            color: white;
        }

        .feature-item h3 {
            color: white;
            font-size: 1.3rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .feature-item p {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
        }

        /* Testimonials Section */
        .testimonials-section {
            margin-bottom: 120px;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .testimonial {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 30px;
            border-radius: 20px;
            color: white;
            transition: all 0.3s ease;
        }

        .testimonial:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .stars {
            color: #ff4444;
            margin-bottom: 20px;
        }

        .stars i {
            margin-right: 2px;
        }

        .testimonial-content p {
            font-style: italic;
            margin-bottom: 25px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #ff4444, #cc0000);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .author-info h4 {
            color: white;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .author-info span {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .stats-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 60px;
            margin-bottom: 120px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
        }

        .stat-item {
            text-align: center;
            color: white;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #ff4444, #cc0000);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .stat-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: #ff4444;
            margin-bottom: 10px;
            display: block;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            font-weight: 500;
        }

        .cta-section {
            text-align: center;
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
            padding: 80px;
            border-radius: 20px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="10" cy="60" r="0.5" fill="rgba(255,255,255,0.1)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }

        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .cta-section .btn-primary {
            background: white;
            color: #ff4444;
            position: relative;
            z-index: 2;
        }

        .cta-section .btn-primary:hover {
            background: #f8f9fa;
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .hero-section {
                grid-template-columns: 1fr;
                gap: 50px;
                text-align: center;
                padding: 40px 0;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .hero-stats {
                justify-content: center;
                gap: 30px;
            }

            .steps-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .floating-cards {
                height: 300px;
            }

            .card {
                position: relative;
                margin-bottom: 20px;
                animation: none;
            }

            .card-1,
            .card-2,
            .card-3,
            .card-4 {
                position: relative;
                top: auto;
                left: auto;
                right: auto;
                bottom: auto;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .testimonials-grid {
                grid-template-columns: 1fr;
            }

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .cta-section {
                padding: 40px 20px;
            }

            .cta-section h2 {
                font-size: 2rem;
            }

            .section-header h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .hero-content h1 {
                font-size: 2rem;
            }

            .hero-stats {
                flex-direction: column;
                gap: 20px;
            }

            .trust-badges {
                flex-direction: column;
                align-items: center;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .hero-actions {
                flex-direction: column;
                align-items: center;
            }

            .nav-actions {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</body>

</html>