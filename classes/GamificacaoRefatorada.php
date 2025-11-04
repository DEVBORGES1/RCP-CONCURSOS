<?php
require_once 'Database.php';

/**
 * Classe GamificacaoRefatorada - Sistema de gamificação em POO
 * 
 * Gerencia pontos, níveis, conquistas, streak e rankings.
 * 
 * @package RCP-CONCURSOS
 * @author Sistema RCP
 * @version 2.0
 */
class GamificacaoRefatorada {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    /**
     * Adiciona pontos ao usuário
     * 
     * @param int $usuario_id ID do usuário
     * @param int $pontos Pontos a adicionar
     * @param string $tipo Tipo de ação (questao, simulado, etc)
     * @return bool Sucesso da operação
     */
    public function adicionarPontos($usuario_id, $pontos, $tipo = 'questao') {
        try {
            $this->pdo->beginTransaction();
            
            // Garantir progresso do usuário
            $this->garantirProgressoUsuario($usuario_id);
            
            // Atualizar pontos
            $sql = "UPDATE usuarios_progresso SET pontos_total = pontos_total + ? WHERE usuario_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$pontos, $usuario_id]);
            
            if ($stmt->rowCount() == 0) {
                throw new Exception("Falha ao atualizar pontos");
            }
            
            // Calcular e atualizar nível
            $novo_nivel = $this->calcularNivel($usuario_id);
            $this->atualizarNivel($usuario_id, $novo_nivel);
            
            // Verificar conquistas
            $this->verificarConquistas($usuario_id, $tipo);
            
            // Atualizar ranking mensal
            $this->atualizarRankingMensal($usuario_id, $pontos);
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erro ao adicionar pontos: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Garante que usuário tem registro de progresso
     * 
     * @param int $usuario_id ID do usuário
     */
    public function garantirProgressoUsuario($usuario_id) {
        $sql = "SELECT COUNT(*) FROM usuarios_progresso WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        
        if ($stmt->fetchColumn() == 0) {
            $sql = "INSERT INTO usuarios_progresso (usuario_id, nivel, pontos_total, streak_dias, ultimo_login) 
                    VALUES (?, 1, 0, 0, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id, date('Y-m-d')]);
        }
    }
    
    /**
     * Calcula nível baseado nos pontos
     * 
     * @param int $usuario_id ID do usuário
     * @return int Nível calculado
     */
    private function calcularNivel($usuario_id) {
        $sql = "SELECT pontos_total FROM usuarios_progresso WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $pontos = $stmt->fetchColumn();
        
        // Fórmula: nível = floor(sqrt(pontos / 100)) + 1
        return floor(sqrt($pontos / 100)) + 1;
    }
    
    /**
     * Atualiza nível do usuário
     * 
     * @param int $usuario_id ID do usuário
     * @param int $nivel Novo nível
     */
    private function atualizarNivel($usuario_id, $nivel) {
        $sql = "UPDATE usuarios_progresso SET nivel = ? WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nivel, $usuario_id]);
    }
    
    /**
     * Verifica conquistas
     * 
     * @param int $usuario_id ID do usuário
     * @param string $tipo Tipo de conquista
     */
    private function verificarConquistas($usuario_id, $tipo) {
        $sql = "SELECT * FROM conquistas WHERE tipo = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$tipo]);
        $conquistas = $stmt->fetchAll();
        
        foreach ($conquistas as $conquista) {
            if ($this->verificarConquistaEspecifica($usuario_id, $conquista)) {
                $this->concederConquista($usuario_id, $conquista['id']);
            }
        }
    }
    
    /**
     * Verifica conquista específica
     * 
     * @param int $usuario_id ID do usuário
     * @param array $conquista Dados da conquista
     * @return bool Conquista alcançada
     */
    private function verificarConquistaEspecifica($usuario_id, $conquista) {
        switch ($conquista['tipo']) {
            case 'questoes':
                return $this->verificarConquistaQuestoes($usuario_id, $conquista['pontos_necessarios']);
            case 'nivel':
                return $this->verificarConquistaNivel($usuario_id, $conquista['pontos_necessarios']);
            case 'streak':
                return $this->verificarConquistaStreak($usuario_id, $conquista['pontos_necessarios']);
            case 'simulado':
                return $this->verificarConquistaSimulado($usuario_id, $conquista['pontos_necessarios']);
        }
        return false;
    }
    
    /**
     * Verifica conquista de questões
     */
    private function verificarConquistaQuestoes($usuario_id, $necessarias) {
        $sql = "SELECT COUNT(*) FROM respostas_usuario WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchColumn() >= $necessarias;
    }
    
    /**
     * Verifica conquista de nível
     */
    private function verificarConquistaNivel($usuario_id, $nivel_necessario) {
        $sql = "SELECT nivel FROM usuarios_progresso WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchColumn() >= $nivel_necessario;
    }
    
    /**
     * Verifica conquista de streak
     */
    private function verificarConquistaStreak($usuario_id, $dias_necessarios) {
        $sql = "SELECT streak_dias FROM usuarios_progresso WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchColumn() >= $dias_necessarios;
    }
    
    /**
     * Verifica conquista de simulado
     */
    private function verificarConquistaSimulado($usuario_id, $necessarios) {
        $sql = "SELECT COUNT(*) FROM simulados WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchColumn() >= $necessarios;
    }
    
    /**
     * Concede conquista ao usuário
     */
    private function concederConquista($usuario_id, $conquista_id) {
        $sql = "SELECT COUNT(*) FROM usuarios_conquistas 
                WHERE usuario_id = ? AND conquista_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $conquista_id]);
        
