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

// Criar novo simulado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['criar_simulado'])) {
    $nome_simulado = $_POST['nome_simulado'];
    $quantidade_questoes = $_POST['quantidade_questoes'];
    $disciplina_id = $_POST['disciplina_id'] ?? null;

    // Criar simulado
    $sql = "INSERT INTO simulados (usuario_id, nome, questoes_total) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION["usuario_id"], $nome_simulado, $quantidade_questoes]);
    $simulado_id = $pdo->lastInsertId();

    // Selecionar questões aleatórias
    $where_clause = "";
    $params = [];

    if ($disciplina_id) {
        $where_clause = "WHERE disciplina_id = ?";
        $params[] = $disciplina_id;
    }

    // Validar quantidade de questões para evitar SQL injection
    $quantidade_questoes = (int)$quantidade_questoes;
    if ($quantidade_questoes <= 0) {
        $quantidade_questoes = 5;
    }

    $sql = "SELECT * FROM questoes $where_clause ORDER BY RAND() LIMIT " . $quantidade_questoes;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $questoes = $stmt->fetchAll();

    // Adicionar questões ao simulado
    foreach ($questoes as $questao) {
        $sql = "INSERT INTO simulados_questoes (simulado_id, questao_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$simulado_id, $questao['id']]);
    }

    header("Location: simulado.php?id=" . $simulado_id);
    exit;
}

// Processar respostas do simulado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['finalizar_simulado'])) {
    $simulado_id = $_POST['simulado_id'];
    $tempo_gasto = $_POST['tempo_gasto'];
    $pontos_total = 0;
    $questoes_corretas = 0;

    // Processar cada resposta
    foreach ($_POST as $key => $resposta) {
        if (strpos($key, 'questao_') === 0) {
            $questao_id = str_replace('questao_', '', $key);

            // Obter resposta correta
            $sql = "SELECT alternativa_correta FROM questoes WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$questao_id]);
            $resposta_correta = $stmt->fetchColumn();

            // Normalizar respostas para comparação
            $resposta_normalizada = strtoupper(trim($resposta));
            $resposta_correta_normalizada = strtoupper(trim($resposta_correta));

            // Debug: Log das respostas para verificação
            error_log("SIMULADO DEBUG - Questão ID: $questao_id, Resposta usuário: '$resposta_normalizada', Resposta correta: '$resposta_correta_normalizada'");

            $acertou = ($resposta_normalizada == $resposta_correta_normalizada) ? 1 : 0;
            $pontos_questao = $acertou ? 10 : 0;

            // Atualizar resposta no simulado
            $sql = "UPDATE simulados_questoes SET resposta_usuario = ?, correta = ? WHERE simulado_id = ? AND questao_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$resposta, $acertou, $simulado_id, $questao_id]);

            // Adicionar pontos
            $pontos_total += $pontos_questao;
            if ($acertou) $questoes_corretas++;

            // Registrar resposta individual (evitar duplicatas)
            $sql = "INSERT IGNORE INTO respostas_usuario (usuario_id, questao_id, resposta, correta, pontos_ganhos) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION["usuario_id"], $questao_id, $resposta, $acertou, $pontos_questao]);
        }
    }

    // Atualizar simulado
    $sql = "UPDATE simulados SET questoes_corretas = ?, pontuacao_final = ?, tempo_gasto = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$questoes_corretas, $pontos_total, $tempo_gasto, $simulado_id]);

    // Adicionar pontos pela conclusão do simulado
    $resultado_pontos = $gamificacao->adicionarPontos($_SESSION["usuario_id"], $pontos_total, 'simulado');
    
    // Debug: Log do resultado
    error_log("SIMULADO DEBUG - Pontos calculados: $pontos_total, Resultado adicionarPontos: " . ($resultado_pontos ? 'SUCESSO' : 'FALHA'));

    // Verificar conquista de simulado perfeito
    $sql = "SELECT questoes_total FROM simulados WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$simulado_id]);
    $total_questoes_simulado = $stmt->fetchColumn();

    if ($questoes_corretas > 0 && $questoes_corretas == $total_questoes_simulado) {
        $gamificacao->adicionarPontos($_SESSION["usuario_id"], 50, 'perfeicao');
    }

    $mensagem = "Simulado finalizado! Você acertou $questoes_corretas questões e ganhou $pontos_total pontos!";
}

