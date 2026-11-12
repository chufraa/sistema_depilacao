<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descricao = trim($_POST['descricao']);
    $tipo = $_POST['tipo'];
    $valor = str_replace(',', '.', $_POST['valor']);
    $data_lancamento = $_POST['data_lancamento'];
    $data_vencimento = !empty($_POST['data_vencimento']) ? $_POST['data_vencimento'] : null;
    $status_pagamento = $_POST['status_pagamento'];

    $stmt = $pdo->prepare("INSERT INTO financeiro (descricao, tipo, valor, data_lancamento, data_vencimento, status_pagamento) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$descricao, $tipo, $valor, $data_lancamento, $data_vencimento, $status_pagamento]);
}

header('Location: index.php?sucesso=1');
exit;