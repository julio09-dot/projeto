<?php
// Configurações via variáveis de ambiente com fallback para valores locais
$host = getenv('DB_HOST') ?: '192.168.10.31';
$port = getenv('DB_PORT') ?: '5432';
$user = getenv('DB_USER') ?: 'postgres';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '1234';
$db   = getenv('DB_NAME') ?: 'autosjulius';

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$db",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    $pdo->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    http_response_code(500);
    die("Erro ao ligar à base de dados PostgreSQL: " . $e->getMessage());
}
