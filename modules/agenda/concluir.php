<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // 1. Buscar os dados do agendamento, incluindo o preço e o nome do cliente e do serviço
    $stmt = $pdo->prepare("SELECT a.*, c.nome as cliente_nome, p.nome as servico_nome, p.preco_venda 
                           FROM agenda a 
                           LEFT JOIN clientes c ON a.cliente_id = c.id 
                           LEFT JOIN produtos p ON a.servico_id = p.id 
                           WHERE a.id = ?");
    $stmt->execute([$id]);
    $ag = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ag) {
        // 2. Atualizar o status do agendamento para Concluído
        $update = $pdo->prepare("UPDATE agenda SET status = 'Concluído' WHERE id = ?");
        $update->execute([$id]);

        // 3. Inserir automaticamente no módulo Financeiro / Caixa
        $descricao = "Atendimento Agenda: " . ($ag['servico_nome'] ?? 'Serviço') . " [Cliente: " . ($ag['cliente_nome'] ?? 'Balcão') . "]";
        $valor = $ag['preco_venda'] ?? 0;
        $data_hoje = date('Y-m-d');
        $tipo = 'Entrada';

        $fin = $pdo->prepare("INSERT INTO financeiro (descricao, tipo, valor, data_lancamento) VALUES (?, ?, ?, ?)");
        $fin->execute([$descricao, $tipo, $valor, $data_hoje]);
    }
}

header('Location: index.php?sucesso=1');
exit;