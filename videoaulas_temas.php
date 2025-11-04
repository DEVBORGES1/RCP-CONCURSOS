<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["usuario_id"];
$categoria_id = $_GET['categoria_id'] ?? 0;

// Obter dados da categoria
$sql = "SELECT * FROM videoaulas_categorias WHERE id = ? AND ativo = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$categoria_id]);
$categoria = $stmt->fetch();

if (!$categoria) {
    header("Location: videoaulas.php");
    exit;
}

// Definir temas padrão para cada matéria
$temas_padrao = [
    'Português' => [
        'Gramática',
        'Interpretação de Texto',
        'Redação',
        'Ortografia',
        'Pontuação',
        'Morfolgia',
        'Sintaxe'
    ],
    'Matemática' => [
        'Álgebra',
        'Geometria',
        'Trigonometria',
        'Estatística',
        'Aritmética',
        'Funções',
        'Geometria Analítica'
    ],
    'Raciocínio Lógico' => [
        'Lógica Proposicional',
        'Lógica de Argumentação',
        'Análise Combinatória',
        'Probabilidade',
        'Raciocínio Sequencial'
    ],
    'Direito Constitucional' => [
        'Constituição Federal',
        'Direitos Fundamentais',
        'Organização dos Poderes',
        'Federalismo',
        'Processo Legislativo'
    ],
    'Direito Administrativo' => [
        'Administração Pública',
        'Ato Administrativo',
        'Licitações',
        'Serviços Públicos',
        'Poderes Administrativos'
    ],
    'Direito Penal' => [
        'Teoria do Crime',
        'Tipos Penais',
        'Processo Penal',
        'Direito Penal Especial'
    ],
    'Direito Civil' => [
        'Direito das Obrigações',
        'Direito das Coisas',
        'Direito de Família',
        'Direito das Sucessões',
        'Processo Civil'
    ],
    'Direito do Trabalho' => [
        'Contrato de Trabalho',
        'Jornada de Trabalho',
        'Salário',
        'Direito Coletivo',
        'Processo do Trabalho'
    ],
    'Direito Tributário' => [
        'Teoria Geral dos Tributos',
        'Impostos',
        'Obrigação Tributária',
        'Processo Tributário'
    ],
    'Informática' => [
        'Sistemas Operacionais',
        'Office',
        'Internet',
        'Redes de Computadores',
        'Segurança da Informação'
    ],
    'Atualidades' => [
        'Política Nacional',
        'Política Internacional',
        'Economia',
        'Meio Ambiente',
        'Tecnologia'
    ],
    'Administração Pública' => [
        'Gestão Pública',
        'Políticas Públicas',
        'Planejamento',
        'Controle e Avaliação'
    ],
    'Legislação Específica' => [
        'Leis Específicas',
        'Decretos',
        'Regulamentações'
    ],
    'Noções de Gestão' => [
        'Planejamento Estratégico',
        'Gestão de Pessoas',
        'Gestão Financeira',
        'Gestão de Qualidade'
    ],
    'Ética' => [
        'Ética no Serviço Público',
        'Código de Ética',
        'Probidade Administrativa'
    ]
];

// Obter todas as videoaulas da categoria
$sql = "SELECT 
            v.*,
            vp.tempo_assistido,
            vp.concluida,
            CASE 
                WHEN vp.concluida = 1 THEN 100
                WHEN vp.tempo_assistido > 0 AND v.duracao > 0 THEN 
                    ROUND((vp.tempo_assistido / (v.duracao * 60)) * 100, 1)
                ELSE 0 
            END as progresso_percentual
        FROM videoaulas v
        LEFT JOIN videoaulas_progresso vp ON v.id = vp.videoaula_id AND vp.usuario_id = ?
        WHERE v.categoria_id = ? AND v.ativo = 1
        ORDER BY v.ordem, v.titulo";

$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_id, $categoria_id]);
$todas_videoaulas = $stmt->fetchAll();

// Agrupar videoaulas por tema/disciplina (extrair do título)
// Formato esperado: "Tema - Título da Videoaula" ou "Tema: Título"
$temas = [];

