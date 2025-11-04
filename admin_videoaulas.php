<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];

// Processar ações
$mensagem = '';
$tipo_mensagem = '';

if ($_POST['action'] ?? '' === 'adicionar_videoaula') {
    $categoria_id = $_POST['categoria_id'] ?? 0;
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $url_video = trim($_POST['url_video'] ?? '');
    $duracao = (int)($_POST['duracao'] ?? 0);
    $ordem = (int)($_POST['ordem'] ?? 0);
    
    if ($categoria_id && $titulo && $url_video) {
        // Converter URL do YouTube para formato embed se necessário
        $url_video = converterUrlYoutube($url_video);
        
        $sql = "INSERT INTO videoaulas (categoria_id, titulo, descricao, url_video, duracao, ordem, ativo) 
                VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$categoria_id, $titulo, $descricao, $url_video, $duracao, $ordem])) {
            $mensagem = 'Videoaula adicionada com sucesso!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Erro ao adicionar videoaula.';
            $tipo_mensagem = 'error';
        }
    } else {
        $mensagem = 'Preencha todos os campos obrigatórios.';
        $tipo_mensagem = 'error';
    }
}

if ($_POST['action'] ?? '' === 'editar_videoaula') {
    $videoaula_id = $_POST['videoaula_id'] ?? 0;
    $categoria_id = $_POST['categoria_id'] ?? 0;
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $url_video = trim($_POST['url_video'] ?? '');
    $duracao = (int)($_POST['duracao'] ?? 0);
    $ordem = (int)($_POST['ordem'] ?? 0);
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    if ($videoaula_id && $categoria_id && $titulo && $url_video) {
        // Converter URL do YouTube para formato embed se necessário
        $url_video = converterUrlYoutube($url_video);
        
        $sql = "UPDATE videoaulas SET categoria_id = ?, titulo = ?, descricao = ?, url_video = ?, duracao = ?, ordem = ?, ativo = ? 
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$categoria_id, $titulo, $descricao, $url_video, $duracao, $ordem, $ativo, $videoaula_id])) {
            $mensagem = 'Videoaula atualizada com sucesso!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Erro ao atualizar videoaula.';
            $tipo_mensagem = 'error';
        }
    }
}

if ($_POST['action'] ?? '' === 'excluir_videoaula') {
    $videoaula_id = $_POST['videoaula_id'] ?? 0;
    if ($videoaula_id) {
        $sql = "DELETE FROM videoaulas WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$videoaula_id])) {
            $mensagem = 'Videoaula excluída com sucesso!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Erro ao excluir videoaula.';
            $tipo_mensagem = 'error';
        }
    }
}

// Função para converter URL do YouTube para formato embed
function converterUrlYoutube($url) {
    // Se já está em formato embed, retorna como está
    if (strpos($url, 'youtube.com/embed') !== false || strpos($url, 'youtu.be') !== false) {
        return $url;
    }
    
    // Extrair ID do vídeo de diferentes formatos
    $patterns = [
        '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/',
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            $video_id = $matches[1];
            return "https://www.youtube.com/embed/" . $video_id;
        }
    }
    
    // Se não conseguir extrair, retorna a URL original
    return $url;
}

// Obter categorias
$sql_categorias = "SELECT * FROM videoaulas_categorias WHERE ativo = 1 ORDER BY ordem, nome";
$categorias = $pdo->query($sql_categorias)->fetchAll();

// Obter videoaulas
$videoaula_id_editar = $_GET['editar'] ?? 0;
$videoaula_editar = null;

if ($videoaula_id_editar) {
    $sql = "SELECT * FROM videoaulas WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$videoaula_id_editar]);
    $videoaula_editar = $stmt->fetch();
}

$sql_videoaulas = "SELECT 
                    v.*,
                    vc.nome as categoria_nome,
                    vc.cor as categoria_cor
                FROM videoaulas v
                JOIN videoaulas_categorias vc ON v.categoria_id = vc.id
                ORDER BY vc.ordem, v.ordem, v.titulo";
