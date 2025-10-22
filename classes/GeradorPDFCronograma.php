<?php
require_once 'conexao.php';
require_once 'GeradorCronograma.php';

class GeradorPDFCronograma {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Gera PDF do cronograma
     */
    public function gerarPDF($cronograma_id, $usuario_id) {
        // Obter dados do cronograma
        $gerador = new GeradorCronograma($this->pdo);
        $dados_cronograma = $gerador->gerarDadosPDF($cronograma_id);
        $estatisticas = $gerador->obterEstatisticasCronograma($cronograma_id);
        
        // Obter dados do usuário
        $sql = "SELECT nome FROM usuarios WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        // Obter dados do edital
        $sql = "SELECT e.nome_arquivo, c.data_inicio, c.data_fim, c.horas_por_dia
                FROM cronogramas c
                JOIN editais e ON c.edital_id = e.id
                WHERE c.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$cronograma_id]);
        $cronograma_info = $stmt->fetch();
        
        // Gerar HTML do PDF
        $html = $this->gerarHTML($dados_cronograma, $estatisticas, $usuario, $cronograma_info);
        
        // Salvar arquivo temporário
        $filename = "cronograma_" . $cronograma_id . "_" . date('Y-m-d_H-i-s') . ".html";
        $filepath = "uploads/" . $filename;
        
        file_put_contents($filepath, $html);
        
        return [
            'sucesso' => true,
            'arquivo' => $filename,
            'caminho' => $filepath
        ];
    }
    
    /**
     * Gera HTML formatado para o cronograma
     */
    private function gerarHTML($dados_cronograma, $estatisticas, $usuario, $cronograma_info) {
        $html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cronograma de Estudos</title>
    <style>
        @page {
            size: A4;
            margin: 2cm;
        }
        
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #000 0%, #cc0000 100%);
            color: white;
            border-radius: 10px;
            border: 3px solid #cc0000;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 16px;
            opacity: 0.9;
        }
        
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px solid #cc0000;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-item h3 {
            margin: 0;
            color: #cc0000;
            font-size: 24px;
            font-weight: bold;
        }
        
        .stat-item p {
            margin: 5px 0 0 0;
            color: #000;
            font-size: 14px;
            font-weight: 500;
        }
        
        .week-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .week-header {
            background: #cc0000;
            color: white;
            padding: 10px 15px;
            border-radius: 5px 5px 0 0;
            font-weight: bold;
            font-size: 16px;
            border: 2px solid #000;
        }
        
        .day-card {
            border: 2px solid #000;
            border-top: none;
            margin-bottom: 0;
        }
        
        .day-header {
            background: #000;
            color: white;
            padding: 12px 15px;
            border-bottom: 2px solid #cc0000;
            font-weight: bold;
            color: white;
        }
        
        .day-content {
            padding: 15px;
        }
        
        .activity {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #cc0000;
        }
        
        .activity:last-child {
            border-bottom: none;
        }
        
        .activity-subject {
            font-weight: 600;
            color: #000;
            font-size: 14px;
        }
        
        .activity-time {
            background: #cc0000;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #000;
        }
        
        .activity-description {
            font-size: 12px;
            color: #000;
            margin-top: 4px;
            font-weight: 500;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            padding: 20px;
            background: #000;
            color: white;
            border-radius: 8px;
            font-size: 12px;
            border: 2px solid #cc0000;
        }
        
        .footer p {
            color: white;
            margin: 5px 0;
        }
        
        /* Estilos adicionais para tema preto e vermelho */
        .header h1 {
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .week-header {
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        
        .day-header {
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }
        
        .activity-time {
            box-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .stats {
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .day-card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .footer {
            box-shadow: 0 -4px 8px rgba(0,0,0,0.2);
        }
        
        @media print {
            body {
                font-size: 12px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .week-section {
                page-break-inside: avoid;
            }
            
            /* Garantir que as cores sejam mantidas na impressão */
            .header, .week-header, .day-header, .activity-time, .footer {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 Cronograma de Estudos</h1>
        <p><strong>Estudante:</strong> ' . htmlspecialchars($usuario['nome']) . '</p>
        <p><strong>Edital:</strong> ' . htmlspecialchars($cronograma_info['nome_arquivo']) . '</p>
        <p><strong>Período:</strong> ' . date('d/m/Y', strtotime($cronograma_info['data_inicio'])) . ' a ' . date('d/m/Y', strtotime($cronograma_info['data_fim'])) . '</p>
    </div>
    
    <div class="stats">
        <div class="stat-item">
            <h3>' . ($estatisticas['total_dias'] ?? 0) . '</h3>
            <p>Dias de Estudo</p>
        </div>
        <div class="stat-item">
            <h3>' . ($estatisticas['total_horas'] ?? 0) . 'h</h3>
            <p>Horas Totais</p>
        </div>
        <div class="stat-item">
            <h3>' . ($estatisticas['total_disciplinas'] ?? 0) . '</h3>
            <p>Disciplinas</p>
        </div>
        <div class="stat-item">
            <h3>' . round($estatisticas['media_horas_dia'] ?? 0, 1) . 'h</h3>
            <p>Média/Dia</p>
        </div>
    </div>';
        
        // Agrupar por semanas
        $semanas = $this->agruparPorSemanas($dados_cronograma);
        
        foreach ($semanas as $semana_num => $dias_semana) {
            $html .= '<div class="week-section">
                <div class="week-header">
                    📅 Semana ' . $semana_num . ' - ' . date('d/m', strtotime($dias_semana[0]['data'])) . ' a ' . date('d/m', strtotime(end($dias_semana)['data'])) . '
                </div>';
            
            foreach ($dias_semana as $dia) {
                $html .= '<div class="day-card">
                    <div class="day-header">
                        ' . $dia['dia_semana'] . ' - ' . $dia['data_formatada'] . '
                    </div>
                    <div class="day-content">';
                
                if (empty($dia['atividades'])) {
                    $html .= '<p style="color: #999; font-style: italic;">Dia de descanso</p>';
                } else {
                    foreach ($dia['atividades'] as $atividade) {
                        $html .= '<div class="activity">
                            <div>
                                <div class="activity-subject">📖 ' . htmlspecialchars($atividade['nome_disciplina']) . '</div>
                                <div class="activity-description">Estudo focado e exercícios práticos</div>
                            </div>
                            <div class="activity-time">' . $atividade['horas_previstas'] . 'h</div>
                        </div>';
                    }
                }
                
                $html .= '</div>
                </div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '<div class="footer">
            <p>📝 <strong>Dicas para o sucesso:</strong></p>
            <p>• Mantenha a consistência nos horários de estudo</p>
            <p>• Faça pausas de 10-15 minutos a cada hora de estudo</p>
            <p>• Revise o conteúdo estudado no dia anterior</p>
            <p>• Pratique com questões e simulados regularmente</p>
            <p>• Mantenha-se hidratado e bem alimentado</p>
            <br>
            <p><strong>Gerado em:</strong> ' . date('d/m/Y H:i') . ' | <strong>Sistema de Concursos</strong></p>
        </div>
    </body>
</html>';
        
        return $html;
    }
    
    /**
     * Agrupa os dias por semanas
     */
    private function agruparPorSemanas($dados_cronograma) {
        $semanas = [];
        $semana_atual = 1;
        $primeiro_dia = true;
        
        foreach ($dados_cronograma as $dia) {
            $dia_semana = date('N', strtotime($dia['data']));
            
            // Se é segunda-feira e não é o primeiro dia, incrementa a semana
            if ($dia_semana == 1 && !$primeiro_dia) {
                $semana_atual++;
            }
            
            $primeiro_dia = false;
            
            if (!isset($semanas[$semana_atual])) {
                $semanas[$semana_atual] = [];
            }
            
            $semanas[$semana_atual][] = $dia;
        }
        
        return $semanas;
    }
}
?>
