<?php
require_once 'Database.php';
require_once 'Questao.php';

/**
 * Classe Simulado - Gerencia operações relacionadas a simulados
 * 
 * Responsável por criação, execução e correção de simulados.
 * 
 * @package RCP-CONCURSOS
 * @author Sistema RCP
 * @version 2.0
 */
class Simulado {
    private $pdo;
    private $id;
    private $usuario_id;
    private $nome;
    private $data_criacao;
    private $questoes_total;
    private $questoes_corretas;
    private $pontuacao_final;
    private $tempo_gasto;
    private $questoes;
    
    /**
     * Construtor
     * 
     * @param int|null $id ID do simulado (opcional)
     */
    public function __construct($id = null) {
        $this->pdo = Database::getInstance()->getConnection();
        $this->questoes = [];
        
        if ($id) {
            $this->loadById($id);
        }
    }
    
    /**
     * Carrega simulado por ID
     * 
     * @param int $id ID do simulado
     * @return bool Sucesso da operação
     */
    public function loadById($id) {
        $sql = "SELECT * FROM simulados WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $simulado = $stmt->fetch();
        
        if ($simulado) {
            $this->id = $simulado['id'];
            $this->usuario_id = $simulado['usuario_id'];
            $this->nome = $simulado['nome'];
            $this->data_criacao = $simulado['data_criacao'];
            $this->questoes_total = $simulado['questoes_total'];
            $this->questoes_corretas = $simulado['questoes_corretas'];
            $this->pontuacao_final = $simulado['pontuacao_final'];
            $this->tempo_gasto = $simulado['tempo_gasto'];
            $this->loadQuestoes();
            return true;
        }
        return false;
    }
    
