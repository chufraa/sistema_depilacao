<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $descricao = trim($_POST['descricao']);
    $tipo = $_POST['tipo'];
    $valor = str_replace(',', '.', $_POST['valor']);
    $data_lancamento = $_POST['data_lancamento'];
    $data_vencimento = !empty($_POST['data_vencimento']) ? $_POST['data_vencimento'] : null;
    $status_pagamento = $_POST['status_pagamento'] ?? 'Pago';

    // Atualiza com o status de pagamento
    $stmt = $pdo->prepare("UPDATE financeiro SET descricao = ?, tipo = ?, valor = ?, data_lancamento = ?, data_vencimento = ?, status_pagamento = ? WHERE id = ?");
    $stmt->execute([$descricao, $tipo, $valor, $data_lancamento, $data_vencimento, $status_pagamento, $id]);

    header('Location: index.php?sucesso=1');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM financeiro WHERE id = ?");
$stmt->execute([$id]);
$lancamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lancamento) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Lançamento - Fernanda Mozer Depilação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { width: 260px; position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 20px; background-color: #4a148c; color: white; }
        .sidebar a { color: #d1c4e9; text-decoration: none; padding: 12px 15px; display: block; border-radius: 8px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background-color: #6a1b9a; color: #fff; }
        .main-content { margin-left: 260px; padding: 20px; min-height: 100vh; display: flex; flex-direction: column; }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div class="text-center py-2">
            <h4 class="fw-bold mb-0" style="color: #ffd54f;">✨ F. Mozer</h4>
            <small class="text-light">Depilação & Gestão</small>
        </div>
        <hr class="text-light my-3">
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="../../dashboard.php"><i class="fas fa-home me-2"></i> Início / Resumo</a></li>
            <li class="nav-item"><a class="nav-link" href="../agenda/index.php"><i class="fas fa-calendar-alt me-2"></i> Agenda</a></li>
            <li class="nav-item"><a class="nav-link" href="../clientes/index.php"><i class="fas fa-users me-2"></i> Clientes</a></li>
            <li class="nav-item"><a class="nav-link" href="../produtos/index.php"><i class="fas fa-box me-2"></i> Produtos & Estoque</a></li>
            <li class="nav-item"><a class="nav-link" href="../caixa/index.php"><i class="fas fa-cash-register me-2"></i> Caixa / PDV</a></li>
            <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-wallet me-2"></i> Financeiro</a></li>
            <li class="nav-item"><a class="nav-link" href="../relatorios/index.php"><i class="fas fa-chart-bar me-2"></i> Relatórios</a></li>
            <li class="nav-item mt-4"><a class="nav-link text-danger" href="../../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="flex-grow-1">
            <?php include_once '../../includes/header.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-0"><i class="fas fa-edit me-2 text-primary"></i> Editar Lançamento</h4>
                    <small class="text-muted">Altere as informações do registro financeiro</small>
                </div>
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i> Voltar
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <form action="editar.php" method="POST">
                        <input type="hidden" name="id" value="<?= $lancamento['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Descrição *</label>
                            <input type="text" name="descricao" class="form-control" value="<?= htmlspecialchars($lancamento['descricao']); ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Tipo *</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="Entrada" <?= ($lancamento['tipo'] == 'Entrada') ? 'selected' : ''; ?>>Entrada (Receita)</option>
                                    <option value="Saida" <?= ($lancamento['tipo'] == 'Saida') ? 'selected' : ''; ?>>Saída (Despesa / Conta)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Valor (R$) *</label>
                                <input type="number" step="0.01" name="valor" class="form-control" value="<?= $lancamento['valor']; ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Data do Lançamento *</label>
                                <input type="date" name="data_lancamento" class="form-control" value="<?= $lancamento['data_lancamento']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Data de Vencimento</label>
                                <input type="date" name="data_vencimento" class="form-control" value="<?= $lancamento['data_vencimento'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status de Pagamento *</label>
                            <select name="status_pagamento" class="form-select" required>
                                <option value="Pago" <?= (($lancamento['status_pagamento'] ?? 'Pago') == 'Pago') ? 'selected' : ''; ?>>Pago</option>
                                <option value="Pendente" <?= (($lancamento['status_pagamento'] ?? '') == 'Pendente') ? 'selected' : ''; ?>>Pendente (Conta a Pagar)</option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn text-white rounded-pill px-5 py-2" style="background-color: #6a1b9a;">
                                <i class="fas fa-save me-2"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php include_once '../../includes/footer.php'; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>