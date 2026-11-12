<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

$page_title = "FINANCEIRO";

// Filtros
$tipo_filtro = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$status_filtro = isset($_GET['status']) ? $_GET['status'] : '';
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';

// Construir consulta dinâmica
$sql = "SELECT * FROM financeiro WHERE 1=1";
$params = [];

if (!empty($tipo_filtro)) {
    $sql .= " AND tipo = ?";
    $params[] = $tipo_filtro;
}
if (!empty($status_filtro)) {
    $sql .= " AND status_pagamento = ?";
    $params[] = $status_filtro;
}
if (!empty($data_inicio) && !empty($data_fim)) {
    $sql .= " AND data_lancamento BETWEEN ? AND ?";
    $params[] = $data_inicio;
    $params[] = $data_fim;
}

$sql .= " ORDER BY data_lancamento DESC, id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lancamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totais gerais
$total_entradas = 0;
$total_saidas = 0;
$total_pendente = 0;

foreach ($lancamentos as $l) {
    $tipo = trim($l['tipo']);
    if (strpos($l['descricao'], 'Venda PDV') !== false) {
        $tipo = 'Entrada';
    }

    if ($tipo == 'Entrada') {
        $total_entradas += floatval($l['valor']);
    } else {
        $total_saidas += floatval($l['valor']);
        if (($l['status_pagamento'] ?? 'Pago') == 'Pendente') {
            $total_pendente += floatval($l['valor']);
        }
    }
}
$saldo_liquido = $total_entradas - $total_saidas;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financeiro - Fernanda Mozer Depilação</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            background-color: #f3f4f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        .sidebar {
            width: 260px;
            position: fixed;
            top: 0; bottom: 0; left: 0;
            z-index: 100;
            padding: 20px;
            background-color: #4a148c;
            color: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar a {
            color: #d1c4e9;
            text-decoration: none;
            padding: 12px 15px;
            display: block;
            border-radius: 8px;
            margin-bottom: 5px;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #6a1b9a;
            color: #fff;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            padding-bottom: 80px; /* Garante o respiro de segurança perfeito para o rodapé */
        }
    </style>
