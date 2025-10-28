<?php
session_start();
require 'conexao.php';
require 'classes/GeradorCronograma.php';
require 'classes/GeradorPDFCronograma.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$mensagem = "";
$tipo_mensagem = "";
$cronograma_gerado = null;

// Obter editais do usuário
$sql = "SELECT e.*, COUNT(DISTINCT d.id) as total_disciplinas 
        FROM editais e 
        LEFT JOIN disciplinas d ON e.id = d.edital_id 
        WHERE e.usuario_id = ? 
        GROUP BY e.id 
        ORDER BY e.data_upload DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$editais = $stmt->fetchAll();

// Obter cronogramas existentes
$sql = "SELECT c.*, e.nome_arquivo 
        FROM cronogramas c 
        JOIN editais e ON c.edital_id = e.id 
        WHERE c.usuario_id = ? 
        ORDER BY c.data_inicio DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$cronogramas = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['gerar_cronograma'])) {
        $edital_id = $_POST['edital_id'];
        $horas_por_dia = (int)$_POST['horas_por_dia'];
        $duracao_semanas = (int)$_POST['duracao_semanas'];
        $data_inicio = $_POST['data_inicio'];
        
        // Validar dados
        if ($horas_por_dia < 1 || $horas_por_dia > 8) {
            $mensagem = "Horas por dia deve estar entre 1 e 8.";
            $tipo_mensagem = "error";
        } elseif ($duracao_semanas < 1 || $duracao_semanas > 12) {
            $mensagem = "Duração deve estar entre 1 e 12 semanas.";
            $tipo_mensagem = "error";
        } else {
            // Gerar cronograma
            $gerador = new GeradorCronograma($pdo);
            $resultado = $gerador->gerarCronograma(
                $_SESSION["usuario_id"], 
                $edital_id, 
                $horas_por_dia, 
                $data_inicio, 
                $duracao_semanas
            );
            
            if ($resultado['sucesso']) {
                $mensagem = "Cronograma gerado com sucesso! ";
                $mensagem .= "Foram distribuídas " . count($resultado['disciplinas']) . " disciplinas ao longo de {$duracao_semanas} semanas.";
                $tipo_mensagem = "success";
                $cronograma_gerado = $resultado;
            } else {
                $mensagem = "Erro ao gerar cronograma: " . $resultado['erro'];
                $tipo_mensagem = "error";
            }
        }
    }
    
    if (isset($_POST['gerar_pdf'])) {
        $cronograma_id = $_POST['cronograma_id'];
        
        $gerador_pdf = new GeradorPDFCronograma($pdo);
        $resultado_pdf = $gerador_pdf->gerarPDF($cronograma_id, $_SESSION["usuario_id"]);
        
        if ($resultado_pdf['sucesso']) {
            // Forçar download do arquivo
            header('Content-Type: text/html');
            header('Content-Disposition: attachment; filename="cronograma_estudos.html"');
            readfile($resultado_pdf['caminho']);
            exit;
        } else {
            $mensagem = "Erro ao gerar PDF: " . $resultado_pdf['erro'];
            $tipo_mensagem = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar Cronograma - Sistema de Concursos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-calendar-alt"></i> Gerar Cronograma</h1>
                <div class="user-info">
                    <a href="dashboard.php" class="action-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>
        </header>

        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo_mensagem ?>">
                <i class="fas fa-<?= $tipo_mensagem == 'success' ? 'check-circle' : 'times-circle' ?>"></i>
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de Geração -->
        <section class="generate-section">
            <div class="card">
                <h2><i class="fas fa-magic"></i> Criar Novo Cronograma</h2>
                <p>Configure seu cronograma personalizado baseado no edital e suas disponibilidades.</p>
                
                <form method="POST" class="cronograma-form">
                    <input type="hidden" name="gerar_cronograma" value="1">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edital_id">Selecionar Edital:</label>
                            <select id="edital_id" name="edital_id" required>
                                <option value="">Escolha um edital...</option>
                                <?php foreach ($editais as $edital): ?>
                                    <option value="<?= $edital['id'] ?>">
                                        <?= htmlspecialchars($edital['nome_arquivo']) ?> 
                                        (<?= $edital['total_disciplinas'] ?> disciplinas)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="data_inicio">Data de Início:</label>
                            <input type="date" id="data_inicio" name="data_inicio" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="horas_por_dia">Horas por Dia:</label>
                            <select id="horas_por_dia" name="horas_por_dia" required>
                                <option value="1">1 hora</option>
                                <option value="2">2 horas</option>
                                <option value="3" selected>3 horas</option>
                                <option value="4">4 horas</option>
                                <option value="5">5 horas</option>
                                <option value="6">6 horas</option>
                                <option value="7">7 horas</option>
                                <option value="8">8 horas</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="duracao_semanas">Duração (semanas):</label>
                            <select id="duracao_semanas" name="duracao_semanas" required>
                                <option value="1">1 semana</option>
                                <option value="2">2 semanas</option>
                                <option value="3">3 semanas</option>
                                <option value="4" selected>4 semanas</option>
                                <option value="6">6 semanas</option>
                                <option value="8">8 semanas</option>
                                <option value="12">12 semanas</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-calendar-plus"></i> Gerar Cronograma
                    </button>
                </form>
            </div>
        </section>

        <!-- Cronogramas Existentes -->
        <?php if (!empty($cronogramas)): ?>
        <section class="existing-cronogramas">
            <div class="card">
                <h2><i class="fas fa-history"></i> Cronogramas Existentes</h2>
                
                <div class="cronogramas-grid">
                    <?php foreach ($cronogramas as $cronograma): ?>
                        <div class="cronograma-card">
                            <div class="cronograma-header">
                                <h3><?= htmlspecialchars($cronograma['nome_arquivo']) ?></h3>
                                <span class="cronograma-date">
                                    <?= date('d/m/Y', strtotime($cronograma['data_inicio'])) ?> - 
                                    <?= date('d/m/Y', strtotime($cronograma['data_fim'])) ?>
                                </span>
                            </div>
                            
                            <div class="cronograma-stats">
                                <div class="stat">
                                    <i class="fas fa-clock"></i>
                                    <span><?= $cronograma['horas_por_dia'] ?>h/dia</span>
                                </div>
                                
                                <div class="stat">
                                    <i class="fas fa-calendar"></i>
                                    <span><?= ceil((strtotime($cronograma['data_fim']) - strtotime($cronograma['data_inicio'])) / (60 * 60 * 24)) ?> dias</span>
                                </div>
                            </div>
                            
                            <div class="cronograma-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="cronograma_id" value="<?= $cronograma['id'] ?>">
                                    <input type="hidden" name="gerar_pdf" value="1">
                                    <button type="submit" class="btn-primary">
                                        <i class="fas fa-download"></i> Baixar PDF
                                    </button>
                                </form>
                                
                                <a href="cronograma_detalhes.php?id=<?= $cronograma['id'] ?>" class="btn-secondary">
                                    <i class="fas fa-eye"></i> Ver Detalhes
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Preview do Cronograma Gerado -->
        <?php if ($cronograma_gerado): ?>
        <section class="preview-section">
            <div class="card">
                <h2><i class="fas fa-eye"></i> Preview do Cronograma</h2>
                
                <div class="preview-stats">
                    <div class="stat-item">
                        <h3><?= count($cronograma_gerado['disciplinas']) ?></h3>
                        <p>Disciplinas</p>
                    </div>
                    <div class="stat-item">
                        <h3><?= $cronograma_gerado['data_inicio'] ?></h3>
                        <p>Data Início</p>
                    </div>
                    <div class="stat-item">
                        <h3><?= $cronograma_gerado['data_fim'] ?></h3>
                        <p>Data Fim</p>
                    </div>
                </div>
                
                <div class="disciplinas-preview">
                    <h3>Disciplinas Incluídas:</h3>
                    <div class="disciplinas-list">
                        <?php foreach ($cronograma_gerado['disciplinas'] as $disciplina): ?>
                            <span class="disciplina-tag">
                                <i class="fas fa-graduation-cap"></i>
                                <?= htmlspecialchars($disciplina['nome_disciplina']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="preview-actions">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="cronograma_id" value="<?= $cronograma_gerado['cronograma_id'] ?>">
                        <input type="hidden" name="gerar_pdf" value="1">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-download"></i> Baixar Cronograma PDF
                        </button>
                    </form>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </div>

    <style>
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }
        
        .alert-success {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
        }
        
        .alert-error {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
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
        
        .cronograma-form {
            margin-top: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: white;
        }
        
        .form-group select,
        .form-group input {
            padding: 12px 15px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(0, 0, 0, 0.6);
            color: white;
        }

        .form-group select option {
            background: #2c3e50;
            color: white;
            padding: 8px 12px;
        }

        .form-group select option:hover {
            background: #34495e;
        }

        .form-group select option:checked {
            background: #ff4444;
            color: white;
        }

        .form-group select::placeholder,
        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #ff4444;
            box-shadow: 0 0 0 3px rgba(255, 68, 68, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 1rem;
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
            border-radius: 8px;
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
        
        .cronogramas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .cronograma-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .cronograma-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-color: #667eea;
        }
        
        .cronograma-header {
            margin-bottom: 15px;
        }
        
        .cronograma-header h3 {
            color: #2c3e50;
            margin: 0 0 5px 0;
            font-size: 1.1rem;
        }
        
        .cronograma-date {
            color: #666;
            font-size: 0.9rem;
        }
        
        .cronograma-stats {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .stat {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .stat i {
            color: #667eea;
        }
        
        .cronograma-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .preview-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .stat-item h3 {
            color: #667eea;
            margin: 0 0 5px 0;
            font-size: 1.5rem;
        }
        
        .stat-item p {
            color: #666;
            margin: 0;
            font-size: 0.9rem;
        }
        
        .disciplinas-preview {
            margin: 20px 0;
        }
        
        .disciplinas-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .disciplina-tag {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .preview-actions {
            text-align: center;
            margin-top: 20px;
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