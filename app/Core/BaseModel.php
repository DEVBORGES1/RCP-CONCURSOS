<?php
/**
 * BaseModel
 * 
 * Classe base para todos os modelos da aplicação
 * Fornece funcionalidades comuns de acesso ao banco de dados
 * 
 * @package App\Core
 */

namespace App\Core;

use Config\Database;
use PDO;
use PDOException;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    /**
     * Construtor da classe base
     * 
     * Inicializa a conexão com o banco de dados
     */
    public function __construct()
    {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    /**
     * Encontra um registro por ID
     * 
     * @param int $id ID do registro
     * @return array|null Registro encontrado ou null
     */
    public function find(int $id): ?array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erro ao buscar registro: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Encontra todos os registros
     * 
     * @param array $conditions Condições WHERE (ex: ['campo' => 'valor'])
     * @param string|null $orderBy Ordenação (ex: 'campo ASC')
     * @param int|null $limit Limite de registros
     * @return array Array de registros
     */
    public function findAll(array $conditions = [], ?string $orderBy = null, ?int $limit = null): array
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
            
            if ($orderBy) {
                $sql .= " ORDER BY {$orderBy}";
            }
            
            if ($limit) {
                $sql .= " LIMIT {$limit}";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($conditions);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro ao buscar registros: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cria um novo registro
     * 
     * @param array $data Dados para inserção
     * @return int|false ID do registro inserido ou false em caso de erro
     */
    public function create(array $data)
    {
        try {
            $fields = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));
            
            $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            
            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao criar registro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza um registro
     * 
     * @param int $id ID do registro
     * @param array $data Dados para atualização
     * @return bool Sucesso da operação
     */
    public function update(int $id, array $data): bool
    {
        try {
            $fields = [];
            foreach (array_keys($data) as $field) {
                $fields[] = "{$field} = :{$field}";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(", ", $fields) . " WHERE {$this->primaryKey} = :id";
            
            $data['id'] = $id;
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar registro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deleta um registro
     * 
     * @param int $id ID do registro
     * @return bool Sucesso da operação
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
            $stmt = $this->db->prepare($sql);
            
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Erro ao deletar registro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Conta registros com condições
     * 
     * @param array $conditions Condições WHERE
     * @return int Número de registros
     */
    public function count(array $conditions = []): int
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            
            if (!empty($conditions)) {
                $where = [];
                foreach ($conditions as $field => $value) {
                    $where[] = "{$field} = :{$field}";
                }
                $sql .= " WHERE " . implode(" AND ", $where);
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($conditions);
            
            $result = $stmt->fetch();
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Erro ao contar registros: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Executa uma query SQL customizada
     * 
     * @param string $sql SQL para executar
     * @param array $params Parâmetros para a query
     * @return array|bool Resultado da query ou false em erro
     */
    protected function query(string $sql, array $params = [])
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            // Se é SELECT, retorna array com resultados
            if (stripos($sql, 'SELECT') === 0) {
                return $stmt->fetchAll();
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao executar query: " . $e->getMessage());
            return false;
        }
    }
}

