<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome            = trim($_POST['nome']);
    $telefone        = trim($_POST['telefone']);
    $cpf             = trim($_POST['cpf']);
    $data_nascimento = !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null;
    $anamnese        = trim($_POST['anamnese']);

    if (!empty($nome)) {
        $sql = "INSERT INTO clientes (nome, telefone, cpf, data_nascimento, anamnese) 
                VALUES (:nome, :telefone, :cpf, :data_nascimento, :anamnese)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome'            => $nome,
            'telefone'        => $telefone,
            'cpf'             => $cpf,
            'data_nascimento' => $data_nascimento,
            'anamnese'        => $anamnese
        ]);
    }
}

header('Location: index.php?sucesso=1');
exit;
?>