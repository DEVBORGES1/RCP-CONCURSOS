<?php
session_start();
require 'conexao.php';
require 'classes/AnalisadorEdital.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$mensagem = "";
$tipo_mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["edital"])) {
    $targetDir = "uploads/";
    $fileName = basename($_FILES["edital"]["name"]);
    $targetFile = $targetDir . uniqid() . "_" . $fileName;
    
    if (move_uploaded_file($_FILES["edital"]["tmp_name"], $targetFile)) {
        // Simular extração de texto do PDF (em produção, usar biblioteca como pdfparser)
        $texto = gerarTextoSimuladoEdital();
        
        // Salvar edital no banco
        $sql = "INSERT INTO editais (usuario_id, nome_arquivo, texto_extraido) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION["usuario_id"], $fileName, $texto]);
        $edital_id = $pdo->lastInsertId();
        
        // Analisar edital automaticamente
        $analisador = new AnalisadorEdital($pdo);
        $resultado = $analisador->analisarEdital($edital_id, $texto);
        
        if ($resultado['sucesso']) {
            $mensagem = "Edital enviado e analisado com sucesso! ";
            $mensagem .= "Foram encontradas {$resultado['disciplinas_encontradas']} disciplinas e geradas questões automáticas.";
            $tipo_mensagem = "success";
        } else {
            $mensagem = "Edital enviado, mas houve erro na análise: " . $resultado['erro'];
            $tipo_mensagem = "warning";
        }
    } else {
        $mensagem = "Erro ao enviar edital.";
        $tipo_mensagem = "error";
    }
}

// Função para gerar texto simulado de edital (substituir por parser real)
function gerarTextoSimuladoEdital() {
    return "
    EDITAL DE CONCURSO PÚBLICO
    
    DISCIPLINAS E CONTEÚDOS PROGRAMÁTICOS:
    
    PORTUGUÊS:
    - Interpretação de texto
    - Gramática: concordância, regência, crase
    - Ortografia e acentuação
    - Literatura brasileira
    
    MATEMÁTICA:
    - Aritmética e álgebra
    - Geometria plana e espacial
    - Trigonometria
    - Estatística básica
    
    RACIOCÍNIO LÓGICO:
    - Lógica proposicional
    - Raciocínio sequencial
    - Problemas de lógica
    - Análise combinatória
    
    INFORMÁTICA:
    - Sistema operacional Windows
    - Microsoft Office (Word, Excel, PowerPoint)
    - Internet e navegadores
    - Segurança da informação
    
    DIREITO CONSTITUCIONAL:
    - Constituição Federal de 1988
    - Direitos e garantias fundamentais
    - Organização do Estado
    - Poderes da União
    
    DIREITO ADMINISTRATIVO:
    - Princípios da Administração Pública
    - Atos administrativos
    - Serviços públicos
    - Controle da Administração Pública
    
    ATUALIDADES:
    - Política nacional e internacional
    - Economia brasileira
    - Meio ambiente
    - Tecnologia e sociedade
    ";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Edital - Sistema de Concursos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-upload"></i> Upload de Edital</h1>
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
                <i class="fas fa-<?= $tipo_mensagem == 'success' ? 'check-circle' : ($tipo_mensagem == 'warning' ? 'exclamation-triangle' : 'times-circle') ?>"></i>
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <!-- Upload Form -->
        <section class="upload-section">
            <div class="card">
                <h2><i class="fas fa-file-pdf"></i> Enviar Edital</h2>
                <p>Envie o PDF do edital para análise automática das disciplinas e geração de questões.</p>
                
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <div class="form-group">
                        <label for="edital">Selecionar Arquivo PDF:</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="edital" name="edital" accept=".pdf" required>
                            <label for="edital" class="file-input-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Clique para selecionar o arquivo PDF</span>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-upload"></i> Enviar e Analisar Edital
                    </button>
                </form>
            </div>
        </section>

        <!-- Features Info -->
        <section class="features-info">
            <div class="card">
                <h3><i class="fas fa-magic"></i> O que acontece após o upload?</h3>
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4>Análise Automática</h4>
                        <p>O sistema analisa o texto do edital e identifica automaticamente as disciplinas.</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h4>Disciplinas Detectadas</h4>
                        <p>As disciplinas são cadastradas automaticamente no sistema.</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h4>Questões Geradas</h4>
                        <p>Questões de exemplo são criadas automaticamente para cada disciplina.</p>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h4>Simulados Prontos</h4>
                        <p>Você pode criar simulados imediatamente com as disciplinas detectadas.</p>
                    </div>
                </div>
            </div>
        </section>
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
        
        .alert-warning {
            background: linear-gradient(45deg, #ff6666, #ff3333);
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
        
        .upload-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: white;
        }
        
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-input-wrapper input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-input-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            border: 3px dashed #ff4444;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            color: white;
        }
        
        .file-input-label:hover {
            border-color: #cc0000;
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .file-input-label i {
            font-size: 2.5rem;
            color: #ff4444;
            margin-bottom: 10px;
        }
        
        .file-input-label span {
            color: white;
            font-weight: 500;
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
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .feature-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            margin-bottom: 15px;
        }
        
        .feature-icon i {
            font-size: 2rem;
            color: #667eea;
        }
        
        .feature-item h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .feature-item p {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.5;
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

    <script>
        // Mostrar nome do arquivo selecionado
        document.getElementById('edital').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const label = document.querySelector('.file-input-label span');
            if (fileName) {
                label.textContent = `Arquivo selecionado: ${fileName}`;
            } else {
                label.textContent = 'Clique para selecionar o arquivo PDF';
            }
        });
    </script>
</body>
</html>