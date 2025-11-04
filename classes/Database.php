<?php
/**
 * Classe Database - Singleton para gerenciar conexão com banco de dados
 * 
 * Implementa o padrão Singleton para garantir uma única conexão com o banco
 * de dados em toda a aplicação.
 * 
 * @package RCP-CONCURSOS
 * @author Sistema RCP
 * @version 2.0
 */
class Database {
    private static $instance = null;
    private $pdo;
    private $host;
    private $db;
    private $user;
    private $pass;
    
    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct() {
        $this->host = "localhost";
        $this->db = "concursos";
        $this->user = "root";
        $this->pass = "1234";
        
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->db};charset=utf8", 
                $this->user, 
                $this->pass
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die("Erro na conexão: " . $e->getMessage());
        }
    }
    
    /**
     * Obtém instância única do Database
     * 
     * @return Database Instância do singleton
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtém conexão PDO
     * 
     * @return PDO Conexão PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Previne clonagem da instância
     */
    private function __clone() {}
    
    /**
     * Previne deserialização da instância
     */
    public function __wakeup() {
        throw new Exception("Não é possível deserializar uma instância de Database");
    }
}

