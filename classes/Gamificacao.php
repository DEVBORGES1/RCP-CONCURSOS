<?php
require_once 'conexao.php';

class Gamificacao {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Adicionar pontos ao usuário
    public function adicionarPontos($usuario_id, $pontos, $tipo = 'questao') {
        try {
            $this->pdo->beginTransaction();
            
            // Garantir que o usuário tenha um registro de progresso
            $this->garantirProgressoUsuario($usuario_id);
            
            // Atualizar progresso do usuário
            $sql = "UPDATE usuarios_progresso SET pontos_total = pontos_total + ? WHERE usuario_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$pontos, $usuario_id]);
            
            // Verificar se a atualização foi bem-sucedida
            if ($stmt->rowCount() == 0) {
                throw new Exception("Falha ao atualizar pontos do usuário");
            }
            
            // Calcular novo nível
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
    
    // Garantir que o usuário tenha um registro de progresso
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
    
    // Calcular nível baseado nos pontos
    private function calcularNivel($usuario_id) {
        $sql = "SELECT pontos_total FROM usuarios_progresso WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $pontos = $stmt->fetchColumn();
        
        // Fórmula: nível = floor(sqrt(pontos / 100)) + 1
        return floor(sqrt($pontos / 100)) + 1;
    }
    
    // Atualizar nível do usuário
    private function atualizarNivel($usuario_id, $nivel) {
        $sql = "UPDATE usuarios_progresso SET nivel = ? WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$nivel, $usuario_id]);
    }
    
    // Verificar e conceder conquistas
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
    
    // Verificar conquista específica
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
    
    // Verificar conquista de questões
    private function verificarConquistaQuestoes($usuario_id, $necessarias) {
        $sql = "SELECT COUNT(*) FROM respostas_usuario WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $respondidas = $stmt->fetchColumn();
        
        return $respondidas >= $necessarias;
    }
    
    // Verificar conquista de nível
    private function verificarConquistaNivel($usuario_id, $nivel_necessario) {
        $sql = "SELECT nivel FROM usuarios_progresso WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $nivel_atual = $stmt->fetchColumn();
        
        return $nivel_atual >= $nivel_necessario;
    }
    
    // Verificar conquista de streak
    private function verificarConquistaStreak($usuario_id, $dias_necessarios) {
        $sql = "SELECT streak_dias FROM usuarios_progresso WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $streak_atual = $stmt->fetchColumn();
        
        return $streak_atual >= $dias_necessarios;
    }
    
    // Verificar conquista de simulado
    private function verificarConquistaSimulado($usuario_id, $necessarios) {
        $sql = "SELECT COUNT(*) FROM simulados WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $simulados = $stmt->fetchColumn();
        
        return $simulados >= $necessarios;
    }
    
    // Conceder conquista
    private function concederConquista($usuario_id, $conquista_id) {
        // Verificar se já tem a conquista
        $sql = "SELECT COUNT(*) FROM usuarios_conquistas 
                WHERE usuario_id = ? AND conquista_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $conquista_id]);
        
        if ($stmt->fetchColumn() == 0) {
            $sql = "INSERT INTO usuarios_conquistas (usuario_id, conquista_id) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id, $conquista_id]);
            
            // Adicionar pontos bônus pela conquista
            $sql = "SELECT pontos_necessarios FROM conquistas WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$conquista_id]);
            $pontos_bonus = $stmt->fetchColumn();
            
            $this->adicionarPontos($usuario_id, $pontos_bonus, 'conquista');
        }
    }
    
    // Atualizar streak do usuário
    public function atualizarStreak($usuario_id) {
        // Garantir que o usuário tenha um registro de progresso
        $this->garantirProgressoUsuario($usuario_id);
        
        $sql = "SELECT ultimo_login FROM usuarios_progresso WHERE usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $ultimo_login = $stmt->fetchColumn();
        
        $hoje = date('Y-m-d');
        $ontem = date('Y-m-d', strtotime('-1 day'));
        
        if ($ultimo_login == $ontem) {
            // Streak continua
            $sql = "UPDATE usuarios_progresso SET streak_dias = streak_dias + 1, ultimo_login = ? WHERE usuario_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$hoje, $usuario_id]);
        } elseif ($ultimo_login != $hoje) {
            // Streak quebrado
            $sql = "UPDATE usuarios_progresso SET streak_dias = 1, ultimo_login = ? WHERE usuario_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$hoje, $usuario_id]);
        }
    }
    
    // Atualizar ranking mensal
    private function atualizarRankingMensal($usuario_id, $pontos) {
        $mes_ano = date('Y-m');
        
        $sql = "INSERT INTO ranking_mensal (usuario_id, mes_ano, pontos_mes) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE pontos_mes = pontos_mes + ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $mes_ano, $pontos, $pontos]);
        
        // Recalcular posições
        $this->recalcularPosicoesRanking($mes_ano);
    }
    
    // Recalcular posições do ranking
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
    
    // Obter dados do usuário
    public function obterDadosUsuario($usuario_id) {
        // Garantir que o usuário tenha um registro de progresso
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
        
        // Garantir que sempre retorne dados válidos
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
    
    // Obter conquistas do usuário
    public function obterConquistasUsuario($usuario_id) {
        $sql = "SELECT c.*, uc.data_conquista 
                FROM conquistas c 
                LEFT JOIN usuarios_conquistas uc ON c.id = uc.conquista_id AND uc.usuario_id = ?
                ORDER BY c.pontos_necessarios";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        
        return $stmt->fetchAll();
    }
    
    // Obter ranking mensal
    public function obterRankingMensal($limite = 10) {
        $mes_ano = date('Y-m');
        
        // Validar limite para evitar SQL injection
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
    
    // Obter posição do usuário no ranking
    public function obterPosicaoUsuario($usuario_id) {
        $mes_ano = date('Y-m');
        
        $sql = "SELECT posicao FROM ranking_mensal WHERE usuario_id = ? AND mes_ano = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $mes_ano]);
        
        return $stmt->fetchColumn();
    }
}
?>
