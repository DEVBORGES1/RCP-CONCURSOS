<?php
require_once 'Database.php';

/**
 * Classe User - Gerencia operações relacionadas a usuários
 * 
 * Responsável por autenticação, autorização e gestão de dados de usuários.
 * 
 * @package RCP-CONCURSOS
 * @author Sistema RCP
 * @version 2.0
 */
class User {
    private $pdo;
    private $id;
    private $nome;
    private $email;
    
    /**
     * Construtor
     * 
     * @param int|null $id ID do usuário (opcional)
     */
    public function __construct($id = null) {
        $this->pdo = Database::getInstance()->getConnection();
        if ($id) {
            $this->loadById($id);
        }
    }
    
    /**
     * Carrega dados do usuário por ID
     * 
     * @param int $id ID do usuário
     * @return bool Sucesso da operação
     */
    public function loadById($id) {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user) {
            $this->id = $user['id'];
            $this->nome = $user['nome'];
            $this->email = $user['email'];
            return true;
        }
        return false;
    }
    
    /**
     * Carrega dados do usuário por email
     * 
     * @param string $email Email do usuário
     * @return bool Sucesso da operação
     */
    public function loadByEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $this->id = $user['id'];
            $this->nome = $user['nome'];
            $this->email = $user['email'];
            return true;
        }
        return false;
    }
    
    /**
     * Cria novo usuário
     * 
     * @param string $nome Nome do usuário
     * @param string $email Email do usuário
     * @param string $senha Senha (será hasheada)
     * @return bool Sucesso da operação
     */
    public function create($nome, $email, $senha) {
        try {
            // Verificar se email já existe
            if ($this->emailExists($email)) {
                return false;
            }
            
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$nome, $email, $senha_hash]);
            
            if ($result) {
                $this->id = $this->pdo->lastInsertId();
                $this->nome = $nome;
                $this->email = $email;
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro ao criar usuário: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se email já existe
     * 
     * @param string $email Email a verificar
     * @return bool Email existe
     */
    public function emailExists($email) {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Autentica usuário
     * 
     * @param string $email Email do usuário
     * @param string $senha Senha do usuário
     * @return bool Sucesso da autenticação
     */
    public function authenticate($email, $senha) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($senha, $user['senha_hash'])) {
            $this->id = $user['id'];
            $this->nome = $user['nome'];
            $this->email = $user['email'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtém ID do usuário
     * 
     * @return int|null ID do usuário
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Obtém nome do usuário
     * 
     * @return string|null Nome do usuário
     */
    public function getNome() {
        return $this->nome;
    }
    
    /**
     * Obtém email do usuário
     * 
     * @return string|null Email do usuário
     */
    public function getEmail() {
        return $this->email;
    }
    
    /**
     * Obtém dados completos do usuário
     * 
     * @return array Dados do usuário
     */
    public function getData() {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email
        ];
    }
    
    /**
     * Atualiza dados do usuário
     * 
     * @param array $data Dados para atualizar
     * @return bool Sucesso da operação
     */
    public function update($data) {
        try {
            $updates = [];
            $params = [];
            
            if (isset($data['nome'])) {
                $updates[] = "nome = ?";
                $params[] = $data['nome'];
            }
            
            if (isset($data['email'])) {
                $updates[] = "email = ?";
                $params[] = $data['email'];
            }
            
            if (isset($data['senha'])) {
                $updates[] = "senha_hash = ?";
                $params[] = password_hash($data['senha'], PASSWORD_DEFAULT);
            }
            
            if (empty($updates)) {
                return false;
            }
            
            $params[] = $this->id;
            $sql = "UPDATE usuarios SET " . implode(', ', $updates) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Erro ao atualizar usuário: " . $e->getMessage());
            return false;
        }
    }
}

