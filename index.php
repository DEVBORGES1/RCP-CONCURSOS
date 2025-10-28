<?php
/**
 * Ponto de Entrada Principal
 * 
 * Redireciona para o sistema antigo ou novo
 */

// Verificar qual sistema usar
if (file_exists(__DIR__ . '/mvc_index.php')) {
    // Usar sistema MVC
    require_once __DIR__ . '/mvc_index.php';
} else {
    // Fallback para homepage antiga
    require_once __DIR__ . '/index_backup.php';
}
