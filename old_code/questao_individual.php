<?php
session_start();
require 'conexao.php';
require 'classes/Gamificacao.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$questao_id = $_GET['id'] ?? null;

if (!$questao_id) {
    echo "Questão não encontrada.";
    exit;
}

// Obter dados da questão
$sql = "SELECT q.*, d.nome_disciplina, e.nome_arquivo,
               (SELECT COUNT(*) FROM respostas_usuario r WHERE r.questao_id = q.id AND r.usuario_id = ?) as ja_respondida,
               (SELECT resposta FROM respostas_usuario r WHERE r.questao_id = q.id AND r.usuario_id = ? ORDER BY r.data_resposta DESC LIMIT 1) as ultima_resposta
        FROM questoes q 
        LEFT JOIN disciplinas d ON q.disciplina_id = d.id
        LEFT JOIN editais e ON q.edital_id = e.id
        WHERE q.id = ? AND q.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["usuario_id"], $_SESSION["usuario_id"], $questao_id, $_SESSION["usuario_id"]]);
$questao = $stmt->fetch();

if (!$questao) {
    echo "Questão não encontrada ou não pertence ao usuário.";
    exit;
}

$gamificacao = new Gamificacao($pdo);
$mensagem = "";

// Processar resposta
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['responder'])) {
    $resposta = strtoupper($_POST['resposta']);
    $resposta_correta = $questao['alternativa_correta'];
    
    // Normalizar respostas para comparação
    $resposta_normalizada = strtoupper(trim($resposta));
    $resposta_correta_normalizada = strtoupper(trim($resposta_correta));
    
    $acertou = ($resposta_normalizada == $resposta_correta_normalizada) ? 1 : 0;
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
    
    // Recarregar dados da questão
    $sql = "SELECT q.*, d.nome_disciplina, e.nome_arquivo,
                   (SELECT COUNT(*) FROM respostas_usuario r WHERE r.questao_id = q.id AND r.usuario_id = ?) as ja_respondida,
                   (SELECT resposta FROM respostas_usuario r WHERE r.questao_id = q.id AND r.usuario_id = ? ORDER BY r.data_resposta DESC LIMIT 1) as ultima_resposta
            FROM questoes q 
            LEFT JOIN disciplinas d ON q.disciplina_id = d.id
            LEFT JOIN editais e ON q.edital_id = e.id
            WHERE q.id = ? AND q.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION["usuario_id"], $_SESSION["usuario_id"], $questao_id, $_SESSION["usuario_id"]]);
    $questao = $stmt->fetch();
}
?>

<div class="questao-individual">
    <?php if ($mensagem): ?>
        <div class="alert <?= strpos($mensagem, 'Parabéns') !== false ? 'alert-success' : 'alert-danger' ?>">
            <i class="fas <?= strpos($mensagem, 'Parabéns') !== false ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
            <?= $mensagem ?>
        </div>
    <?php endif; ?>
    
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
        <p class="enunciado"><?= nl2br(htmlspecialchars($questao['enunciado'])) ?></p>
        
        <form method="POST" class="alternativas-form">
            <input type="hidden" name="responder" value="1">
            
            <div class="alternativas">
                <?php foreach (['a', 'b', 'c', 'd', 'e'] as $alt): ?>
                    <label class="alternativa <?= $questao['ultima_resposta'] == strtoupper($alt) ? 'selected' : '' ?>">
                        <input type="radio" 
                               name="resposta" 
                               value="<?= strtoupper($alt) ?>"
                               <?= $questao['ultima_resposta'] == strtoupper($alt) ? 'checked' : '' ?>
                               <?= $questao['ja_respondida'] ? 'disabled' : '' ?>>
                        <span class="alternativa-letter"><?= strtoupper($alt) ?>)</span>
                        <span class="alternativa-text"><?= htmlspecialchars($questao['alternativa_' . $alt]) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            
            <?php if (!$questao['ja_respondida']): ?>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-paper-plane"></i> Responder
                    </button>
                </div>
            <?php else: ?>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="fecharModal()">
                        <i class="fas fa-times"></i> Fechar
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<style>
    .questao-individual {
        max-width: 100%;
    }
    
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
    
    .alert-danger {
        background: linear-gradient(45deg, #ff4444, #cc0000);
        color: white;
    }
    
    .questao-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 255, 255, 0.3);
    }
    
    .questao-header h3 {
        color: white;
        margin: 0;
        font-size: 1.3rem;
    }
    
    .questao-meta {
        display: flex;
        gap: 10px;
    }
    
    .disciplina-tag {
        background: linear-gradient(45deg, #ff4444, #cc0000);
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .respondida-tag {
        background: #ff4444;
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
        margin-bottom: 25px;
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
    
    .alternativa input[type="radio"] {
        width: auto;
        margin: 0;
    }
    
    .alternativa input[type="radio"]:disabled {
        cursor: not-allowed;
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
    
    .form-actions {
        text-align: center;
        padding-top: 20px;
        border-top: 2px solid rgba(255, 255, 255, 0.2);
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
        box-shadow: 0 4px 15px rgba(255, 68, 68, 0.3);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(255, 68, 68, 0.4);
        background: linear-gradient(45deg, #cc0000, #990000);
    }
    
    .btn-secondary {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        padding: 15px 30px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
        backdrop-filter: blur(10px);
    }
    
    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: #ff4444;
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(255, 68, 68, 0.2);
    }
    
    @media (max-width: 768px) {
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
