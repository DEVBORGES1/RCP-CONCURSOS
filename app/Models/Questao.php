<?php
/**
 * Questao Model
 * 
 * Model responsável por operações com a tabela questoes
 * 
 * @package App\Models
 */

namespace App\Models;

use App\Core\BaseModel;

class Questao extends BaseModel
{
    protected string $table = 'questoes';

    /**
     * Busca questões por edital
     * 
     * @param int $editalId ID do edital
     * @return array
     */
    public function findByEdital(int $editalId): array
    {
        return $this->findAll(['edital_id' => $editalId]);
    }

    /**
     * Busca questões por disciplina
     * 
     * @param int $disciplinaId ID da disciplina
     * @return array
     */
    public function findByDisciplina(int $disciplinaId): array
    {
        return $this->findAll(['disciplina_id' => $disciplinaId]);
    }

    /**
     * Busca questão aleatória
     * 
     * @param array $conditions Condições opcionais
     * @return array|null
     */
    public function findRandom(array $conditions = []): ?array
    {
        try {
            $sql = "SELECT * FROM {$this->table}";
            
            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $field => $value) {
                    $where[] = "{$field} = :{$field}";
                }
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $sql .= " ORDER BY RAND() LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($conditions);
            
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Erro ao buscar questão aleatória: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca questões aleatórias para simulado
     * 
     * @param int $quantidade Quantidade de questões
     * @param array $disciplinas IDs das disciplinas (opcional)
     * @param int|null $editalId ID do edital (opcional)
     * @return array
     */
    public function buscarParaSimulado(int $quantidade, array $disciplinas = [], ?int $editalId = null): array
    {
        try {
            $where = [];
            $params = [];
            
            if ($editalId) {
                $where[] = "edital_id = :edital_id";
                $params['edital_id'] = $editalId;
            }
            
            if (!empty($disciplinas)) {
                $where[] = "disciplina_id IN (" . implode(",", array_fill(0, count($disciplinas), "?")) . ")";
                $params = array_merge($params, $disciplinas);
            }
            
            $sql = "SELECT * FROM {$this->table}";
            
            if (!empty($where)) {
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $sql .= " ORDER BY RAND() LIMIT :limit";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind dos parâmetros
            foreach ($params as $key => $value) {
                $stmt->bindValue(":{$key}", $value, \PDO::PARAM_INT);
            }
            
            $stmt->bindValue(":limit", $quantidade, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Erro ao buscar questões para simulado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se a resposta está correta
     * 
     * @param int $questaoId ID da questão
     * @param string $resposta Resposta do usuário (A, B, C, D, E)
     * @return bool
     */
    public function verificarResposta(int $questaoId, string $resposta): bool
    {
        $questao = $this->find($questaoId);
        
        if (!$questao) {
            return false;
        }
        
        return strtoupper(trim($resposta)) === strtoupper(trim($questao['alternativa_correta']));
    }
}

