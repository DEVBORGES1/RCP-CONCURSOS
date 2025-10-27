<?php
session_start();
require 'conexao.php';
require 'classes/AnalisadorEdital.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

// Obter editais do usuário
$sql = "SELECT e.*, 
               COUNT(DISTINCT d.id) as total_disciplinas,
               COUNT(q.id) as total_questoes
        FROM editais e 
        LEFT JOIN disciplinas d ON e.id = d.edital_id 
        LEFT JOIN questoes q ON d.id = q.disciplina_id 
        WHERE e.usuario_id = ? 
        GROUP BY e.id 
        ORDER BY e.data_upload DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$editais = $stmt->fetchAll();

// Obter estatísticas gerais
$sql = "SELECT 
            COUNT(DISTINCT e.id) as total_editais,
            COUNT(DISTINCT d.id) as total_disciplinas,
            COUNT(q.id) as total_questoes
        FROM editais e 
        LEFT JOIN disciplinas d ON e.id = d.edital_id 
        LEFT JOIN questoes q ON d.id = q.disciplina_id 
        WHERE e.usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$estatisticas = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Editais - Sistema de Concursos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-file-alt"></i> Meus Editais</h1>
                <div class="user-info">
                    <a href="dashboard.php" class="action-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                    <a href="upload_edital.php" class="action-btn">
                        <i class="fas fa-plus"></i>
                        <span>Novo Edital</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Estatísticas -->
        <section class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $estatisticas['total_editais'] ?></h3>
                        <p>Editais Enviados</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $estatisticas['total_disciplinas'] ?></h3>
                        <p>Disciplinas Detectadas</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $estatisticas['total_questoes'] ?></h3>
                        <p>Questões Geradas</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Lista de Editais -->
        <section class="editais-section">
            <div class="card">
                <h2><i class="fas fa-list"></i> Editais Analisados</h2>
                
                <?php if (empty($editais)): ?>
                    <div class="empty-state">
                        <i class="fas fa-file-pdf"></i>
                        <h3>Nenhum edital enviado ainda</h3>
                        <p>Envie seu primeiro edital para começar a análise automática!</p>
                        <a href="upload_edital.php" class="btn-primary">
                            <i class="fas fa-upload"></i> Enviar Primeiro Edital
                        </a>
                    </div>
                <?php else: ?>
                    <div class="editais-grid">
                        <?php foreach ($editais as $edital): ?>
                            <div class="edital-card">
                                <div class="edital-header">
                                    <h3><?= htmlspecialchars($edital['nome_arquivo']) ?></h3>
                                    <span class="edital-date">
                                        <?= date('d/m/Y H:i', strtotime($edital['data_upload'])) ?>
                                    </span>
                                </div>
                                
                                <div class="edital-stats">
                                    <div class="stat">
                                        <i class="fas fa-graduation-cap"></i>
                                        <span><?= $edital['total_disciplinas'] ?> disciplinas</span>
                                    </div>
                                    
                                    <div class="stat">
                                        <i class="fas fa-question-circle"></i>
                                        <span><?= $edital['total_questoes'] ?> questões</span>
                                    </div>
                                </div>
                                
                                <div class="edital-actions">
                                    <a href="edital_detalhes.php?id=<?= $edital['id'] ?>" class="btn-primary">
                                        <i class="fas fa-eye"></i> Ver Detalhes
                                    </a>
                                    <a href="questoes.php?edital_id=<?= $edital['id'] ?>" class="btn-secondary">
                                        <i class="fas fa-question-circle"></i> Questões
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <style>
        .stats-section {
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            color: white;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #ff4444, #cc0000);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stat-icon i {
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-content h3 {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin: 0;
        }
        
        .stat-content p {
            color: rgba(255, 255, 255, 0.8);
            margin: 5px 0 0 0;
            font-weight: 500;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .empty-state h3 {
            color: white;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .editais-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .edital-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
            color: white;
        }
        
        .edital-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            border-color: #ff4444;
        }
        
        .edital-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .edital-header h3 {
            color: white;
            margin: 0;
            font-size: 1.2rem;
            flex: 1;
            margin-right: 15px;
        }
        
        .edital-date {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            white-space: nowrap;
        }
        
        .edital-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
        }
        
        .stat i {
            color: #ff4444;
            font-size: 1.1rem;
        }
        
        .edital-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 68, 68, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        .action-btn {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
    </style>
</body>
</html>
