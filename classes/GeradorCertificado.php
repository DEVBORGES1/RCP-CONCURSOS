<?php
require_once 'Database.php';

/**
 * Classe GeradorCertificado - Gera certificados PDF em HTML
 * 
 * @package RCP-CONCURSOS
 * @author Sistema RCP
 * @version 2.0
 */
class GeradorCertificado {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    /**
     * Gera certificado em HTML (para impressão/PDF)
     * 
     * @param int $usuario_id ID do usuário
     * @param int $categoria_id ID da categoria/matéria
     * @return array Resultado ['sucesso' => bool, 'arquivo' => string, 'caminho' => string]
     */
    public function gerarCertificado($usuario_id, $categoria_id) {
        // Obter dados do usuário
        $sql = "SELECT nome, email FROM usuarios WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            return ['sucesso' => false, 'erro' => 'Usuário não encontrado'];
        }
        
        // Obter dados da categoria
        $sql = "SELECT nome, descricao FROM videoaulas_categorias WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoria_id]);
        $categoria = $stmt->fetch();
        
        if (!$categoria) {
            return ['sucesso' => false, 'erro' => 'Categoria não encontrada'];
        }
        
        // Obter estatísticas
        $sql = "SELECT 
                    COUNT(*) as total_videoaulas,
                    SUM(CASE WHEN vp.concluida = 1 THEN 1 ELSE 0 END) as videoaulas_concluidas,
                    SUM(CASE WHEN vp.concluida = 1 THEN v.duracao ELSE 0 END) as tempo_total_minutos
                FROM videoaulas v
                LEFT JOIN videoaulas_progresso vp ON v.id = vp.videoaula_id AND vp.usuario_id = ?
                WHERE v.categoria_id = ? AND v.ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $categoria_id]);
        $estatisticas = $stmt->fetch();
        
        // Gerar HTML
        $html = $this->gerarHTML($usuario, $categoria, $estatisticas);
        
        // Salvar arquivo
        $filename = "certificado_" . $usuario_id . "_" . $categoria_id . "_" . date('Y-m-d_His') . ".html";
        $filepath = "uploads/" . $filename;
        
        file_put_contents($filepath, $html);
        
        return [
            'sucesso' => true,
            'arquivo' => $filename,
            'caminho' => $filepath
        ];
    }
    
    /**
     * Gera HTML do certificado
     */
    private function gerarHTML($usuario, $categoria, $estatisticas) {
        $data_atual = date('d/m/Y');
        $tempo_horas = round(($estatisticas['tempo_total_minutos'] ?? 0) / 60, 1);
        
        $html = '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Conclusão</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.5cm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Times New Roman", serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .certificado {
            background: #ffffff;
            width: 100%;
            max-width: 1000px;
            padding: 60px 80px;
            border: 20px solid #1a1a1a;
            box-shadow: 0 10px 50px rgba(0,0,0,0.3);
            position: relative;
        }
        
        .borda-dourada {
            border: 8px solid #d4af37;
            padding: 40px;
            position: relative;
        }
        
        .cabecalho {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .titulo-principal {
            font-size: 42px;
            font-weight: bold;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 10px;
            border-bottom: 4px solid #d4af37;
            padding-bottom: 15px;
        }
        
        .subtitulo {
            font-size: 24px;
            color: #333;
            font-style: italic;
            margin-top: 10px;
        }
        
        .conteudo {
            text-align: center;
            margin: 50px 0;
            line-height: 2;
        }
        
        .texto-principal {
            font-size: 22px;
            color: #2c2c2c;
            margin: 30px 0;
            text-align: justify;
            text-indent: 40px;
        }
        
        .nome-usuario {
            font-size: 32px;
            font-weight: bold;
            color: #1a1a1a;
            text-transform: uppercase;
            border-bottom: 3px solid #d4af37;
            padding-bottom: 10px;
            display: inline-block;
            margin: 20px 0;
            min-width: 400px;
        }
        
        .texto-secundario {
            font-size: 20px;
            color: #444;
            margin: 30px 0;
        }
        
        .materia {
            font-size: 26px;
            font-weight: bold;
            color: #cc0000;
            text-transform: uppercase;
            margin: 20px 0;
        }
        
        .estatisticas {
            background: #f9f9f9;
            border: 3px solid #d4af37;
            border-radius: 10px;
            padding: 25px;
            margin: 40px 0;
            display: flex;
            justify-content: space-around;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-numero {
            font-size: 36px;
            font-weight: bold;
            color: #cc0000;
        }
        
        .stat-label {
            font-size: 16px;
            color: #666;
            margin-top: 5px;
        }
        
        .rodape {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .assinatura {
            text-align: center;
            width: 250px;
        }
        
        .linha-assinatura {
            border-top: 2px solid #1a1a1a;
            margin-top: 60px;
            padding-top: 10px;
            font-size: 16px;
            font-weight: bold;
        }
        
        .data {
            text-align: center;
            font-size: 18px;
            color: #555;
            margin-top: 20px;
        }
        
        .selo {
            position: absolute;
            top: -40px;
            right: -40px;
            width: 120px;
            height: 120px;
            background: #cc0000;
            border: 5px solid #d4af37;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            font-weight: bold;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        .numero-certificado {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 12px;
            color: #999;
            font-family: monospace;
        }
        
        @media print {
            body {
                background: white;
            }
            
            .certificado {
                box-shadow: none;
                border: 20px solid #1a1a1a;
            }
            
            .borda-dourada {
                border: 8px solid #d4af37;
            }
        }
    </style>
</head>
<body>
    <div class="certificado">
        <div class="borda-dourada">
            <div class="selo">✓</div>
            
            <div class="cabecalho">
                <div class="titulo-principal">Certificado de Conclusão</div>
                <div class="subtitulo">RCP - Sistema de Concursos Públicos</div>
            </div>
            
            <div class="conteudo">
                <div class="texto-principal">
                    Certificamos que <span class="nome-usuario">' . htmlspecialchars(strtoupper($usuario['nome'])) . '</span>
                    completou com sucesso todas as videoaulas do módulo de estudos da matéria:
                </div>
                
                <div class="materia">' . htmlspecialchars($categoria['nome']) . '</div>
                
                <div class="estatisticas">
                    <div class="stat-item">
                        <div class="stat-numero">' . ($estatisticas['videoaulas_concluidas'] ?? 0) . '</div>
                        <div class="stat-label">Videoaulas</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-numero">' . $tempo_horas . 'h</div>
                        <div class="stat-label">Horas Estudadas</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-numero">100%</div>
                        <div class="stat-label">Progresso</div>
                    </div>
                </div>
                
                <div class="texto-secundario">
                    Este certificado comprova a dedicação e o empenho na conclusão completa do conteúdo programático desta disciplina,
                    demonstrando comprometimento com o aprendizado e desenvolvimento profissional.
                </div>
            </div>
            
            <div class="rodape">
                <div class="assinatura">
                    <div class="linha-assinatura">Sistema RCP</div>
                </div>
                <div class="data">
                    Concedido em ' . $data_atual . '
                </div>
                <div class="assinatura">
                    <div class="linha-assinatura">Diretoria Acadêmica</div>
                </div>
            </div>
            
            <div class="numero-certificado">
                Certificado Nº: RCP-' . $usuario_id . '-' . $categoria_id . '-' . date('YmdHis')
            </div>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
}