        if ($stmt->fetchColumn() == 0) {
            $sql = "INSERT INTO usuarios_conquistas (usuario_id, conquista_id) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id, $conquista_id]);
            
            // Pontos bônus pela conquista
            $sql = "SELECT pontos_necessarios FROM conquistas WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$conquista_id]);
            $pontos_bonus = $stmt->fetchColumn();
            
            $sql = "UPDATE usuarios_progresso SET pontos_total = pontos_total + ? WHERE usuario_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$pontos_bonus, $usuario_id]);
        }
    }
    
    /**
     * Atualiza streak do usuário
     * 
     * @param int $usuario_id ID do usuário
     */
    public function atualizarStreak($usuario_id) {
        $this->garantirProgressoUsuario($usuario_id);
        
        $sql = "SELECT ultimo_login FROM usuarios_progresso WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $ultimo_login = $stmt->fetchColumn();
        
        $hoje = date('Y-m-d');
        $ontem = date('Y-m-d', strtotime('-1 day'));
        
        if ($ultimo_login == $ontem) {
            $sql = "UPDATE usuarios_progresso SET streak_dias = streak_dias + 1, ultimo_login = ? WHERE usuario_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$hoje, $usuario_id]);
        } elseif ($ultimo_login != $hoje) {
            $sql = "UPDATE usuarios_progresso SET streak_dias = 1, ultimo_login = ? WHERE usuario_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$hoje, $usuario_id]);
        }
    }
    
    /**
     * Atualiza ranking mensal
     */
    private function atualizarRankingMensal($usuario_id, $pontos) {
        $mes_ano = date('Y-m');
        
        $sql = "INSERT INTO ranking_mensal (usuario_id, mes_ano, pontos_mes) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE pontos_mes = pontos_mes + ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $mes_ano, $pontos, $pontos]);
        
        $this->recalcularPosicoesRanking($mes_ano);
    }
    
    /**
     * Recalcula posições do ranking
     */
    private function recalcularPosicoesRanking($mes_ano) {
        $sql = "UPDATE ranking_mensal r1 
                SET posicao = (
                    SELECT COUNT(*) + 1 
                    FROM ranking_mensal r2 
                    WHERE r2.mes_ano = r1.mes_ano 
                    AND r2.pontos_mes > r1.pontos_mes
                ) 
                WHERE r1.mes_ano = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$mes_ano]);
    }
    
    /**
     * Obtém dados do usuário
     * 
     * @param int $usuario_id ID do usuário
     * @return array Dados do usuário
     */
    public function obterDadosUsuario($usuario_id) {
        $this->garantirProgressoUsuario($usuario_id);
        
        $sql = "SELECT u.nome, u.email, p.nivel, p.pontos_total, p.streak_dias,
                       (SELECT COUNT(DISTINCT questao_id) FROM respostas_usuario WHERE usuario_id = ?) as questoes_respondidas,
                       (SELECT COUNT(DISTINCT questao_id) FROM respostas_usuario WHERE usuario_id = ? AND correta = 1) as questoes_corretas
                FROM usuarios u 
                LEFT JOIN usuarios_progresso p ON u.id = p.usuario_id 
                WHERE u.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $usuario_id, $usuario_id]);
        
        $dados = $stmt->fetch();
        
        if (!$dados) {
            return [
                'nome' => 'Usuário',
                'email' => '',
                'nivel' => 1,
                'pontos_total' => 0,
                'streak_dias' => 0,
                'questoes_respondidas' => 0,
                'questoes_corretas' => 0
            ];
        }
        
        return $dados;
    }
    
    /**
     * Obtém conquistas do usuário
     * 
     * @param int $usuario_id ID do usuário
     * @return array Lista de conquistas
     */
    public function obterConquistasUsuario($usuario_id) {
        $sql = "SELECT c.*, uc.data_conquista 
                FROM conquistas c 
                LEFT JOIN usuarios_conquistas uc ON c.id = uc.conquista_id AND uc.usuario_id = ?
                ORDER BY c.pontos_necessarios";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtém ranking mensal
     * 
     * @param int $limite Limite de resultados
     * @return array Ranking
     */
    public function obterRankingMensal($limite = 10) {
        $mes_ano = date('Y-m');
        
        // Validar e sanitizar limite para evitar SQL injection
        $limite = (int)$limite;
        if ($limite <= 0) {
            $limite = 10;
        }
        
        $sql = "SELECT u.nome, r.pontos_mes, r.posicao 
                FROM ranking_mensal r 
                JOIN usuarios u ON r.usuario_id = u.id 
                WHERE r.mes_ano = ? 
                ORDER BY r.posicao 
                LIMIT " . $limite;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$mes_ano]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtém posição do usuário no ranking
     * 
     * @param int $usuario_id ID do usuário
     * @return int|null Posição no ranking
     */
    public function obterPosicaoUsuario($usuario_id) {
        $mes_ano = date('Y-m');
        
        $sql = "SELECT posicao FROM ranking_mensal WHERE usuario_id = ? AND mes_ano = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $mes_ano]);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Verifica todas as conquistas
     * 
     * @param int $usuario_id ID do usuário
     */
    public function verificarTodasConquistas($usuario_id) {
        $this->garantirProgressoUsuario($usuario_id);
        
        $sql = "SELECT * FROM conquistas";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $conquistas = $stmt->fetchAll();
        
        foreach ($conquistas as $conquista) {
            if ($this->verificarConquistaEspecifica($usuario_id, $conquista)) {
                $this->concederConquista($usuario_id, $conquista['id']);
            }
        }
    }
    
    /**
     * Adiciona pontos por completar videoaula
     * 
     * @param int $usuario_id ID do usuário
     * @param int $videoaula_id ID da videoaula
     * @return bool Sucesso da operação
     */
    public function adicionarPontosVideoaula($usuario_id, $videoaula_id) {
        // Verificar se já ganhou pontos por esta videoaula
        $sql = "SELECT pontos_ganhos FROM videoaulas_progresso 
                WHERE usuario_id = ? AND videoaula_id = ? AND concluida = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $videoaula_id]);
        $progresso = $stmt->fetch();
        
        // Se já ganhou pontos, não dar novamente
        if ($progresso && isset($progresso['pontos_ganhos']) && $progresso['pontos_ganhos'] > 0) {
            return true;
        }
        
        // Obter duração da videoaula
        $sql = "SELECT duracao FROM videoaulas WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$videoaula_id]);
        $duracao = $stmt->fetchColumn() ?? 0;
        
        // Calcular pontos: 5 pontos por minuto de videoaula (mínimo 10, máximo 100)
        $pontos = max(10, min(100, $duracao * 5));
        
        // Adicionar pontos
        $resultado = $this->adicionarPontos($usuario_id, $pontos, 'videoaula');
        
        // Marcar que ganhou pontos por esta videoaula
        if ($resultado) {
            // Verificar se a coluna pontos_ganhos existe, se não, usar uma abordagem alternativa
            try {
                $sql = "UPDATE videoaulas_progresso SET pontos_ganhos = ? 
                        WHERE usuario_id = ? AND videoaula_id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$pontos, $usuario_id, $videoaula_id]);
            } catch (Exception $e) {
                // Se a coluna não existir, ignorar
                error_log("Coluna pontos_ganhos pode não existir: " . $e->getMessage());
            }
        }
        
        return $resultado;
    }
    
    /**
     * Verifica se categoria/matéria está 100% completa
     * 
     * @param int $usuario_id ID do usuário
     * @param int $categoria_id ID da categoria
     * @return bool Está 100% completa
     */
    public function verificarCategoriaCompleta($usuario_id, $categoria_id) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN vp.concluida = 1 THEN 1 ELSE 0 END) as concluidas
                FROM videoaulas v
                LEFT JOIN videoaulas_progresso vp ON v.id = vp.videoaula_id AND vp.usuario_id = ?
                WHERE v.categoria_id = ? AND v.ativo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $categoria_id]);
        $resultado = $stmt->fetch();
        
        return $resultado && $resultado['total'] > 0 && $resultado['concluidas'] == $resultado['total'];
    }
}

