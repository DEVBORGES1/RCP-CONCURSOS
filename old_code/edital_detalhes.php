<?php
session_start();
require 'conexao.php';
require 'classes/AnalisadorEdital.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$edital_id = $_GET['id'] ?? null;

if (!$edital_id) {
    header("Location: editais.php");
    exit;
}

// Obter dados do edital
$sql = "SELECT * FROM editais WHERE id = ? AND usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$edital_id, $_SESSION["usuario_id"]]);
$edital = $stmt->fetch();

if (!$edital) {
    header("Location: editais.php");
    exit;
}

// Obter disciplinas do edital
$sql = "SELECT d.*, COUNT(q.id) as total_questoes 
        FROM disciplinas d 
        LEFT JOIN questoes q ON d.id = q.disciplina_id 
        WHERE d.edital_id = ? 
        GROUP BY d.id 
        ORDER BY d.nome_disciplina";
$stmt = $pdo->prepare($sql);
$stmt->execute([$edital_id]);
$disciplinas = $stmt->fetchAll();

// Obter estatísticas
$sql = "SELECT 
            COUNT(DISTINCT d.id) as total_disciplinas,
            COUNT(q.id) as total_questoes
        FROM disciplinas d 
        LEFT JOIN questoes q ON d.id = q.disciplina_id 
        WHERE d.edital_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$edital_id]);
$estatisticas = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($edital['nome_arquivo']) ?> - Detalhes do Edital</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-file-alt"></i> Detalhes do Edital</h1>
                <div class="user-info">
                    <a href="editais.php" class="action-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Informações do Edital -->
        <section class="edital-info">
            <div class="card">
                <div class="edital-header">
                    <div class="edital-title">
                        <h2><i class="fas fa-file-pdf"></i> <?= htmlspecialchars($edital['nome_arquivo']) ?></h2>
                        <p>Enviado em <?= date('d/m/Y H:i', strtotime($edital['data_upload'])) ?></p>
                    </div>
                    <div class="edital-stats">
                        <div class="stat">
                            <i class="fas fa-graduation-cap"></i>
                            <span><?= $estatisticas['total_disciplinas'] ?> disciplinas</span>
                        </div>
                        <div class="stat">
                            <i class="fas fa-question-circle"></i>
                            <span><?= $estatisticas['total_questoes'] ?> questões</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Texto do Edital -->
        <section class="edital-text">
            <div class="card">
                <h3><i class="fas fa-align-left"></i> Texto Extraído</h3>
                <div class="text-content">
                    <pre><?= htmlspecialchars($edital['texto_extraido']) ?></pre>
                </div>
            </div>
        </section>

        <!-- Disciplinas Detectadas -->
        <section class="disciplinas-section">
            <div class="card">
                <h3><i class="fas fa-graduation-cap"></i> Disciplinas Detectadas</h3>
                
                <?php if (empty($disciplinas)): ?>
                    <div class="empty-state">
                        <i class="fas fa-graduation-cap"></i>
                        <h4>Nenhuma disciplina detectada</h4>
                        <p>O sistema não conseguiu identificar disciplinas neste edital.</p>
                    </div>
                <?php else: ?>
                    <div class="disciplinas-grid">
                        <?php foreach ($disciplinas as $disciplina): ?>
                            <div class="disciplina-card">
                                <div class="disciplina-header">
                                    <h4><?= htmlspecialchars($disciplina['nome_disciplina']) ?></h4>
                                    <span class="questoes-count">
                                        <i class="fas fa-question-circle"></i>
                                        <?= $disciplina['total_questoes'] ?> questões
                                    </span>
                                </div>
                                
                                <div class="disciplina-actions">
                                    <a href="questoes.php?disciplina_id=<?= $disciplina['id'] ?>" class="btn-primary">
                                        <i class="fas fa-eye"></i> Ver Questões
                                    </a>
                                    <a href="simulados.php?disciplina_id=<?= $disciplina['id'] ?>" class="btn-secondary">
                                        <i class="fas fa-play"></i> Criar Simulado
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Ações Rápidas -->
        <section class="quick-actions">
            <div class="card">
                <h3><i class="fas fa-bolt"></i> Ações Rápidas</h3>
                <div class="actions-grid">
                    <a href="questoes.php?edital_id=<?= $edital_id ?>" class="action-btn">
                        <i class="fas fa-question-circle"></i>
                        <span>Todas as Questões</span>
                    </a>
                    <a href="simulados.php?edital_id=<?= $edital_id ?>" class="action-btn">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Criar Simulado</span>
                    </a>
                    <a href="gerar_cronograma.php?edital_id=<?= $edital_id ?>" class="action-btn">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Gerar Cronograma</span>
                    </a>
                </div>
            </div>
        </section>
    </div>

    <style>
        .edital-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .edital-title h2 {
            color: white;
            margin: 0 0 5px 0;
        }
        
        .edital-title p {
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
        }
        
        .edital-stats {
            display: flex;
            gap: 20px;
        }
        
        .stat {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }
        
        .stat i {
            color: #ff4444;
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
        
        .text-content {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .text-content pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: white;
        }
        
        .disciplinas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .disciplina-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            color: white;
        }
        
        .disciplina-card:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            border-color: #ff4444;
        }
        
        .disciplina-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .disciplina-header h4 {
            color: white;
            margin: 0;
            font-size: 1.1rem;
        }
        
        .questoes-count {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .questoes-count i {
            color: #667eea;
        }
        
        .disciplina-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .action-btn {
            background: #6c757d;
            color: white;
            padding: 15px 20px;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .action-btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ccc;
        }
        
        .empty-state h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
    </style>
</body>
</html>
