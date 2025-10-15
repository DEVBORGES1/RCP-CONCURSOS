<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$simulado_id = $_GET['id'] ?? null;
$view_mode = isset($_GET['view']);

if (!$simulado_id) {
    header("Location: simulados.php");
    exit;
}

// Obter dados do simulado
$sql = "SELECT * FROM simulados WHERE id = ? AND usuario_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$simulado_id, $_SESSION["usuario_id"]]);
$simulado = $stmt->fetch();

if (!$simulado) {
    header("Location: simulados.php");
    exit;
}

// Obter questões do simulado
$sql = "SELECT sq.*, q.*, d.nome_disciplina 
        FROM simulados_questoes sq 
        JOIN questoes q ON sq.questao_id = q.id 
        LEFT JOIN disciplinas d ON q.disciplina_id = d.id
        WHERE sq.simulado_id = ? 
        ORDER BY sq.id";
$stmt = $pdo->prepare($sql);
$stmt->execute([$simulado_id]);
$questoes = $stmt->fetchAll();

if (empty($questoes)) {
    header("Location: simulados.php");
    exit;
}

$gamificacao = new Gamificacao($pdo);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($simulado['nome']) ?> - Sistema de Concursos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-clipboard-list"></i> <?= htmlspecialchars($simulado['nome']) ?></h1>
                <div class="user-info">
                    <?php if (!$view_mode): ?>
                        <div class="timer" id="timer">
                            <i class="fas fa-clock"></i>
                            <span id="time-display">00:00</span>
                        </div>
                    <?php endif; ?>
                    <a href="simulados.php" class="logout-btn">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </header>

        <?php if ($view_mode): ?>
            <!-- Modo Visualização de Resultado -->
            <section class="resultado-section">
                <div class="resultado-card">
                    <div class="resultado-header">
                        <h2><i class="fas fa-trophy"></i> Resultado do Simulado</h2>
                        <div class="resultado-stats">
                            <div class="stat-item">
                                <i class="fas fa-check-circle"></i>
                                <span><?= $simulado['questoes_corretas'] ?>/<?= $simulado['questoes_total'] ?></span>
                                <small>Acertos</small>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-star"></i>
                                <span><?= $simulado['pontuacao_final'] ?></span>
                                <small>Pontos</small>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-percentage"></i>
                                <span><?= round(($simulado['questoes_corretas'] / $simulado['questoes_total']) * 100, 1) ?>%</span>
                                <small>Taxa de Acerto</small>
                            </div>
                            <?php if ($simulado['tempo_gasto']): ?>
                                <div class="stat-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?= $simulado['tempo_gasto'] ?>min</span>
                                    <small>Tempo</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Questões -->
        <section class="questoes-section">
            <form id="simulado-form" method="POST" action="simulados.php">
                <input type="hidden" name="finalizar_simulado" value="1">
                <input type="hidden" name="simulado_id" value="<?= $simulado_id ?>">
                <input type="hidden" name="tempo_gasto" id="tempo-gasto" value="0">
                
                <?php foreach ($questoes as $index => $questao): ?>
                    <div class="questao-card">
                        <div class="questao-header">
                            <h3>Questão <?= $index + 1 ?></h3>
                            <?php if ($questao['nome_disciplina']): ?>
                                <span class="disciplina-tag"><?= htmlspecialchars($questao['nome_disciplina']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="questao-content">
                            <p class="enunciado"><?= nl2br(htmlspecialchars($questao['enunciado'])) ?></p>
                            
                            <div class="alternativas">
                                <?php foreach (['a', 'b', 'c', 'd', 'e'] as $alt): ?>
                                    <label class="alternativa <?= $view_mode && $questao['resposta_usuario'] == strtoupper($alt) ? 'selected' : '' ?> 
                                           <?= $view_mode && $questao['alternativa_correta'] == strtoupper($alt) ? 'correct' : '' ?>
                                           <?= $view_mode && $questao['resposta_usuario'] == strtoupper($alt) && $questao['correta'] == 0 ? 'incorrect' : '' ?>">
                                        <input type="radio" 
                                               name="questao_<?= $questao['questao_id'] ?>" 
                                               value="<?= strtoupper($alt) ?>"
                                               <?= $view_mode ? 'disabled' : '' ?>
                                               <?= $questao['resposta_usuario'] == strtoupper($alt) ? 'checked' : '' ?>>
                                        <span class="alternativa-letter"><?= strtoupper($alt) ?>)</span>
                                        <span class="alternativa-text"><?= htmlspecialchars($questao['alternativa_' . $alt]) ?></span>
                                        
                                        <?php if ($view_mode): ?>
                                            <?php if ($questao['alternativa_correta'] == strtoupper($alt)): ?>
                                                <i class="fas fa-check correct-icon"></i>
                                            <?php elseif ($questao['resposta_usuario'] == strtoupper($alt) && $questao['correta'] == 0): ?>
                                                <i class="fas fa-times incorrect-icon"></i>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (!$view_mode): ?>
                    <div class="submit-section">
                        <button type="submit" class="btn-primary btn-large">
                            <i class="fas fa-check"></i> Finalizar Simulado
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </section>
    </div>

    <script>
        <?php if (!$view_mode): ?>
        // Timer
        let startTime = Date.now();
        let timerInterval;
        
        function updateTimer() {
            const elapsed = Math.floor((Date.now() - startTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            
            document.getElementById('time-display').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            document.getElementById('tempo-gasto').value = Math.floor(elapsed / 60);
        }
        
        // Iniciar timer
        timerInterval = setInterval(updateTimer, 1000);
        
        // Parar timer ao enviar formulário
        document.getElementById('simulado-form').addEventListener('submit', function() {
            clearInterval(timerInterval);
        });
        
        // Salvar progresso automaticamente
        function saveProgress() {
            const formData = new FormData(document.getElementById('simulado-form'));
            formData.append('salvar_progresso', '1');
            
            fetch('simulados.php', {
                method: 'POST',
                body: formData
            });
        }
        
        // Salvar progresso a cada 30 segundos
        setInterval(saveProgress, 30000);
        
        // Salvar progresso ao sair da página
        window.addEventListener('beforeunload', saveProgress);
        <?php endif; ?>
        
        // Animações
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.questao-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>

    <style>
        .timer {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        
        .resultado-section {
            margin-bottom: 30px;
        }
        
        .resultado-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            color: white;
        }
        
        .resultado-header h2 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.8rem;
        }
        
        .resultado-header h2 i {
            color: #ff4444;
            margin-right: 10px;
        }
        
        .resultado-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            transition: all 0.3s ease;
            color: white;
        }
        
        .stat-item:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }
        
        .stat-item i {
            font-size: 2rem;
            color: #ff4444;
            margin-bottom: 10px;
        }
        
        .stat-item span {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 5px;
        }
        
        .stat-item small {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }
        
        .questoes-section {
            margin-bottom: 30px;
        }
        
        .questao-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
        }
        
        .questao-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .questao-header h3 {
            color: #2c3e50;
            margin: 0;
            font-size: 1.3rem;
        }
        
        .disciplina-tag {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .enunciado {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #2c3e50;
            margin-bottom: 25px;
        }
        
        .alternativas {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .alternativa {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .alternativa:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        
        .alternativa.selected {
            border-color: #667eea;
            background: #f8f9ff;
        }
        
        .alternativa.correct {
            border-color: #28a745;
            background: #d4edda;
        }
        
        .alternativa.incorrect {
            border-color: #dc3545;
            background: #f8d7da;
        }
        
        .alternativa input[type="radio"] {
            width: auto;
            margin: 0;
        }
        
        .alternativa-letter {
            font-weight: 700;
            color: #667eea;
            min-width: 25px;
        }
        
        .alternativa-text {
            flex: 1;
            color: #2c3e50;
        }
        
        .correct-icon {
            color: #28a745;
            font-size: 1.2rem;
        }
        
        .incorrect-icon {
            color: #dc3545;
            font-size: 1.2rem;
        }
        
        .submit-section {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn-large {
            padding: 20px 40px;
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .resultado-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .questao-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .alternativa {
                padding: 12px 15px;
            }
        }
    </style>
</body>
</html>
