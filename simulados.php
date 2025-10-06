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
            
            $acertou = ($resposta == $resposta_correta) ? 1 : 0;
            $pontos_questao = $acertou ? 10 : 0;
            
            // Atualizar resposta no simulado
            $sql = "UPDATE simulados_questoes SET resposta_usuario = ?, correta = ? WHERE simulado_id = ? AND questao_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$resposta, $acertou, $simulado_id, $questao_id]);
            
            // Adicionar pontos
            $pontos_total += $pontos_questao;
            if ($acertou) $questoes_corretas++;
            
            // Registrar resposta individual
            $sql = "INSERT INTO respostas_usuario (usuario_id, questao_id, resposta, correta, pontos_ganhos) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION["usuario_id"], $questao_id, $resposta, $acertou, $pontos_questao]);
        }
    }
    
    // Atualizar simulado
    $sql = "UPDATE simulados SET questoes_corretas = ?, pontuacao_final = ?, tempo_gasto = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$questoes_corretas, $pontos_total, $tempo_gasto, $simulado_id]);
    
    // Adicionar pontos pela conclusão do simulado
    $gamificacao->adicionarPontos($_SESSION["usuario_id"], $pontos_total, 'simulado');
    
    // Verificar conquista de simulado perfeito
    if ($questoes_corretas == $pontos_total / 10) {
        $gamificacao->adicionarPontos($_SESSION["usuario_id"], 50, 'perfeicao');
    }
    
    $mensagem = "Simulado finalizado! Você acertou $questoes_corretas questões e ganhou $pontos_total pontos!";
}

// Obter simulados do usuário
$sql = "SELECT * FROM simulados WHERE usuario_id = ? ORDER BY data_criacao DESC";
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
    <title>Simulados - Sistema de Concursos</title>
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

        <!-- Simulados Anteriores -->
        <section class="simulados-history">
            <div class="card">
                <h2><i class="fas fa-history"></i> Seus Simulados</h2>
                
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
                                <div class="simulado-header">
                                    <h3><?= htmlspecialchars($simulado['nome']) ?></h3>
                                    <span class="simulado-date">
                                        <?= date('d/m/Y', strtotime($simulado['data_criacao'])) ?>
                                    </span>
                                </div>
                                
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
                                            <i class="fas fa-star"></i>
                                            <span><?= $simulado['pontuacao_final'] ?> pontos</span>
                                        </div>
                                        
                                        <?php if ($simulado['tempo_gasto']): ?>
                                            <div class="stat">
                                                <i class="fas fa-clock"></i>
                                                <span><?= $simulado['tempo_gasto'] ?> min</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="simulado-actions">
                                    <?php if ($simulado['questoes_corretas'] === null): ?>
                                        <a href="simulado.php?id=<?= $simulado['id'] ?>" class="btn-primary">
                                            <i class="fas fa-play"></i> Continuar
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
            background: linear-gradient(45deg, #43e97b, #38f9d7);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
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
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
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
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .simulado-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .simulado-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .simulado-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .simulado-header h3 {
            color: #2c3e50;
            margin: 0;
        }
        
        .simulado-date {
            color: #666;
            font-size: 0.9rem;
        }
        
        .simulado-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .stat {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .stat i {
            color: #667eea;
        }
        
        .simulado-actions {
            text-align: center;
        }
    </style>
</body>
</html>