// Obter simulados do usuário (excluindo os pré-definidos automáticos)
$sql = "SELECT * FROM simulados 
        WHERE usuario_id = ? 
        AND nome NOT IN ('Simulado Geral Básico', 'Simulado Português e Matemática', 'Simulado Conhecimentos Específicos', 'Simulado Raciocínio e Informática', 'Simulado Completo')
        ORDER BY data_criacao DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$simulados = $stmt->fetchAll();

// Obter disciplinas para filtro
$sql = "SELECT DISTINCT d.* FROM disciplinas d 
        JOIN questoes q ON d.id = q.disciplina_id 
        WHERE q.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"]]);
$disciplinas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCP - Sistema de Concursos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-clipboard-list"></i> Simulados</h1>
                <div class="user-info">
                    <a href="dashboard.php" class="action-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>
        </header>

        <?php if ($mensagem): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $mensagem ?>
            </div>
        <?php endif; ?>

        <!-- Criar Novo Simulado -->
        <section class="create-simulado">
            <div class="card">
                <h2><i class="fas fa-plus-circle"></i> Criar Novo Simulado</h2>
                <form method="POST">
                    <input type="hidden" name="criar_simulado" value="1">

                    <div class="form-group">
                        <label for="nome_simulado">Nome do Simulado:</label>
                        <input type="text" id="nome_simulado" name="nome_simulado"
                            placeholder="Ex: Simulado de Português" required>
                    </div>

                    <div class="form-group">
                        <label for="quantidade_questoes">Quantidade de Questões:</label>
                        <select id="quantidade_questoes" name="quantidade_questoes" required>
                            <option value="5">5 questões</option>
                            <option value="10">10 questões</option>
                            <option value="15">15 questões</option>
                            <option value="20">20 questões</option>
                            <option value="30">30 questões</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="disciplina_id">Disciplina (opcional):</label>
                        <select id="disciplina_id" name="disciplina_id">
                            <option value="">Todas as disciplinas</option>
                            <?php foreach ($disciplinas as $disciplina): ?>
                                <option value="<?= $disciplina['id'] ?>">
                                    <?= htmlspecialchars($disciplina['nome_disciplina']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-play"></i> Iniciar Simulado
                    </button>
                </form>
            </div>
        </section>

        <!-- Simulados Pré-definidos -->
        <section class="predefined-simulados">
            <div class="card">
                <h2><i class="fas fa-star"></i> Simulados Pré-definidos</h2>
                <p>Escolha um dos simulados criados especialmente para você:</p>

                <div class="predefined-grid">
                    <div class="predefined-card">
                        <div class="predefined-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h3>Simulado Geral Básico</h3>
                        <p>Todas as disciplinas em um simulado equilibrado</p>
                        <div class="predefined-stats">
                            <span><i class="fas fa-question-circle"></i> 15 questões</span>
                            <span><i class="fas fa-clock"></i> ~30 min</span>
                        </div>
                        <a href="simulado.php?predefined=geral" class="btn-primary">
                            <i class="fas fa-play"></i> Iniciar
                        </a>
                    </div>

                    <div class="predefined-card">
                        <div class="predefined-icon">
                            <i class="fas fa-language"></i>
                        </div>
                        <h3>Português e Matemática</h3>
                        <p>Foco nas disciplinas mais importantes</p>
                        <div class="predefined-stats">
                            <span><i class="fas fa-question-circle"></i> 12 questões</span>
                            <span><i class="fas fa-clock"></i> ~25 min</span>
                        </div>
                        <a href="simulado.php?predefined=portugues-matematica" class="btn-primary">
                            <i class="fas fa-play"></i> Iniciar
                        </a>
                    </div>

                    <div class="predefined-card">
                        <div class="predefined-icon">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <h3>Conhecimentos Específicos</h3>
                        <p>Direito, administração e atualidades</p>
                        <div class="predefined-stats">
                            <span><i class="fas fa-question-circle"></i> 10 questões</span>
                            <span><i class="fas fa-clock"></i> ~20 min</span>
                        </div>
                        <a href="simulado.php?predefined=especificos" class="btn-primary">
                            <i class="fas fa-play"></i> Iniciar
                        </a>
                    </div>

                    <div class="predefined-card">
                        <div class="predefined-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h3>Raciocínio e Informática</h3>
                        <p>Lógica e conhecimentos de informática</p>
                        <div class="predefined-stats">
                            <span><i class="fas fa-question-circle"></i> 10 questões</span>
                            <span><i class="fas fa-clock"></i> ~20 min</span>
                        </div>
                        <a href="simulado.php?predefined=logico-informatica" class="btn-primary">
                            <i class="fas fa-play"></i> Iniciar
                        </a>
                    </div>

                    <div class="predefined-card">
                        <div class="predefined-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3>Simulado Completo</h3>
                        <p>Todas as questões disponíveis</p>
                        <div class="predefined-stats">
                            <span><i class="fas fa-question-circle"></i> 30 questões</span>
                            <span><i class="fas fa-clock"></i> ~60 min</span>
                        </div>
                        <a href="simulado.php?predefined=completo" class="btn-primary">
                            <i class="fas fa-play"></i> Iniciar
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Simulados Pré-definidos Iniciados -->
        <?php
        // Verificar se o usuário já iniciou algum simulado pré-definido (apenas os que têm questões respondidas)
        $sql = "SELECT DISTINCT s.* FROM simulados s 
                JOIN simulados_questoes sq ON s.id = sq.simulado_id 
                JOIN respostas_usuario ru ON sq.questao_id = ru.questao_id 
                WHERE s.usuario_id = ? 
                AND ru.usuario_id = ?
                AND s.nome IN ('Simulado Geral Básico', 'Simulado Português e Matemática', 'Simulado Conhecimentos Específicos', 'Simulado Raciocínio e Informática', 'Simulado Completo')
                ORDER BY s.data_criacao DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION["usuario_id"], $_SESSION["usuario_id"]]);
        $simulados_predefinidos = $stmt->fetchAll();
        ?>

        <?php if (!empty($simulados_predefinidos)): ?>
            <section class="predefined-started">
                <div class="card">
                    <h2><i class="fas fa-play-circle"></i> Simulados Pré-definidos Iniciados</h2>
                    <p>Você já iniciou alguns dos simulados pré-definidos:</p>

                    <div class="simulados-grid">
                        <?php foreach ($simulados_predefinidos as $simulado): ?>
                            <div class="simulado-card">
                                <!-- Status Badge -->
                                <div class="simulado-status <?= $simulado['questoes_corretas'] === null ? 'status-pendente' : 'status-concluido' ?>">
                                    <?= $simulado['questoes_corretas'] === null ? 'Em Andamento' : 'Concluído' ?>
                                </div>

                                <div class="simulado-header">
                                    <h3><?= htmlspecialchars($simulado['nome']) ?></h3>
                                    <span class="simulado-date">
                                        <?= date('d/m/Y', strtotime($simulado['data_criacao'])) ?>
                                    </span>
                                </div>

                                <!-- Progress Bar para simulados em andamento -->
                                <?php if ($simulado['questoes_corretas'] === null): ?>
                                    <?php
                                    // Calcular progresso baseado nas questões respondidas
                                    $sql = "SELECT COUNT(*) as respondidas FROM simulados_questoes sq 
                                        JOIN respostas_usuario ru ON sq.questao_id = ru.questao_id 
                                        WHERE sq.simulado_id = ? AND ru.usuario_id = ?";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute([$simulado['id'], $_SESSION["usuario_id"]]);
                                    $progresso = $stmt->fetch();
                                    $percentual = $progresso['respondidas'] > 0 ? ($progresso['respondidas'] / $simulado['questoes_total']) * 100 : 0;
                                    ?>
                                    <div class="simulado-progress">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?= $percentual ?>%"></div>
                                        </div>
                                        <div class="progress-text">
                                            <?= $progresso['respondidas'] ?> de <?= $simulado['questoes_total'] ?> questões respondidas
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="simulado-stats">
                                    <div class="stat">
                                        <i class="fas fa-question-circle"></i>
                                        <span><?= $simulado['questoes_total'] ?> questões</span>
                                    </div>

                                    <?php if ($simulado['questoes_corretas'] !== null): ?>
                                        <div class="stat">
                                            <i class="fas fa-check-circle"></i>
                                            <span><?= $simulado['questoes_corretas'] ?> corretas</span>
                                        </div>

                                        <div class="stat">
                                            <i class="fas fa-percentage"></i>
                                            <span><?= round(($simulado['questoes_corretas'] / $simulado['questoes_total']) * 100, 1) ?>% acerto</span>
                                        </div>

                                        <div class="stat">
                                            <i class="fas fa-star"></i>
                                            <span><?= $simulado['pontuacao_final'] ?> pontos</span>
                                        </div>

                                        <?php if ($simulado['tempo_gasto']): ?>
                                            <div class="stat">
                                                <i class="fas fa-clock"></i>
                                                <span><?= $simulado['tempo_gasto'] ?> min</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="stat">
                                            <i class="fas fa-play-circle"></i>
                                            <span>Iniciar</span>
                                        </div>

                                        <div class="stat">
                                            <i class="fas fa-trophy"></i>
                                            <span>0 pontos</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="simulado-actions">
                                    <?php if ($simulado['questoes_corretas'] === null): ?>
                                        <a href="simulado.php?id=<?= $simulado['id'] ?>" class="btn-primary">
                                            <i class="fas fa-play"></i>
                                            <?= $progresso['respondidas'] > 0 ? 'Continuar' : 'Iniciar' ?>
                                        </a>
                                    <?php else: ?>
                                        <a href="simulado.php?id=<?= $simulado['id'] ?>&view=1" class="btn-secondary">
                                            <i class="fas fa-eye"></i> Ver Resultado
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <!-- Mensagem quando não há simulados pré-definidos iniciados -->
            <section class="predefined-started">
                <div class="card">
                    <h2><i class="fas fa-info-circle"></i> Simulados Pré-definidos</h2>
                    <p>Você ainda não iniciou nenhum simulado pré-definido. Escolha um dos simulados acima para começar!</p>
                </div>
            </section>
        <?php endif; ?>

        <!-- Simulados Anteriores -->
        <section class="simulados-history">
            <div class="card">
                <h2><i class="fas fa-history"></i> Seus Simulados Personalizados</h2>

                <?php if (empty($simulados)): ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>Nenhum simulado realizado ainda</h3>
                        <p>Crie seu primeiro simulado para começar a praticar!</p>
                    </div>
                <?php else: ?>
                    <div class="simulados-grid">
                        <?php foreach ($simulados as $simulado): ?>
                            <div class="simulado-card">
                                <!-- Status Badge -->
                                <div class="simulado-status <?= $simulado['questoes_corretas'] === null ? 'status-pendente' : 'status-concluido' ?>">
                                    <?= $simulado['questoes_corretas'] === null ? 'Em Andamento' : 'Concluído' ?>
                                </div>

                                <div class="simulado-header">
                                    <h3><?= htmlspecialchars($simulado['nome']) ?></h3>
                                    <span class="simulado-date">
                                        <?= date('d/m/Y', strtotime($simulado['data_criacao'])) ?>
                                    </span>
                                </div>

                                <!-- Progress Bar para simulados em andamento -->
                                <?php if ($simulado['questoes_corretas'] === null): ?>
                                    <?php
                                    // Calcular progresso baseado nas questões respondidas
                                    $sql = "SELECT COUNT(*) as respondidas FROM simulados_questoes sq 
                                            JOIN respostas_usuario ru ON sq.questao_id = ru.questao_id 
                                            WHERE sq.simulado_id = ? AND ru.usuario_id = ?";
                                    $stmt = $pdo->prepare($sql);
                                    $stmt->execute([$simulado['id'], $_SESSION["usuario_id"]]);
                                    $progresso = $stmt->fetch();
                                    $percentual = $progresso['respondidas'] > 0 ? ($progresso['respondidas'] / $simulado['questoes_total']) * 100 : 0;
                                    ?>
                                    <div class="simulado-progress">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?= $percentual ?>%"></div>
                                        </div>
                                        <div class="progress-text">
                                            <?= $progresso['respondidas'] ?> de <?= $simulado['questoes_total'] ?> questões respondidas
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="simulado-stats">
                                    <div class="stat">
                                        <i class="fas fa-question-circle"></i>
                                        <span><?= $simulado['questoes_total'] ?> questões</span>
                                    </div>

                                    <?php if ($simulado['questoes_corretas'] !== null): ?>
                                        <div class="stat">
                                            <i class="fas fa-check-circle"></i>
                                            <span><?= $simulado['questoes_corretas'] ?> corretas</span>
                                        </div>

                                        <div class="stat">
                                            <i class="fas fa-percentage"></i>
                                            <span><?= round(($simulado['questoes_corretas'] / $simulado['questoes_total']) * 100, 1) ?>% acerto</span>
                                        </div>

                                        <div class="stat">
                                            <i class="fas fa-star"></i>
                                            <span><?= $simulado['pontuacao_final'] ?> pontos</span>
                                        </div>

                                        <?php if ($simulado['tempo_gasto']): ?>
                                            <div class="stat">
                                                <i class="fas fa-clock"></i>
                                                <span><?= $simulado['tempo_gasto'] ?> min</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="stat">
                                            <i class="fas fa-play-circle"></i>
                                            <span>Iniciar</span>
                                        </div>

                                        <div class="stat">
                                            <i class="fas fa-trophy"></i>
                                            <span>0 pontos</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="simulado-actions">
                                    <?php if ($simulado['questoes_corretas'] === null): ?>
                                        <a href="simulado.php?id=<?= $simulado['id'] ?>" class="btn-primary">
                                            <i class="fas fa-play"></i>
                                            <?= $progresso['respondidas'] > 0 ? 'Continuar' : 'Iniciar' ?>
                                        </a>
                                    <?php else: ?>
                                        <a href="simulado.php?id=<?= $simulado['id'] ?>&view=1" class="btn-secondary">
                                            <i class="fas fa-eye"></i> Ver Resultado
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: white;
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
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 68, 68, 0.4);
        }

        .btn-secondary {
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

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ccc;
        }

        .simulados-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .simulado-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .simulado-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(45deg, #ff4444, #cc0000);
        }

        .simulado-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            background: rgba(255, 255, 255, 0.15);
        }

        .simulado-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            gap: 15px;
        }

        .simulado-header h3 {
            color: white;
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            flex: 1;
        }

        .simulado-date {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 4px 8px;
            border-radius: 6px;
            white-space: nowrap;
        }

        .simulado-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .stat i {
            color: #ff4444;
            font-size: 1.2rem;
        }

        .stat span {
            color: white;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .simulado-actions {
            text-align: center;
            margin-top: 20px;
        }

        .simulado-actions .btn-primary,
        .simulado-actions .btn-secondary {
            width: 100%;
            justify-content: center;
            padding: 12px 20px;
            font-size: 1rem;
        }

        /* Status badges */
        .simulado-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pendente {
            background: linear-gradient(45deg, #ffa726, #ff9800);
            color: white;
        }

        .status-concluido {
            background: linear-gradient(45deg, #4caf50, #2e7d32);
            color: white;
        }

        /* Progress bar para simulados em andamento */
        .simulado-progress {
            margin-bottom: 15px;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(45deg, #ff4444, #cc0000);
            transition: width 0.3s ease;
        }

        .progress-text {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 5px;
            text-align: center;
        }

        /* Simulados Pré-definidos */
        .predefined-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .predefined-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .predefined-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(45deg, #ff4444, #cc0000);
        }

        .predefined-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            background: rgba(255, 255, 255, 0.15);
        }

        .predefined-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #ff4444, #cc0000);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.5rem;
            color: white;
        }

        .predefined-card h3 {
            color: white;
            margin: 0 0 10px 0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .predefined-card p {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 20px;
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .predefined-stats {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .predefined-stats span {
            display: flex;
            align-items: center;
            gap: 5px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 4px 8px;
            border-radius: 6px;
        }

        .predefined-stats i {
            color: #ff4444;
        }

        .predefined-card .btn-primary {
            width: 100%;
            justify-content: center;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .simulados-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .predefined-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .simulado-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .simulado-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .predefined-stats {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</body>

</html>