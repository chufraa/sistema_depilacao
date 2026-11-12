<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome               = trim($_POST['nome']);
    $tipo               = $_POST['tipo'];
    $preco_venda        = str_replace(',', '.', trim($_POST['preco_venda']));
    $quantidade_estoque = (int)($_POST['quantidade_estoque'] ?? 0);

    if (!empty($nome) && is_numeric($preco_venda)) {
        $sql = "INSERT INTO produtos (nome, tipo, preco_venda, quantidade_estoque) VALUES (:nome, :tipo, :preco_venda, :quantidade_estoque)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome'               => $nome,
            'tipo'               => $tipo,
            'preco_venda'        => $preco_venda,
            'quantidade_estoque' => $quantidade_estoque
        ]);
    }
}

header('Location: index.php?sucesso=1');
exit;
?>