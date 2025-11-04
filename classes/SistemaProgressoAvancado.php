<?php
require_once 'Database.php';
require_once 'GamificacaoRefatorada.php';

/**
 * Classe SistemaProgressoAvancado - Sistema de progresso avançado para dashboard
 * 
 * @package RCP-CONCURSOS
 * @author Sistema RCP
 * @version 2.0
 */
class SistemaProgressoAvancado {
    private $pdo;
    private $gamificacao;
    
    public function __construct($pdo = null) {
        $this->pdo = $pdo ?: Database::getInstance()->getConnection();
        $this->gamificacao = new GamificacaoRefatorada();
    }
    
    /**
     * Obtém dashboard completo do usuário
     * 
     * @param int $usuario_id ID do usuário
     * @return array Dashboard completo
     */
    public function obterDashboardCompleto($usuario_id) {
        $dados_usuario = $this->gamificacao->obterDadosUsuario($usuario_id);
        
        return [
            'resumo_geral' => [
                'nivel' => $dados_usuario['nivel'] ?? 1,
                'pontos_total' => $dados_usuario['pontos_total'] ?? 0,
                'streak_dias' => $dados_usuario['streak_dias'] ?? 0,
                'questoes_respondidas' => $dados_usuario['questoes_respondidas'] ?? 0,
                'questoes_corretas' => $dados_usuario['questoes_corretas'] ?? 0,
                'taxa_acerto' => $dados_usuario['questoes_respondidas'] > 0 ? 
                    round(($dados_usuario['questoes_corretas'] / $dados_usuario['questoes_respondidas']) * 100, 1) : 0,
                'disciplinas_estudadas' => $this->obterTotalDisciplinas($usuario_id),
                'progresso_nivel' => $this->calcularProgressoNivel($dados_usuario['pontos_total'] ?? 0, $dados_usuario['nivel'] ?? 1)
            ],
            'progresso_por_disciplina' => $this->obterProgressoPorDisciplina($usuario_id),
            'insights_inteligentes' => $this->obterInsights($usuario_id),
            'proximos_desafios' => $this->obterProximosDesafios($usuario_id),
            'historico_progresso' => $this->obterHistoricoProgresso($usuario_id),
            'conquistas_recentes' => $this->obterConquistas($usuario_id)
        ];
    }
    
    /**
     * Obtém total de disciplinas estudadas
     */
    private function obterTotalDisciplinas($usuario_id) {
        $sql = "SELECT COUNT(DISTINCT d.id) as total
                FROM disciplinas d
                JOIN questoes q ON d.id = q.disciplina_id
                JOIN respostas_usuario r ON q.id = r.questao_id
                WHERE r.usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchColumn() ?? 0;
    }
    
    /**
     * Calcula progresso do nível
     */
    private function calcularProgressoNivel($pontos_total, $nivel_atual) {
        // Pontos necessários para o nível atual
        $pontos_nivel_atual = pow($nivel_atual - 1, 2) * 100;
        // Pontos necessários para o próximo nível
        $pontos_proximo_nivel = pow($nivel_atual, 2) * 100;
        // Pontos do intervalo atual
        $pontos_intervalo = $pontos_proximo_nivel - $pontos_nivel_atual;
        // Pontos ganhos no intervalo atual
        $pontos_ganhos_intervalo = $pontos_total - $pontos_nivel_atual;
        
        if ($pontos_intervalo > 0) {
            return round(($pontos_ganhos_intervalo / $pontos_intervalo) * 100, 1);
        }
        return 0;
    }
    
