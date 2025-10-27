<?php
/**
 * Simulado Model
 * 
 * Model responsável por operações com a tabela simulados
 * 
 * @package App\Models
 */

namespace App\Models;

use App\Core\BaseModel;

class Simulado extends BaseModel
{
    protected string $table = 'simulados';

    /**
     * Busca simulados por usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return array
     */
    public function findByUsuario(int $usuarioId): array
    {
        return $this->findAll(['usuario_id' => $usuarioId], 'data_criacao DESC');
    }

    /**
     * Busca simulados concluídos por usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return array
     */
    public function findConcluidos(int $usuarioId): array
    {
        try {
            $sql = "
                SELECT * FROM {$this->table} 
                WHERE usuario_id = :usuario_id 
                AND questoes_corretas IS NOT NULL 
                ORDER BY data_criacao DESC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['usuario_id' => $usuarioId]);
            
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Erro ao buscar simulados concluídos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Finaliza um simulado
     * 
     * @param int $simuladoId ID do simulado
     * @param int $questoesCorretas Quantidade de questões corretas
     * @param int $totalQuestoes Total de questões
     * @return bool
     */
    public function finalizar(int $simuladoId, int $questoesCorretas, int $totalQuestoes): bool
    {
        $data = [
            'questoes_corretas' => $questoesCorretas,
            'questoes_total' => $totalQuestoes,
            'pontuacao_final' => $questoesCorretas
        ];
        
        return $this->update($simuladoId, $data);
    }
}

