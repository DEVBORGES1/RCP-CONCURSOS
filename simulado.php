<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$simulado_id = $_GET['id'] ?? null;
$predefined_type = $_GET['predefined'] ?? null;
$view_mode = isset($_GET['view']);

// Processar simulados pré-definidos
if ($predefined_type) {
    // Debug: Log do tipo de simulado solicitado
    error_log("SIMULADO DEBUG - Tipo solicitado: $predefined_type");
    
    $predefined_configs = [
        'geral' => [
            'nome' => 'Simulado Geral Básico',
            'quantidade' => 15,
            'disciplinas' => null
        ],
        'portugues-matematica' => [
            'nome' => 'Simulado Português e Matemática',
            'quantidade' => 12,
            'disciplinas' => ['Português', 'Matemática']
        ],
        'especificos' => [
            'nome' => 'Simulado Conhecimentos Específicos',
            'quantidade' => 10,
            'disciplinas' => ['Direito', 'Administração', 'Atualidades']
        ],
        'logico-informatica' => [
            'nome' => 'Simulado Raciocínio e Informática',
            'quantidade' => 10,
            'disciplinas' => ['Raciocínio Lógico', 'Informática']
        ],
        'completo' => [
            'nome' => 'Simulado Completo',
            'quantidade' => 30,
            'disciplinas' => null
        ]
    ];
    
    if (!isset($predefined_configs[$predefined_type])) {
        header("Location: simulados.php");
        exit;
    }
    
    $config = $predefined_configs[$predefined_type];
    
    // Verificar se já existe um simulado pré-definido para este usuário
    $sql = "SELECT * FROM simulados WHERE usuario_id = ? AND nome = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION["usuario_id"], $config['nome']]);
    $simulado = $stmt->fetch();
    
    if (!$simulado) {
        // Debug: Log de criação de novo simulado
        error_log("SIMULADO DEBUG - Criando novo simulado: {$config['nome']} para usuário: {$_SESSION['usuario_id']}");
        
        // Verificar se há questões suficientes disponíveis
        $sql_verificacao = "SELECT COUNT(DISTINCT q.id) as total_questoes FROM questoes q 
                           LEFT JOIN disciplinas d ON q.disciplina_id = d.id 
                           WHERE q.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)";
        $params_verificacao = [$_SESSION["usuario_id"]];
        
        if ($config['disciplinas']) {
            $placeholders_verificacao = str_repeat('?,', count($config['disciplinas']) - 1) . '?';
            $sql_verificacao .= " AND d.nome_disciplina IN ($placeholders_verificacao)";
            $params_verificacao = array_merge($config['disciplinas'], $params_verificacao);
        }
        
        $stmt_verificacao = $pdo->prepare($sql_verificacao);
        $stmt_verificacao->execute($params_verificacao);
        $total_disponivel = $stmt_verificacao->fetchColumn();
        
        // Debug: Log de questões disponíveis
        error_log("SIMULADO DEBUG - Questões disponíveis: $total_disponivel, Solicitadas: {$config['quantidade']}");
        
        if ($total_disponivel < $config['quantidade']) {
            // Ajustar quantidade para o que está disponível
            $config['quantidade'] = max(1, $total_disponivel);
            error_log("SIMULADO DEBUG - Ajustando quantidade para: {$config['quantidade']}");
        }
        // Criar novo simulado pré-definido
        $sql = "INSERT INTO simulados (usuario_id, nome, questoes_total) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION["usuario_id"], $config['nome'], $config['quantidade']]);
        $simulado_id = $pdo->lastInsertId();
        
        // Selecionar questões baseado no tipo
        $where_clause = "";
        $params = [];
        
        if ($config['disciplinas']) {
            $placeholders = str_repeat('?,', count($config['disciplinas']) - 1) . '?';
            $where_clause = "WHERE d.nome_disciplina IN ($placeholders)";
            $params = $config['disciplinas'];
        }
        
        $sql = "SELECT DISTINCT q.* FROM questoes q 
                LEFT JOIN disciplinas d ON q.disciplina_id = d.id 
                $where_clause
                AND q.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)
                ORDER BY RAND() LIMIT " . $config['quantidade'];
        $params[] = $_SESSION["usuario_id"];
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $questoes_selecionadas = $stmt->fetchAll();
        
        // Debug: Log de questões selecionadas
        error_log("SIMULADO DEBUG - Questões selecionadas (primeira tentativa): " . count($questoes_selecionadas));
        
        // Se não há questões suficientes, pegar todas as disponíveis (sem duplicatas)
        if (count($questoes_selecionadas) < $config['quantidade']) {
            $sql = "SELECT DISTINCT q.* FROM questoes q 
                    WHERE q.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)
                    ORDER BY RAND() LIMIT " . $config['quantidade'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION["usuario_id"]]);
            $questoes_selecionadas = $stmt->fetchAll();
            
            // Debug: Log de questões selecionadas (segunda tentativa)
            error_log("SIMULADO DEBUG - Questões selecionadas (segunda tentativa): " . count($questoes_selecionadas));
        }
        
        // Adicionar questões ao simulado (evitar duplicatas)
        $questoes_ja_adicionadas = [];
        foreach ($questoes_selecionadas as $questao) {
            if (!in_array($questao['id'], $questoes_ja_adicionadas)) {
                $sql = "INSERT INTO simulados_questoes (simulado_id, questao_id) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$simulado_id, $questao['id']]);
                $questoes_ja_adicionadas[] = $questao['id'];
            }
        }
        
        // Recarregar dados do simulado
        $sql = "SELECT * FROM simulados WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$simulado_id]);
        $simulado = $stmt->fetch();
    } else {
        $simulado_id = $simulado['id'];
    }
} else {
    if (!$simulado_id) {
        header("Location: simulados.php");
        exit;
    }
    
    // Obter dados do simulado normal
    $sql = "SELECT * FROM simulados WHERE id = ? AND usuario_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$simulado_id, $_SESSION["usuario_id"]]);
    $simulado = $stmt->fetch();
    
    if (!$simulado) {
        header("Location: simulados.php");
        exit;
    }
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
                <a href="questoes.php" class="nav-item">
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
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .questao-header h3 {
            color: white;
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .disciplina-tag {
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .enunciado {
            font-size: 1.1rem;
            line-height: 1.6;
            color: white;
            margin-bottom: 25px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            border-left: 4px solid #ff4444;
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
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .alternativa:hover {
            border-color: #ff4444;
            background: rgba(255, 255, 255, 0.15);
        }
        
        .alternativa.selected {
            border-color: #ff4444;
            background: rgba(255, 255, 255, 0.2);
        }
        
        .alternativa.correct {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.2);
        }
        
        .alternativa.incorrect {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.2);
        }
        
        .alternativa input[type="radio"] {
            width: auto;
            margin: 0;
        }
        
        .alternativa-letter {
            font-weight: 700;
            color: #ff4444;
            min-width: 25px;
        }
        
        .alternativa-text {
            flex: 1;
            color: white;
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
            background: linear-gradient(45deg, #ff4444, #cc0000);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 68, 68, 0.3);
        }
        
        .btn-large:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 68, 68, 0.4);
            background: linear-gradient(45deg, #cc0000, #990000);
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