$videoaulas = $pdo->query($sql_videoaulas)->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Videoaulas - Sistema de Concursos</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            margin: 0;
            color: #2c3e50;
        }
        
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .form-section h2 {
            margin: 0 0 20px 0;
            color: #2c3e50;
            font-size: 1.5em;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .videoaulas-list {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .videoaulas-list h2 {
            margin: 0 0 20px 0;
            color: #2c3e50;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
            font-weight: 600;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .help-text {
            font-size: 0.85em;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .youtube-preview {
            margin-top: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #dc3545;
        }
        
        .youtube-preview h4 {
            margin: 0 0 10px 0;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-cog"></i> Gerenciar Videoaulas</h1>
                <div class="user-info">
                    <a href="videoaulas.php" class="action-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>
        </header>

        <div class="admin-container">
            <?php if ($mensagem): ?>
                <div class="message <?= $tipo_mensagem ?>">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>

            <!-- Formulário de Adicionar/Editar Videoaula -->
            <div class="form-section">
                <h2>
                    <i class="fas fa-<?= $videoaula_editar ? 'edit' : 'plus-circle' ?>"></i>
                    <?= $videoaula_editar ? 'Editar Videoaula' : 'Adicionar Nova Videoaula' ?>
                </h2>
                
                <form method="POST" id="formVideoaula">
                    <input type="hidden" name="action" value="<?= $videoaula_editar ? 'editar_videoaula' : 'adicionar_videoaula' ?>">
                    <?php if ($videoaula_editar): ?>
                        <input type="hidden" name="videoaula_id" value="<?= $videoaula_editar['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="categoria_id">Categoria/Disciplina *</label>
                            <select name="categoria_id" id="categoria_id" required>
                                <option value="">Selecione uma categoria</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" 
                                        <?= ($videoaula_editar && $videoaula_editar['categoria_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="titulo">Título da Videoaula *</label>
                            <input type="text" name="titulo" id="titulo" 
                                value="<?= htmlspecialchars($videoaula_editar['titulo'] ?? '') ?>" 
                                placeholder="Ex: Português - Análise Sintática" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="url_video">URL do YouTube *</label>
                            <input type="text" name="url_video" id="url_video" 
                                value="<?= htmlspecialchars($videoaula_editar['url_video'] ?? '') ?>" 
                                placeholder="https://www.youtube.com/watch?v=..." required>
                            <div class="help-text">
                                Cole a URL completa do YouTube. Formatos aceitos: youtube.com/watch?v=..., youtu.be/..., youtube.com/embed/...
                            </div>
                            <div id="youtubePreview" class="youtube-preview" style="display: none;">
                                <h4><i class="fab fa-youtube"></i> Preview do Vídeo</h4>
                                <div id="previewFrame"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="duracao">Duração (minutos)</label>
                            <input type="number" name="duracao" id="duracao" 
                                value="<?= $videoaula_editar['duracao'] ?? 0 ?>" 
                                min="0" placeholder="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="ordem">Ordem</label>
                            <input type="number" name="ordem" id="ordem" 
                                value="<?= $videoaula_editar['ordem'] ?? 0 ?>" 
                                min="0" placeholder="0">
                            <div class="help-text">Ordem de exibição (menor número aparece primeiro)</div>
                        </div>
                        
                        <?php if ($videoaula_editar): ?>
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" name="ativo" value="1" 
                                    <?= $videoaula_editar['ativo'] ? 'checked' : '' ?>>
                                <span>Ativo</span>
                            </label>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group full-width">
                            <label for="descricao">Descrição</label>
                            <textarea name="descricao" id="descricao" 
                                placeholder="Descrição da videoaula..."><?= htmlspecialchars($videoaula_editar['descricao'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?= $videoaula_editar ? 'save' : 'plus' ?>"></i>
                            <?= $videoaula_editar ? 'Salvar Alterações' : 'Adicionar Videoaula' ?>
                        </button>
                        <?php if ($videoaula_editar): ?>
                            <a href="admin_videoaulas.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Lista de Videoaulas -->
            <div class="videoaulas-list">
                <h2><i class="fas fa-list"></i> Videoaulas Cadastradas</h2>
                
                <?php if (empty($videoaulas)): ?>
                    <p style="text-align: center; padding: 40px; color: #6c757d;">
                        <i class="fas fa-video" style="font-size: 3em; opacity: 0.3; margin-bottom: 15px; display: block;"></i>
                        Nenhuma videoaula cadastrada. Adicione sua primeira videoaula acima.
                    </p>
                <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th>Título</th>
                                    <th>Duração</th>
                                    <th>Ordem</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($videoaulas as $v): ?>
                                    <tr>
                                        <td>
                                            <span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: <?= $v['categoria_cor'] ?>; margin-right: 8px;"></span>
                                            <?= htmlspecialchars($v['categoria_nome']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($v['titulo']) ?></td>
                                        <td><?= $v['duracao'] ?> min</td>
                                        <td><?= $v['ordem'] ?></td>
                                        <td>
                                            <span class="badge <?= $v['ativo'] ? 'badge-active' : 'badge-inactive' ?>">
                                                <?= $v['ativo'] ? 'Ativo' : 'Inativo' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <a href="admin_videoaulas.php?editar=<?= $v['id'] ?>" class="btn btn-success" style="padding: 5px 10px; font-size: 0.85em;">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir esta videoaula?');">
                                                    <input type="hidden" name="action" value="excluir_videoaula">
                                                    <input type="hidden" name="videoaula_id" value="<?= $v['id'] ?>">
                                                    <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.85em;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Preview do YouTube
        document.getElementById('url_video')?.addEventListener('input', function() {
            const url = this.value;
            const preview = document.getElementById('youtubePreview');
            const frame = document.getElementById('previewFrame');
            
            if (!url) {
                preview.style.display = 'none';
                return;
            }
            
            // Extrair ID do vídeo
            let videoId = null;
            const patterns = [
                /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
            ];
            
            for (const pattern of patterns) {
                const match = url.match(pattern);
                if (match) {
                    videoId = match[1];
                    break;
                }
            }
            
            if (videoId) {
                frame.innerHTML = `<iframe width="100%" height="200" src="https://www.youtube.com/embed/${videoId}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });
        
        // Scroll para o formulário ao editar
        <?php if ($videoaula_editar): ?>
            document.querySelector('.form-section').scrollIntoView({ behavior: 'smooth' });
        <?php endif; ?>
    </script>
</body>
</html>

