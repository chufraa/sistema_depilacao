<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id                 = (int)$_POST['id'];
    $nome               = trim($_POST['nome']);
    $tipo               = trim($_POST['tipo']); // Remove espaços em branco invisíveis
    $preco_venda        = str_replace(',', '.', trim($_POST['preco_venda']));
    $quantidade_estoque = (int)($_POST['quantidade_estoque'] ?? 0);

    try {
        // Verificar se foi enviada uma nova foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto_nome = 'prod_' . time() . '.' . $extensao;
            $pasta_destino = '../../uploads/';
            
            if (!is_dir($pasta_destino)) {
                mkdir($pasta_destino, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $pasta_destino . $foto_nome)) {
                // Atualiza com a nova foto
                $sql = "UPDATE produtos SET nome = :nome, tipo = :tipo, preco_venda = :preco_venda, quantidade_estoque = :quantidade_estoque, foto = :foto WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'nome'               => $nome,
                    'tipo'               => $tipo,
                    'preco_venda'        => $preco_venda,
                    'quantidade_estoque' => $quantidade_estoque,
                    'foto'               => $foto_nome,
                    'id'                 => $id
                ]);
            }
        } else {
            // Atualiza mantendo a foto anterior se nenhuma nova foi enviada
            $sql = "UPDATE produtos SET nome = :nome, tipo = :tipo, preco_venda = :preco_venda, quantidade_estoque = :quantidade_estoque WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nome'               => $nome,
                'tipo'               => $tipo,
                'preco_venda'        => $preco_venda,
                'quantidade_estoque' => $quantidade_estoque,
                'id'                 => $id
            ]);
        }

        header('Location: index.php?sucesso=1');
        exit;

    } catch (Exception $e) {
        // Se houver algum erro no banco, redireciona informando o erro
        header('Location: index.php?erro=1');
        exit;
    }
}

header('Location: index.php');
exit;
?>