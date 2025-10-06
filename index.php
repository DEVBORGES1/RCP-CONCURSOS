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
    <title>Sistema de Concursos - Plataforma de Estudos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1><i class="fas fa-graduation-cap"></i> Sistema de Concursos</h1>
                <p class="hero-subtitle">Transforme seus estudos em uma jornada gamificada e eficiente</p>
                <p class="hero-description">
                    Nossa plataforma combina tecnologia avançada com gamificação para criar 
                    a experiência de estudo mais envolvente para candidatos a concursos públicos.
                </p>
                
                <div class="hero-actions">
                    <a href="register.php" class="btn-primary btn-large">
                        <i class="fas fa-rocket"></i> Começar Agora
                    </a>
                    <a href="login.php" class="btn-secondary btn-large">
                        <i class="fas fa-sign-in-alt"></i> Fazer Login
                    </a>
                </div>
            </div>
            
            <div class="hero-image">
                <div class="feature-cards">
                    <div class="feature-card">
                        <i class="fas fa-trophy"></i>
                        <h3>Gamificação</h3>
                        <p>Pontos, níveis e conquistas</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-brain"></i>
                        <h3>IA Inteligente</h3>
                        <p>Cronogramas personalizados</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-chart-line"></i>
                        <h3>Progresso</h3>
                        <p>Acompanhe sua evolução</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <h2>Por que escolher nossa plataforma?</h2>
            
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

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="stats-container">
                <div class="stat-item">
                    <div class="stat-number">1000+</div>
                    <div class="stat-label">Questões Cadastradas</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Usuários Ativos</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">95%</div>
                    <div class="stat-label">Taxa de Satisfação</div>
                </div>
                <div class="stat-item">
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
        .hero-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
            margin-bottom: 80px;
            padding: 60px 0;
        }
        
        .hero-content h1 {
            font-size: 3rem;
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .hero-content h1 i {
            color: #667eea;
            margin-right: 15px;
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            color: #667eea;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .hero-description {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.6;
            margin-bottom: 40px;
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
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.9);
            color: #2c3e50;
            border: 2px solid #667eea;
        }
        
        .btn-secondary:hover {
            background: #667eea;
            color: white;
            transform: translateY(-3px);
        }
        
        .hero-image {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .feature-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .feature-card i {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .feature-card h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        
        .feature-card p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .features-section {
            margin-bottom: 80px;
        }
        
        .features-section h2 {
            text-align: center;
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 50px;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .feature-item {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #667eea, #764ba2);
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
            color: #2c3e50;
            font-size: 1.3rem;
            margin-bottom: 15px;
        }
        
        .feature-item p {
            color: #666;
            line-height: 1.6;
        }
        
        .stats-section {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 60px;
            margin-bottom: 80px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .cta-section {
            text-align: center;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 80px;
            border-radius: 20px;
            margin-bottom: 40px;
        }
        
        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        
        .cta-section .btn-primary {
            background: white;
            color: #667eea;
        }
        
        .cta-section .btn-primary:hover {
            background: #f8f9fa;
            transform: translateY(-3px);
        }
        
        @media (max-width: 768px) {
            .hero-section {
                grid-template-columns: 1fr;
                gap: 30px;
                text-align: center;
            }
            
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
            
            .hero-actions {
                justify-content: center;
            }
            
            .feature-cards {
                grid-template-columns: 1fr;
            }
            
            .features-grid {
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
        }
        
        @media (max-width: 480px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .hero-actions {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</body>
</html>