<?php
/**
 * Database Connection Manager
 * 
 * Gerenciador centralizado de conexão com o banco de dados
 * usando padrão Singleton para garantir apenas uma conexão ativa
 */

namespace Config;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    /**
     * Construtor privado para implementar padrão Singleton
     */
    private function __construct()
    {
        // Construtor privado
    }

    /**
     * Obtém a instância única da conexão (Singleton)
     * 
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtém a conexão PDO com o banco de dados
     * 
     * @return PDO
     * @throws PDOException Se a conexão falhar
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $config = require __DIR__ . '/config.php';
            $db = $config['database'];

            try {
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    $db['host'],
                    $db['name'],
                    $db['charset']
                );

                $this->connection = new PDO(
                    $dsn,
                    $db['user'],
                    $db['password'],
                    $db['options']
                );
            } catch (PDOException $e) {
                throw new PDOException(
                    "Erro ao conectar com o banco de dados: " . $e->getMessage(),
                    (int)$e->getCode()
                );
            }
        }

        return $this->connection;
    }

    /**
     * Previne clonagem da instância
     */
    private function __clone()
    {
    }

    /**
     * Previne desserialização da instância
     */
    public function __wakeup()
    {
        throw new \Exception("Não é possível desserializar singleton");
    }
}

