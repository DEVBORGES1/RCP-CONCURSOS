<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$gamificacao = new Gamificacao($pdo);
$mensagem = "";

// Adicionar nova questão
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adicionar_questao'])) {
    $edital_id = $_POST['edital_id'];
    $disciplina_id = $_POST['disciplina_id'] ?? null;
    $enunciado = $_POST['enunciado'];
    $a = $_POST['a'];
    $b = $_POST['b'];
    $c = $_POST['c'];
    $d = $_POST['d'];
    $e = $_POST['e'];
    $correta = strtoupper($_POST['correta']);
    
    $sql = "INSERT INTO questoes (edital_id, disciplina_id, enunciado, alternativa_a, alternativa_b, alternativa_c, alternativa_d, alternativa_e, alternativa_correta)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$edital_id, $disciplina_id, $enunciado, $a, $b, $c, $d, $e, $correta]);
    
    $mensagem = "Questão adicionada com sucesso!";
}

// Responder questão individual
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['responder_questao'])) {
    $questao_id = $_POST['questao_id'];
    $resposta = strtoupper($_POST['resposta']);
    
    // Obter resposta correta
    $sql = "SELECT alternativa_correta FROM questoes WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$questao_id]);
    $resposta_correta = $stmt->fetchColumn();
    
    $acertou = ($resposta == $resposta_correta) ? 1 : 0;
    $pontos = $acertou ? 10 : 0;
    
    // Registrar resposta
    $sql = "INSERT INTO respostas_usuario (usuario_id, questao_id, resposta, correta, pontos_ganhos)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION["usuario_id"], $questao_id, $resposta, $acertou, $pontos]);
    
    // Adicionar pontos
    $gamificacao->adicionarPontos($_SESSION["usuario_id"], $pontos, 'questao');
    
    $mensagem = $acertou ? 
        "Parabéns! Você acertou e ganhou $pontos pontos!" : 
        "Que pena! A resposta correta era $resposta_correta. Continue tentando!";
}

// Obter editais do usuário
$sql = "SELECT * FROM editais WHERE usuario_id = ? ORDER BY data_upload DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$editais = $stmt->fetchAll();

// Obter disciplinas
$sql = "SELECT DISTINCT d.* FROM disciplinas d 
        JOIN questoes q ON d.id = q.disciplina_id 
        WHERE q.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$disciplinas = $stmt->fetchAll();

// Obter questões para prática
$sql = "SELECT q.*, d.nome_disciplina, e.nome_arquivo,
               (SELECT COUNT(*) FROM respostas_usuario r WHERE r.questao_id = q.id AND r.usuario_id = ?) as ja_respondida
        FROM questoes q 
        LEFT JOIN disciplinas d ON q.disciplina_id = d.id
        LEFT JOIN editais e ON q.edital_id = e.id
        WHERE q.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)
        ORDER BY RAND() 
        LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"], $_SESSION["usuario_id"]]);
$questoes_pratica = $stmt->fetchAll();

// Obter estatísticas
$sql = "SELECT COUNT(*) as total FROM questoes q 
        WHERE q.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$total_questoes = $stmt->fetchColumn();

$sql = "SELECT COUNT(*) as respondidas FROM respostas_usuario r
        JOIN questoes q ON r.questao_id = q.id
        WHERE r.usuario_id = ? AND q.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"], $_SESSION["usuario_id"]]);
$questoes_respondidas = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banco de Questões - Sistema de Concursos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                <a href="dashboard.php" class="nav-item">
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
                <a href="questoes.php" class="nav-item active">
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
                <h1><i class="fas fa-question-circle"></i> Banco de Questões</h1>
                <div class="user-info">
                    <a href="dashboard.php" class="action-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>
        </header>

        <?php if ($mensagem): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <?= $mensagem ?>
            </div>
        <?php endif; ?>

        <!-- Estatísticas -->
        <section class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $total_questoes ?></h3>
                        <p>Total de Questões</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $questoes_respondidas ?></h3>
                        <p>Questões Respondidas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $total_questoes > 0 ? round(($questoes_respondidas / $total_questoes) * 100, 1) : 0 ?>%</h3>
                        <p>Progresso</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Adicionar Questão -->
        <section class="add-questao-section">
            <div class="card">
                <h2><i class="fas fa-plus-circle"></i> Adicionar Nova Questão</h2>