    /**
     * Obtém progresso por disciplina
     */
    private function obterProgressoPorDisciplina($usuario_id) {
        $sql = "SELECT 
                    d.nome_disciplina,
                    COUNT(DISTINCT r.questao_id) as questoes_respondidas,
                    SUM(CASE WHEN r.correta = 1 THEN 1 ELSE 0 END) as questoes_corretas,
                    CASE 
                        WHEN COUNT(DISTINCT r.questao_id) > 0 THEN
                            ROUND((SUM(CASE WHEN r.correta = 1 THEN 1 ELSE 0 END) / COUNT(DISTINCT r.questao_id)) * 100, 1)
                        ELSE 0 
                    END as taxa_acerto,
                    SUM(COALESCE(r.pontos_ganhos, 0)) as pontos_disciplina,
                    CASE 
                        WHEN SUM(CASE WHEN r.correta = 1 THEN 1 ELSE 0 END) >= 10 THEN 3
                        WHEN SUM(CASE WHEN r.correta = 1 THEN 1 ELSE 0 END) >= 5 THEN 2
                        ELSE 1 
                    END as nivel_dominio
                FROM disciplinas d
                JOIN questoes q ON d.id = q.disciplina_id
                LEFT JOIN respostas_usuario r ON q.id = r.questao_id AND r.usuario_id = ?
                WHERE d.edital_id IN (SELECT id FROM editais WHERE usuario_id = ?)
                GROUP BY d.id, d.nome_disciplina
                HAVING questoes_respondidas > 0
                ORDER BY pontos_disciplina DESC, taxa_acerto DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $usuario_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtém insights inteligentes
     */
    private function obterInsights($usuario_id) {
        $insights = [];
        
        $dados = $this->gamificacao->obterDadosUsuario($usuario_id);
        
        // Insight sobre taxa de acerto
        $taxa_acerto = $dados['questoes_respondidas'] > 0 ? 
            round(($dados['questoes_corretas'] / $dados['questoes_respondidas']) * 100, 1) : 0;
        
        if ($taxa_acerto < 50) {
            $insights[] = [
                'tipo' => 'warning',
                'titulo' => 'Taxa de acerto baixa',
                'icone' => '⚠️',
                'mensagem' => 'Sua taxa de acerto está em ' . $taxa_acerto . '%. Revise os conteúdos com mais atenção.',
                'acao_sugerida' => 'Revisar conteúdo'
            ];
        } elseif ($taxa_acerto >= 80) {
            $insights[] = [
                'tipo' => 'success',
                'titulo' => 'Excelente desempenho!',
                'icone' => '🎯',
                'mensagem' => 'Sua taxa de acerto está em ' . $taxa_acerto . '%. Continue assim!',
                'acao_sugerida' => 'Continuar estudando'
            ];
        }
        
        // Insight sobre streak
        if ($dados['streak_dias'] >= 7) {
            $insights[] = [
                'tipo' => 'success',
                'titulo' => 'Sequência impressionante!',
                'icone' => '🔥',
                'mensagem' => 'Você está há ' . $dados['streak_dias'] . ' dias estudando consecutivamente!',
                'acao_sugerida' => 'Manter sequência'
            ];
        }
        
        // Insight sobre nível
        if ($dados['nivel'] >= 10) {
            $insights[] = [
                'tipo' => 'success',
                'titulo' => 'Nível avançado!',
                'icone' => '⭐',
                'mensagem' => 'Você alcançou o nível ' . $dados['nivel'] . '. Parabéns pelo seu progresso!',
                'acao_sugerida' => 'Ver conquistas'
            ];
        }
        
        return $insights;
    }
    
    /**
     * Obtém próximos desafios
     */
    private function obterProximosDesafios($usuario_id) {
        $desafios = [];
        
        $dados = $this->gamificacao->obterDadosUsuario($usuario_id);
        
        // Desafio de questões
        if ($dados['questoes_respondidas'] < 50) {
            $desafios[] = [
                'titulo' => 'Responder 50 questões',
                'descricao' => 'Complete 50 questões para ganhar uma conquista especial.',
                'dificuldade' => 'medio',
                'pontos_recompensa' => 100,
                'tempo_estimado' => '2-3 horas'
            ];
        }
        
        // Desafio de streak
        if ($dados['streak_dias'] < 7) {
            $desafios[] = [
                'titulo' => 'Sequência de 7 dias',
                'descricao' => 'Estude 7 dias seguidos para ganhar pontos extras.',
                'dificuldade' => 'medio',
                'pontos_recompensa' => 200,
                'tempo_estimado' => '7 dias'
            ];
        }
        
        // Desafio de nível
        $proximo_nivel = $dados['nivel'] + 1;
        $pontos_proximo_nivel = pow($proximo_nivel, 2) * 100;
        $pontos_restantes = $pontos_proximo_nivel - $dados['pontos_total'];
        
        if ($pontos_restantes > 0 && $pontos_restantes < 500) {
            $desafios[] = [
                'titulo' => 'Alcançar nível ' . $proximo_nivel,
                'descricao' => 'Faltam ' . $pontos_restantes . ' pontos para o próximo nível.',
                'dificuldade' => 'facil',
                'pontos_recompensa' => 50,
                'tempo_estimado' => '1-2 horas'
            ];
        }
        
        return $desafios;
    }
    
    /**
     * Obtém histórico de progresso
     */
    private function obterHistoricoProgresso($usuario_id, $dias = 30) {
        $sql = "SELECT 
                    data_registro,
                    pontos_dia,
                    questoes_respondidas,
                    questoes_corretas,
                    taxa_acerto,
                    tempo_estudo_minutos,
                    simulados_completos
                FROM historico_progresso
                WHERE usuario_id = ? 
                AND data_registro >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                ORDER BY data_registro ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $dias]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtém conquistas recentes e próximas
     */
    private function obterConquistas($usuario_id) {
        // Conquistas já desbloqueadas
        $sql = "SELECT c.*, uc.data_conquista
                FROM conquistas c
                JOIN usuarios_conquistas uc ON c.id = uc.conquista_id
                WHERE uc.usuario_id = ?
                ORDER BY uc.data_conquista DESC
                LIMIT 5";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $recentes = $stmt->fetchAll();
        
        // Próximas conquistas (não desbloqueadas)
        $sql = "SELECT c.*
                FROM conquistas c
                LEFT JOIN usuarios_conquistas uc ON c.id = uc.conquista_id AND uc.usuario_id = ?
                WHERE uc.id IS NULL
                ORDER BY c.pontos_necessarios ASC
                LIMIT 5";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $proximas = $stmt->fetchAll();
        
        return [
            'recentes' => $recentes,
            'proximas' => $proximas
        ];
    }
}