</head>
<body>

    <!-- Menu Lateral Fixo -->
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

    <!-- Conteúdo Principal -->
    <div class="main-content">
        <div>
            <?php include_once '../../includes/header.php'; ?>
            <?php include_once '../../includes/ticker.php'; ?>

            <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Operação realizada com sucesso!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-0"><i class="fas fa-wallet me-2 text-success"></i> Controle Financeiro & Contas</h4>
                    <small class="text-muted">Acompanhe entradas, despesas pagas e contas a pagar</small>
                </div>
                <button class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #4a148c;" data-bs-toggle="modal" data-bs-target="#modalNovoLancamento">
                    <i class="fas fa-plus me-2"></i> Novo Lançamento / Despesa
                </button>
            </div>

            <!-- Cards Resumo -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card bg-success text-white p-3 shadow-sm border-0 rounded-3">
                        <h6 class="text-white-50">Total de Entradas</h6>
                        <h3 class="fw-bold mb-0">R$ <?= number_format($total_entradas, 2, ',', '.'); ?></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white p-3 shadow-sm border-0 rounded-3">
                        <h6 class="text-white-50">Total de Saídas / Despesas</h6>
                        <h3 class="fw-bold mb-0">R$ <?= number_format($total_saidas, 2, ',', '.'); ?></h3>
                        <?php if ($total_pendente > 0): ?>
                            <small class="text-warning mt-1 d-block"><i class="fas fa-exclamation-triangle"></i> Despesas Pendentes: R$ <?= number_format($total_pendente, 2, ',', '.'); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-primary text-white p-3 shadow-sm border-0 rounded-3">
                        <h6 class="text-white-50">Saldo Líquido em Caixa</h6>
                        <h3 class="fw-bold mb-0">R$ <?= number_format($saldo_liquido, 2, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body">
                    <form method="GET" action="index.php" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Tipo</label>
                            <select name="tipo" class="form-select">
                                <option value="">Todos os Tipos</option>
                                <option value="Entrada" <?= ($tipo_filtro == 'Entrada') ? 'selected' : ''; ?>>Entrada</option>
                                <option value="Saida" <?= ($tipo_filtro == 'Saida') ? 'selected' : ''; ?>>Saída / Despesa</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Status do Pagamento</label>
                            <select name="status" class="form-select">
                                <option value="">Todos os Status</option>
                                <option value="Pago" <?= ($status_filtro == 'Pago') ? 'selected' : ''; ?>>Pago</option>
                                <option value="Pendente" <?= ($status_filtro == 'Pendente') ? 'selected' : ''; ?>>Pendente (A Pagar)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">Início</label>
                            <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($data_inicio); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">Fim</label>
                            <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($data_fim); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn text-white w-100 rounded-pill shadow-sm" style="background-color: #4a148c;">
                                <i class="fas fa-filter me-1"></i> Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabela de Lançamentos -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Data Lançamento</th>
                                    <th>Vencimento</th>
                                    <th>Descrição</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($lancamentos) > 0): ?>
                                    <?php foreach ($lancamentos as $l): ?>
                                        <?php 
                                            $tipo = $l['tipo'];
                                            if (strpos($l['descricao'], 'Venda PDV') !== false) { $tipo = 'Entrada'; }
                                            $status_pag = $l['status_pagamento'] ?? 'Pago';
                                            $vencimento_fmt = (!empty($l['data_vencimento']) && $l['data_vencimento'] != '0000-00-00' && strpos($l['data_vencimento'], '-') !== false) ? date('d/m/Y', strtotime($l['data_vencimento'])) : '-';
                                        ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($l['data_lancamento'])); ?></td>
                                            <td><?= $vencimento_fmt; ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($l['descricao']); ?></td>
                                            <td>
                                                <span class="badge <?= ($tipo == 'Entrada') ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?= $tipo; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= ($status_pag == 'Pago') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                                    <?= $status_pag; ?>
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold <?= ($tipo == 'Entrada') ? 'text-success' : 'text-danger'; ?>">
                                                <?= ($tipo == 'Entrada') ? '+ ' : '- '; ?>R$ <?= number_format($l['valor'], 2, ',', '.'); ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($status_pag == 'Pendente'): ?>
                                                    <a href="pagar.php?id=<?= $l['id']; ?>" class="btn btn-outline-success btn-sm rounded-pill px-2 me-1" title="Marcar como Pago">
                                                        <i class="fas fa-check"></i> Pagar
                                                    </a>
                                                <?php endif; ?>
                                                <a href="editar.php?id=<?= $l['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2 me-1" title="Editar">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <a href="excluir.php?id=<?= $l['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill px-2" onclick="return confirm('Deseja excluir este lançamento?');" title="Excluir">
                                                    <i class="fas fa-trash-alt"></i> Excluir
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Nenhum lançamento encontrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rodapé -->
        <?php include_once '../../includes/footer.php'; ?>
    </div>

    <!-- Modal Novo Lançamento -->
    <div class="modal fade" id="modalNovoLancamento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background-color: #4a148c;">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice-dollar me-2"></i> Novo Lançamento / Conta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="salvar.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Descrição *</label>
                            <input type="text" name="descricao" class="form-control" placeholder="Ex: Aluguel, Conta de Luz, Compra de Cera..." required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Tipo *</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="Entrada">Entrada (Receita)</option>
                                    <option value="Saida" selected>Saída (Despesa / Conta)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Valor (R$) *</label>
                                <input type="number" step="0.01" name="valor" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Data do Lançamento *</label>
                                <input type="date" name="data_lancamento" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Data de Vencimento</label>
                                <input type="date" name="data_vencimento" class="form-control" value="<?= date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status de Pagamento *</label>
                            <select name="status_pagamento" class="form-select" required>
                                <option value="Pago">Pago</option>
                                <option value="Pendente">Pendente (Conta a Pagar)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #6a1b9a;">Salvar Lançamento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>