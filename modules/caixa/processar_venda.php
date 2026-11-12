<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente_id      = !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
    $forma_pagamento = $_POST['forma_pagamento'] ?? 'Dinheiro';
    $produtos_ids    = $_POST['produtos_id'] ?? [];
    $quantidades     = $_POST['quantidades'] ?? [];
    $valor_total     = 0;

    // Buscar o nome do cliente se houver ID selecionado
    $nome_cliente = "Consumidor Geral";
    if ($cliente_id) {
        $stmt_cli = $pdo->prepare("SELECT nome FROM clientes WHERE id = ?");
        $stmt_cli->execute([$cliente_id]);
        $cli = $stmt_cli->fetch();
        if ($cli) {
            $nome_cliente = $cli['nome'];
        }
    }

    if (count($produtos_ids) > 0) {
        try {
            $pdo->beginTransaction();

            // 1. Calcula o total e abate o estoque dos produtos
            for ($i = 0; $i < count($produtos_ids); $i++) {
                $prod_id = (int)$produtos_ids[$i];
                $qtd = (int)($quantidades[$i] ?? 1);

                $stmt = $pdo->prepare("SELECT preco_venda, tipo, quantidade_estoque FROM produtos WHERE id = ?");
                $stmt->execute([$prod_id]);
                $produto = $stmt->fetch();

                if ($produto) {
                    $valor_total += floatval($produto['preco_venda']) * $qtd;

                    if ($produto['tipo'] == 'Produto') {
                        $novo_estoque = max(0, $produto['quantidade_estoque'] - $qtd);
                        $stmt_est = $pdo->prepare("UPDATE produtos SET quantidade_estoque = ? WHERE id = ?");
                        $stmt_est->execute([$novo_estoque, $prod_id]);
                    }
                }
            }

            // 2. Insere no financeiro incluindo o nome do cliente na descrição
            if ($valor_total > 0) {
                $descricao_venda = "Venda PDV - " . count($produtos_ids) . " item(ns) [Cliente: " . $nome_cliente . "] [Pgto: " . $forma_pagamento . "]";
                $tipo_lancamento = 'Entrada';
                
                $stmt_fin = $pdo->prepare("INSERT INTO financeiro (descricao, tipo, valor, data_lancamento) VALUES (?, ?, ?, CURDATE())");
                $stmt_fin->execute([$descricao_venda, $tipo_lancamento, $valor_total]);
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            header('Location: index.php?erro=1');
            exit;
        }
    }
}

header('Location: index.php?sucesso=1');
exit;
?>