// Se não houver videoaulas cadastradas, criar temas padrão baseados na matéria
if (empty($todas_videoaulas) && isset($temas_padrao[$categoria['nome']])) {
    foreach ($temas_padrao[$categoria['nome']] as $tema_nome) {
        $temas[$tema_nome] = [
            'nome' => $tema_nome,
            'videoaulas' => [],
            'total' => 0,
            'concluidas' => 0,
            'progresso' => 0
        ];
    }
} else {
    // Agrupar videoaulas existentes por tema
    foreach ($todas_videoaulas as $videoaula) {
        $titulo = $videoaula['titulo'];
        
        // Tentar extrair tema do título (formato: "Tema - " ou "Tema: ")
        $tema = $titulo;
        if (strpos($titulo, ' - ') !== false) {
            $partes = explode(' - ', $titulo, 2);
            $tema = trim($partes[0]);
            $titulo_videoaula = trim($partes[1]);
        } elseif (strpos($titulo, ': ') !== false) {
            $partes = explode(': ', $titulo, 2);
            $tema = trim($partes[0]);
            $titulo_videoaula = trim($partes[1]);
        } else {
            // Se não tiver separador, usar o título completo como tema (uma videoaula por tema)
            $tema = $titulo;
            $titulo_videoaula = $titulo;
        }
        
        if (!isset($temas[$tema])) {
            $temas[$tema] = [
                'nome' => $tema,
                'videoaulas' => [],
                'total' => 0,
                'concluidas' => 0,
                'progresso' => 0
            ];
        }
        
        $temas[$tema]['videoaulas'][] = $videoaula;
        $temas[$tema]['total']++;
        if ($videoaula['concluida']) {
            $temas[$tema]['concluidas']++;
        }
    }
    
    // Adicionar temas padrão que ainda não foram criados (sem videoaulas)
    if (isset($temas_padrao[$categoria['nome']])) {
        foreach ($temas_padrao[$categoria['nome']] as $tema_padrao) {
            if (!isset($temas[$tema_padrao])) {
                $temas[$tema_padrao] = [
                    'nome' => $tema_padrao,
                    'videoaulas' => [],
                    'total' => 0,
                    'concluidas' => 0,
                    'progresso' => 0
                ];
            }
        }
    }
}

// Calcular progresso de cada tema
foreach ($temas as &$tema) {
    if ($tema['total'] > 0) {
        $tema['progresso'] = round(($tema['concluidas'] / $tema['total']) * 100, 1);
    }
}

// Ordenar temas por progresso (menor primeiro) e depois por nome
usort($temas, function($a, $b) {
    if ($a['progresso'] != $b['progresso']) {
        return $a['progresso'] <=> $b['progresso'];
    }
    return strcmp($a['nome'], $b['nome']);
});

