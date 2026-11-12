<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

$page_title = "RELATÓRIOS";

// Filtros de data (padrão: mês atual)
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01');
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-t');
$tipo_relatorio = isset($_GET['tipo_relatorio']) ? $_GET['tipo_relatorio'] : 'financeiro';

// Consultas baseadas nos filtros
$dados_relatorio = [];
$total_periodo = 0;

if ($tipo_relatorio == 'financeiro') {
    // Buscar lançamentos financeiros no período
    $stmt = $pdo->prepare("SELECT * FROM financeiro WHERE data_lancamento BETWEEN ? AND ? ORDER BY data_lancamento DESC");
    $stmt->execute([$data_inicio, $data_fim]);
    $dados_relatorio = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($dados_relatorio as &$l) {
        if (strpos($l['descricao'], 'Venda PDV') !== false) {
            $l['tipo'] = 'Entrada';
        }
        if ($l['tipo'] == 'Entrada') {
            $total_periodo += floatval($l['valor']);
        }
    }
    unset($l);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Fernanda Mozer Depilação</title>
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
        @media print {
            .sidebar, .no-print {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Menu Lateral Fixo -->
    <nav class="sidebar no-print">
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
            <li class="nav-item"><a class="nav-link" href="../financeiro/index.php"><i class="fas fa-wallet me-2"></i> Financeiro</a></li>
            <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-chart-bar me-2"></i> Relatórios</a></li>
            <li class="nav-item mt-4"><a class="nav-link text-danger" href="../../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
        </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="main-content">
        <div>
            <div class="no-print">
                <?php include_once '../../includes/header.php'; ?>
                <?php include_once '../../includes/ticker.php'; ?>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-0"><i class="fas fa-chart-line me-2 text-primary"></i> Relatórios Gerenciais</h4>
                    <small class="text-muted">Acompanhe o desempenho do salão por período</small>
                </div>
                <button onclick="window.print()" class="btn btn-outline-dark rounded-pill px-4 shadow-sm no-print">
                    <i class="fas fa-print me-2"></i> Imprimir Relatório
                </button>
            </div>

            <!-- Filtros de Relatório -->
            <div class="card border-0 shadow-sm rounded-3 mb-4 no-print">
                <div class="card-body">
                    <form method="GET" action="index.php" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Tipo de Relatório</label>
                            <select name="tipo_relatorio" class="form-select">
                                <option value="financeiro" <?= ($tipo_relatorio == 'financeiro') ? 'selected' : ''; ?>>Entradas e Faturamento por Período</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Data Inicial</label>
                            <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($data_inicio); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Data Final</label>
                            <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($data_fim); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn text-white w-100 rounded-pill shadow-sm" style="background-color: #4a148c;">
                                <i class="fas fa-filter me-1"></i> Gerar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resumo do Período -->
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <div class="card bg-white p-4 shadow-sm border-0 rounded-3 d-flex flex-row justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold small">Faturamento Total no Período</span>
                            <h3 class="fw-bold text-success mb-0">R$ <?= number_format($total_periodo, 2, ',', '.'); ?></h3>
                            <small class="text-muted">De <?= date('d/m/Y', strtotime($data_inicio)); ?> até <?= date('d/m/Y', strtotime($data_fim)); ?></small>
                        </div>
                        <div>
                            <i class="fas fa-file-invoice-dollar fa-3x text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Resultados -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list me-2"></i> Detalhamento dos Lançamentos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Data</th>
                                    <th>Descrição</th>
                                    <th>Tipo</th>
                                    <th class="text-end">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($dados_relatorio) > 0): ?>
                                    <?php foreach ($dados_relatorio as $l): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($l['data_lancamento'])); ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($l['descricao']); ?></td>
                                            <td>
                                                <span class="badge <?= ($l['tipo'] == 'Entrada') ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?= $l['tipo']; ?>
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold <?= ($l['tipo'] == 'Entrada') ? 'text-success' : 'text-danger'; ?>">
                                                <?= ($l['tipo'] == 'Entrada') ? '+ ' : '- '; ?>R$ <?= number_format($l['valor'], 2, ',', '.'); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Nenhum registro encontrado para este período.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rodapé -->
        <div class="no-print">
            <?php include_once '../../includes/footer.php'; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>