<?php
// Configurações do Banco de Dados - TiDB Cloud (Nuvem)
$host     = 'gateway01.sa-east-1.prod.aws.tidbcloud.com';
$port     = '4000';
$user     = '3CBJN1D9QTgFwsa.root';
$pass     = '3gnDcXQI27x43L3D'; // Sua nova senha limpa
$dbname   = 'sys'; // Ou o nome do banco do seu sistema

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        // Configuração de SSL exigida pelo TiDB Cloud
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Erro na conexão com o banco de dados na nuvem: " . $e->getMessage());
}
?>