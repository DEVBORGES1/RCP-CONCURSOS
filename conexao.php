<?php
/**
 * Arquivo de compatibilidade - MIGRAR PARA POO
 * 
 * Este arquivo mantém compatibilidade com código antigo.
 * Use Database::getInstance() em novos códigos.
 * 
 * @deprecated Use classes/Database.php
 */

require_once __DIR__ . '/classes/Database.php';

// Manter compatibilidade
$pdo = Database::getInstance()->getConnection();

