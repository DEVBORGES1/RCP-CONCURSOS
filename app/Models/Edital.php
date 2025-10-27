<?php
/**
 * Edital Model
 * 
 * Model responsável por operações com a tabela editais
 * 
 * @package App\Models
 */

namespace App\Models;

use App\Core\BaseModel;

class Edital extends BaseModel
{
    protected string $table = 'editais';

    /**
     * Busca editais por usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return array
     */
    public function findByUsuario(int $usuarioId): array
    {
        return $this->findAll(['usuario_id' => $usuarioId], 'data_upload DESC');
    }

    /**
     * Conta editais por usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return int
     */
    public function contarPorUsuario(int $usuarioId): int
    {
        return $this->count(['usuario_id' => $usuarioId]);
    }

    /**
     * Obtém o último edital do usuário
     * 
     * @param int $usuarioId ID do usuário
     * @return array|null
     */
    public function ultimoEdital(int $usuarioId): ?array
    {
        $result = $this->findAll(['usuario_id' => $usuarioId], 'data_upload DESC', 1);
        return !empty($result) ? $result[0] : null;
    }
}