// Se só houver um tema com uma videoaula, redirecionar diretamente para ela
if (count($temas) == 1 && !empty($temas[0]['videoaulas']) && count($temas[0]['videoaulas']) == 1) {
    header("Location: videoaula_individual.php?id=" . $temas[0]['videoaulas'][0]['id']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($categoria['nome']) ?> - Disciplinas</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .temas-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .categoria-header {
            background: linear-gradient(135deg, <?= $categoria['cor'] ?> 0%, <?= $categoria['cor'] ?>dd 100%);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .categoria-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .categoria-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.2em;
        }
        
        .temas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .tema-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .tema-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            border-color: <?= $categoria['cor'] ?>;
        }
        
        .tema-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: <?= $categoria['cor'] ?>;
        }
        
        .tema-card.completo {
            border-color: #27ae60;
            background: linear-gradient(135deg, #ffffff 0%, #f0f9f4 100%);
        }
        
        .badge-completo {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #27ae60;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.75em;
            font-weight: 600;
        }
        
        .tema-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .tema-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: <?= $categoria['cor'] ?>;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3em;
            margin-right: 15px;
        }
        
        .tema-info h3 {
            margin: 0;
            color: #2c3e50;
            font-size: 1.3em;
            font-weight: bold;
        }
        
        .tema-info p {
            margin: 5px 0 0 0;
            color: #7f8c8d;
            font-size: 0.9em;
        }
        
        .tema-progress {
            margin: 15px 0;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .progress-label {
            font-size: 0.9em;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .progress-percentage {
            font-weight: bold;
            color: <?= $categoria['cor'] ?>;
            font-size: 1em;
        }
        
        .progress-bar {
            width: 100%;
            height: 10px;
            background: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, <?= $categoria['cor'] ?>, <?= $categoria['cor'] ?>88);
            border-radius: 10px;
            transition: width 0.8s ease;
        }
        
        .tema-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ecf0f1;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-item h4 {
            margin: 0;
            color: <?= $categoria['cor'] ?>;
            font-size: 1.3em;
            font-weight: bold;
        }
        
        .stat-item p {
            margin: 3px 0 0 0;
            color: #7f8c8d;
            font-size: 0.8em;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .temas-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1><i class="<?= $categoria['icone'] ?>"></i> <?= htmlspecialchars($categoria['nome']) ?></h1>
                <div class="user-info">
                    <a href="videoaulas.php" class="action-btn">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>
        </header>

        <div class="temas-container">
            <div class="categoria-header">
                <h1>
                    <i class="<?= $categoria['icone'] ?>"></i>
                    <?= htmlspecialchars($categoria['nome']) ?>
                </h1>
                <p><?= htmlspecialchars($categoria['descricao']) ?></p>
            </div>

            <?php if (empty($temas)): ?>
                <div class="empty-state">
                    <i class="fas fa-book"></i>
                    <h3>Nenhuma disciplina encontrada</h3>
                    <p>Ainda não há videoaulas cadastradas para esta matéria.</p>
                </div>
            <?php else: ?>
                <div class="temas-grid">
                    <?php foreach ($temas as $tema): ?>
                        <?php 
                        // Se só tem uma videoaula, vai direto para ela
                        // Se tem várias ou nenhuma, precisa de uma página intermediária ou lista
                        $tem_videoaulas = count($tema['videoaulas']) > 0;
                        $tem_varias = count($tema['videoaulas']) > 1;
                        $primeira_videoaula_id = $tem_videoaulas ? $tema['videoaulas'][0]['id'] : null;
                        
                        // Determinar URL de destino
                        if ($tem_videoaulas && !$tem_varias) {
                            // Só tem uma videoaula, vai direto para ela
                            $url_destino = "videoaula_individual.php?id=" . $primeira_videoaula_id;
                        } else {
                            // Tem várias ou nenhuma, vai para lista
                            $url_destino = "videoaulas_disciplina.php?categoria_id=$categoria_id&tema=" . urlencode($tema['nome']);
                        }
                        ?>
                        <div class="tema-card <?= $tema['progresso'] == 100 ? 'completo' : '' ?>" 
                             onclick="window.location.href='<?= $url_destino ?>'">
                            
                            <?php if ($tema['progresso'] == 100): ?>
                                <div class="badge-completo">
                                    <i class="fas fa-check-circle"></i> Completo
                                </div>
                            <?php endif; ?>
                            
                            <div class="tema-header">
                                <div class="tema-icon">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div class="tema-info">
                                    <h3><?= htmlspecialchars($tema['nome']) ?></h3>
                                    <p><?= count($tema['videoaulas']) > 0 ? count($tema['videoaulas']) . ' videoaula(s)' : 'Aguardando videoaulas' ?></p>
                                </div>
                            </div>
                            
                            <div class="tema-progress">
                                <div class="progress-header">
                                    <span class="progress-label">Progresso</span>
                                    <span class="progress-percentage"><?= $tema['progresso'] ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $tema['progresso'] ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="tema-stats">
                                <div class="stat-item">
                                    <h4><?= $tema['total'] ?></h4>
                                    <p>Total</p>
                                </div>
                                <div class="stat-item">
                                    <h4><?= $tema['concluidas'] ?></h4>
                                    <p>Concluídas</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

