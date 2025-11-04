<?php
require_once 'Database.php';

/**
 * Classe Questao - Gerencia operações relacionadas a questões
 * 
 * Responsável por CRUD de questões, correção de respostas e estatísticas.
 * 
 * @package RCP-CONCURSOS
 * @author Sistema RCP
 * @version 2.0
 */
class Questao {
    private $pdo;
    private $id;
    private $edital_id;
    private $disciplina_id;
    private $enunciado;
    private $alternativas;
    private $alternativa_correta;
    
    /**
     * Construtor
     * 
     * @param int|null $id ID da questão (opcional)
     */
    public function __construct($id = null) {
        $this->pdo = Database::getInstance()->getConnection();
        $this->alternativas = ['a' => '', 'b' => '', 'c' => '', 'd' => '', 'e' => ''];
        
        if ($id) {
            $this->loadById($id);
        }
    }
    
    /**
     * Carrega questão por ID
     * 
     * @param int $id ID da questão
     * @return bool Sucesso da operação
     */
    public function loadById($id) {
        $sql = "SELECT * FROM questoes WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $questao = $stmt->fetch();
        
        if ($questao) {
            $this->id = $questao['id'];
            $this->edital_id = $questao['edital_id'];
            $this->disciplina_id = $questao['disciplina_id'];
            $this->enunciado = $questao['enunciado'];
            $this->alternativas = [
                'a' => $questao['alternativa_a'],
                'b' => $questao['alternativa_b'],
                'c' => $questao['alternativa_c'],
                'd' => $questao['alternativa_d'],
                'e' => $questao['alternativa_e']
            ];
            $this->alternativa_correta = $questao['alternativa_correta'];
            return true;
        }
        return false;
    }
    
    /**
     * Cria nova questão
     * 
     * @param int $edital_id ID do edital
     * @param int|null $disciplina_id ID da disciplina (opcional)
     * @param string $enunciado Enunciado da questão
     * @param array $alternativas Array com alternativas ['a' => '', 'b' => '', ...]
     * @param string $alternativa_correta Alternativa correta (A, B, C, D ou E)
     * @return bool Sucesso da operação
     */
    public function create($edital_id, $disciplina_id, $enunciado, $alternativas, $alternativa_correta) {
        try {
            $sql = "INSERT INTO questoes 
                    (edital_id, disciplina_id, enunciado, alternativa_a, alternativa_b, 
                     alternativa_c, alternativa_d, alternativa_e, alternativa_correta) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                $edital_id,
                $disciplina_id,
                $enunciado,
                $alternativas['a'] ?? '',
                $alternativas['b'] ?? '',
                $alternativas['c'] ?? '',
                $alternativas['d'] ?? '',
                $alternativas['e'] ?? '',
                strtoupper($alternativa_correta)
            ]);
            