<form method="POST">
                    <input type="hidden" name="adicionar_questao" value="1">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edital_id">Edital:</label>
                            <select id="edital_id" name="edital_id" required>
                                <option value="">Selecione um edital</option>
                                <?php foreach ($editais as $edital): ?>
                                    <option value="<?= $edital['id'] ?>">
                                        <?= htmlspecialchars($edital['nome_arquivo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="disciplina_id">Disciplina (opcional):</label>
                            <select id="disciplina_id" name="disciplina_id">
                                <option value="">Selecione uma disciplina</option>
                                <?php foreach ($disciplinas as $disciplina): ?>
                                    <option value="<?= $disciplina['id'] ?>">
                                        <?= htmlspecialchars($disciplina['nome_disciplina']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="enunciado">Enunciado:</label>
                        <textarea id="enunciado" name="enunciado" rows="4" required></textarea>
                    </div>
                    
                    <div class="alternativas-grid">
                        <div class="form-group">
                            <label for="a">A)</label>
                            <input type="text" id="a" name="a" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="b">B)</label>
                            <input type="text" id="b" name="b" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="c">C)</label>
                            <input type="text" id="c" name="c" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="d">D)</label>
                            <input type="text" id="d" name="d" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="e">E)</label>
                            <input type="text" id="e" name="e" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="correta">Resposta Correta:</label>
                        <select id="correta" name="correta" required>
                            <option value="">Selecione</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-plus"></i> Adicionar Questão
                    </button>
                </form>
            </div>
        </section>

        <!-- Prática de Questões -->
        <section class="pratica-section">
            <div class="card">
                <h2><i class="fas fa-play-circle"></i> Prática de Questões</h2>
                
                <?php if (empty($questoes_pratica)): ?>
                    <div class="empty-state">
                        <i class="fas fa-question-circle"></i>
                        <h3>Nenhuma questão disponível</h3>
                        <p>Adicione questões primeiro para começar a praticar!</p>
                    </div>
                <?php else: ?>
                    <div class="questoes-grid">
                        <?php foreach ($questoes_pratica as $questao): ?>
                            <div class="questao-card">
                                <div class="questao-header">
                                    <h3>Questão #<?= $questao['id'] ?></h3>
                                    <div class="questao-meta">
                                        <?php if ($questao['nome_disciplina']): ?>
                                            <span class="disciplina-tag"><?= htmlspecialchars($questao['nome_disciplina']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($questao['ja_respondida']): ?>
                                            <span class="respondida-tag">Respondida</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="questao-content">
                                    <p class="enunciado"><?= nl2br(htmlspecialchars(substr($questao['enunciado'], 0, 150))) ?>...</p>
                                    
                                    <div class="questao-actions">
                                        <button class="btn-primary btn-small" onclick="abrirQuestao(<?= $questao['id'] ?>)">
                                            <i class="fas fa-eye"></i> Ver Questão
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        </div>
    </div>

    <!-- Modal da Questão -->
    <div id="questao-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-titulo">Questão</h2>
                <span class="close" onclick="fecharModal()">&times;</span>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
        </div>
    </div>

    <script>
        function abrirQuestao(questaoId) {
            fetch(`questao_individual.php?id=${questaoId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modal-body').innerHTML = html;
                    document.getElementById('questao-modal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Erro:', error);
                });
        }
        
        function fecharModal() {
            document.getElementById('questao-modal').style.display = 'none';
        }
        
        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modal = document.getElementById('questao-modal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>

    <script>
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

    <style>
        .alert {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .alternativas-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .questoes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .questao-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
            color: white;
        }
        
        .questao-card:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }
        
        .questao-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .questao-header h3 {
            color: white;
            margin: 0;
        }
        
        .questao-meta {
            display: flex;
            gap: 10px;
        }
        
        .disciplina-tag {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        
        .respondida-tag {
            background: #ff4444;
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        
        .enunciado {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .btn-small {
            padding: 8px 16px;
            font-size: 0.9rem;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
        }
        
        .modal-header h2 {
            margin: 0;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .close {
            font-size: 2rem;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.8);
            transition: color 0.3s ease;
        }
        
        .close:hover {
            color: #ff4444;
        }
        
        .modal-body {
            padding: 30px;
            background: rgba(0, 0, 0, 0.3);
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .alternativas-grid {
                grid-template-columns: 1fr;
            }
            
            .questao-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</body>
</html>