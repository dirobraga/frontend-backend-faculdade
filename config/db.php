<?php
// ============================================================
// Configuração do Banco de Dados
// ============================================================

define('DB_HOST',     'localhost');   // Endereço do servidor MySQL
define('DB_NAME',     'u199367788_SjJHpEoZL_fincontrol');  // Nome do banco de dados
define('DB_USER',     '');        // Usuário do banco
define('DB_PASSWORD', '');            // Senha do banco
define('DB_CHARSET',  'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;color:red;padding:20px;">
                <h2>Erro de Conexão com Banco de Dados</h2>
                <p>Verifique as configurações em <code>config/db.php</code></p>
                <p><small>' . htmlspecialchars($e->getMessage()) . '</small></p>
            </div>');
        }
    }
    return $pdo;
}
define('DB_CHARSET',  'utf8mb4');

// ← adicione esta linha aqui
require_once __DIR__ . '/../includes/auth.php';