            if ($result) {
                $this->id = $this->pdo->lastInsertId();
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro ao criar questão: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica resposta do usuário
     * 
     * @param string $resposta Resposta fornecida pelo usuário
     * @return bool Resposta correta
     */
    public function verificarResposta($resposta) {
        $resposta_normalizada = strtoupper(trim($resposta));
        $correta_normalizada = strtoupper(trim($this->alternativa_correta));
        
        return $resposta_normalizada === $correta_normalizada;
    }
    
    /**
     * Registra resposta do usuário
     * 
     * @param int $usuario_id ID do usuário
     * @param string $resposta Resposta fornecida
     * @return array Resultado ['acertou' => bool, 'pontos' => int]
     */
    public function registrarResposta($usuario_id, $resposta) {
        $acertou = $this->verificarResposta($resposta);
        $pontos = $acertou ? 10 : 0;
        
        try {
            // Evitar duplicatas
            $sql = "INSERT IGNORE INTO respostas_usuario 
                    (usuario_id, questao_id, resposta, correta, pontos_ganhos) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id, $this->id, $resposta, $acertou ? 1 : 0, $pontos]);
            
            return [
                'acertou' => $acertou,
                'pontos' => $pontos,
                'resposta_correta' => $this->alternativa_correta
            ];
        } catch (Exception $e) {
            error_log("Erro ao registrar resposta: " . $e->getMessage());
            return ['acertou' => false, 'pontos' => 0, 'resposta_correta' => null];
        }
    }
    
    /**
     * Obtém questões aleatórias
     * 
     * @param int $limite Limite de questões
     * @param array $filtros Filtros opcionais ['edital_id' => int, 'disciplina_id' => int]
     * @return array Lista de questões
     */
    public static function getRandom($limite = 10, $filtros = []) {
        $pdo = Database::getInstance()->getConnection();
        
        $where = [];
        $params = [];
        
        if (isset($filtros['edital_id'])) {
            $where[] = "edital_id = ?";
            $params[] = $filtros['edital_id'];
        }
        
        if (isset($filtros['disciplina_id'])) {
            $where[] = "disciplina_id = ?";
            $params[] = $filtros['disciplina_id'];
        }
        
        // Validar e sanitizar limite para evitar SQL injection
        $limite = (int)$limite;
        if ($limite <= 0) {
            $limite = 10;
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        $sql = "SELECT * FROM questoes {$whereClause} ORDER BY RAND() LIMIT " . $limite;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtém estatísticas de questões por usuário
     * 
     * @param int $usuario_id ID do usuário
     * @param array $filtros Filtros opcionais
     * @return array Estatísticas ['total' => int, 'respondidas' => int, 'corretas' => int]
     */
    public static function getEstatisticas($usuario_id, $filtros = []) {
        $pdo = Database::getInstance()->getConnection();
        
        // Total de questões
        $where = [];
        $params = [];
        
        if (isset($filtros['edital_id'])) {
            $where[] = "q.edital_id = ?";
            $params[] = $filtros['edital_id'];
        }
        
        if (isset($filtros['disciplina_id'])) {
            $where[] = "q.disciplina_id = ?";
            $params[] = $filtros['disciplina_id'];
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT COUNT(*) as total FROM questoes q {$whereClause}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];
        
        // Questões respondidas
        $params2 = $params;
        $params2[] = $usuario_id;
        $sql = "SELECT COUNT(DISTINCT q.id) as respondidas 
                FROM questoes q 
                JOIN respostas_usuario r ON q.id = r.questao_id 
                {$whereClause} AND r.usuario_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params2);
        $respondidas = $stmt->fetch()['respondidas'];
        
        // Questões corretas
        $params3 = $params;
        $params3[] = $usuario_id;
        $sql = "SELECT COUNT(*) as corretas 
                FROM questoes q 
                JOIN respostas_usuario r ON q.id = r.questao_id 
                {$whereClause} AND r.usuario_id = ? AND r.correta = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params3);
        $corretas = $stmt->fetch()['corretas'];
        
        return [
            'total' => $total,
            'respondidas' => $respondidas,
            'corretas' => $corretas,
            'percentual_acerto' => $respondidas > 0 ? round(($corretas / $respondidas) * 100, 1) : 0
        ];
    }
    
    /**
     * Getters
     */
    public function getId() { return $this->id; }
    public function getEditalId() { return $this->edital_id; }
    public function getDisciplinaId() { return $this->disciplina_id; }
    public function getEnunciado() { return $this->enunciado; }
    public function getAlternativas() { return $this->alternativas; }
    public function getAlternativaCorreta() { return $this->alternativa_correta; }
    
    /**
     * Obtém dados completos da questão
     * 
     * @return array Dados da questão
     */
    public function getData() {
        return [
            'id' => $this->id,
            'edital_id' => $this->edital_id,
            'disciplina_id' => $this->disciplina_id,
            'enunciado' => $this->enunciado,
            'alternativas' => $this->alternativas,
            'alternativa_correta' => $this->alternativa_correta
        ];
    }
}

