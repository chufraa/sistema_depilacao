<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_id = (int)$_POST['cliente_id'];
    $servico_id = (int)$_POST['servico_id'];
    $data_hora  = $_POST['data_hora'];

    if ($cliente_id > 0 && $servico_id > 0 && !empty($data_hora)) {
        // Certifique-se de que a tabela 'agenda' existe no banco com os campos: cliente_id, servico_id, data_hora
        $sql = "INSERT INTO agenda (cliente_id, servico_id, data_hora) VALUES (:cliente_id, :servico_id, :data_hora)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'cliente_id' => $cliente_id,
            'servico_id' => $servico_id,
            'data_hora'  => $data_hora
        ]);
    }
}

header('Location: index.php?sucesso=1');
exit;
?>