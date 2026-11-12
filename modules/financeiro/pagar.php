<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE financeiro SET status_pagamento = 'Pago' WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: index.php?sucesso=1');
exit;