    /**
     * Carrega questões do simulado
     */
    private function loadQuestoes() {
        if (!$this->id) return;
        
        $sql = "SELECT q.*, sq.resposta_usuario, sq.correta 
                FROM simulados_questoes sq 
                JOIN questoes q ON sq.questao_id = q.id 
                WHERE sq.simulado_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->id]);
        $this->questoes = $stmt->fetchAll();
    }
    
    /**
     * Cria novo simulado
     * 
     * @param int $usuario_id ID do usuário
     * @param string $nome Nome do simulado
     * @param int $quantidade_questoes Quantidade de questões
     * @param array $filtros Filtros para seleção de questões
     * @return bool Sucesso da operação
     */
    public function create($usuario_id, $nome, $quantidade_questoes, $filtros = []) {
        try {
            $this->pdo->beginTransaction();
            
            // Criar simulado
            $sql = "INSERT INTO simulados (usuario_id, nome, questoes_total) VALUES (?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$usuario_id, $nome, $quantidade_questoes]);
            
            $this->id = $this->pdo->lastInsertId();
            $this->usuario_id = $usuario_id;
            $this->nome = $nome;
            $this->questoes_total = $quantidade_questoes;
            
            // Selecionar questões aleatórias
            $questoes = Questao::getRandom($quantidade_questoes, $filtros);
            
            // Adicionar questões ao simulado
            foreach ($questoes as $questao) {
                $sql = "INSERT INTO simulados_questoes (simulado_id, questao_id) VALUES (?, ?)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$this->id, $questao['id']]);
            }
            
            $this->pdo->commit();
            $this->loadQuestoes();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erro ao criar simulado: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Finaliza e corrige simulado
     * 
     * @param array $respostas Respostas do usuário ['questao_id' => 'resposta']
     * @param int $tempo_gasto Tempo gasto em minutos
     * @return array Resultado ['sucesso' => bool, 'pontos' => int, 'acertos' => int]
     */
    public function finalizar($respostas, $tempo_gasto) {
        try {
            $this->pdo->beginTransaction();
            
            $pontos_total = 0;
            $questoes_corretas = 0;
            
            // Processar cada resposta
            foreach ($respostas as $questao_id => $resposta_usuario) {
                if (strpos($questao_id, 'questao_') === 0) {
                    $questao_id = str_replace('questao_', '', $questao_id);
                }
                
                // Buscar resposta correta
                $questao = new Questao($questao_id);
                if (!$questao->getId()) continue;
                
                $acertou = $questao->verificarResposta($resposta_usuario);
                $pontos = $acertou ? 10 : 0;
                
                // Atualizar resposta no simulado
                $sql = "UPDATE simulados_questoes 
                        SET resposta_usuario = ?, correta = ? 
                        WHERE simulado_id = ? AND questao_id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    $resposta_usuario,
                    $acertou ? 1 : 0,
                    $this->id,
                    $questao_id
                ]);
                
                // Registrar resposta individual
                $questao->registrarResposta($this->usuario_id, $resposta_usuario);
                
                $pontos_total += $pontos;
                if ($acertou) $questoes_corretas++;
            }
            
            // Atualizar simulado
            $sql = "UPDATE simulados 
                    SET questoes_corretas = ?, pontuacao_final = ?, tempo_gasto = ? 
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $questoes_corretas,
                $pontos_total,
                $tempo_gasto,
                $this->id
            ]);
            
            $this->pdo->commit();
            
            $this->questoes_corretas = $questoes_corretas;
            $this->pontuacao_final = $pontos_total;
            $this->tempo_gasto = $tempo_gasto;
            
            return [
                'sucesso' => true,
                'pontos' => $pontos_total,
                'acertos' => $questoes_corretas,
                'total' => $this->questoes_total
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Erro ao finalizar simulado: " . $e->getMessage());
            return [
                'sucesso' => false,
                'pontos' => 0,
                'acertos' => 0,
                'total' => 0
            ];
        }
    }
    
    /**
     * Obtém simulado para exibição (sem respostas corretas)
     * 
     * @return array Dados do simulado
     */
    public function getDataForDisplay() {
        $questoes_display = [];
        
        foreach ($this->questoes as $q) {
            $questoes_display[] = [
                'id' => $q['id'],
                'enunciado' => $q['enunciado'],
                'alternativas' => [
                    'A' => $q['alternativa_a'],
                    'B' => $q['alternativa_b'],
                    'C' => $q['alternativa_c'],
                    'D' => $q['alternativa_d'],
                    'E' => $q['alternativa_e']
                ]
            ];
        }
        
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'questoes_total' => $this->questoes_total,
            'questoes' => $questoes_display
        ];
    }
    
    /**
     * Obtém resultado do simulado (com respostas corretas)
     * 
     * @return array Dados do resultado
     */
    public function getResultData() {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'data_criacao' => $this->data_criacao,
            'questoes_total' => $this->questoes_total,
            'questoes_corretas' => $this->questoes_corretas,
            'pontuacao_final' => $this->pontuacao_final,
            'tempo_gasto' => $this->tempo_gasto,
            'percentual_acerto' => $this->questoes_total > 0 
                ? round(($this->questoes_corretas / $this->questoes_total) * 100, 1) 
                : 0,
            'questoes' => $this->questoes
        ];
    }
    
    /**
     * Lista simulados por usuário
     * 
     * @param int $usuario_id ID do usuário
     * @param array $filtros Filtros opcionais
     * @return array Lista de simulados
     */
    public static function listByUser($usuario_id, $filtros = []) {
        $pdo = Database::getInstance()->getConnection();
        
        $where = ["usuario_id = ?"];
        $params = [$usuario_id];
        
        if (isset($filtros['finalizado'])) {
            if ($filtros['finalizado']) {
                $where[] = "questoes_corretas IS NOT NULL";
            } else {
                $where[] = "questoes_corretas IS NULL";
            }
        }
        
        $whereClause = "WHERE " . implode(" AND ", $where);
        $sql = "SELECT * FROM simulados {$whereClause} ORDER BY data_criacao DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getUsuarioId() { return $this->usuario_id; }
    public function getNome() { return $this->nome; }
    public function getQuestoesTotal() { return $this->questoes_total; }
    public function getQuestoesCorretas() { return $this->questoes_corretas; }
    public function getPontuacaoFinal() { return $this->pontuacao_final; }
    public function getQuestoes() { return $this->questoes; }
}

