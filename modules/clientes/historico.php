<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

$cliente_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Buscar dados da cliente
$stmt_cli = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt_cli->execute([$cliente_id]);
$cliente = $stmt_cli->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    header('Location: index.php');
    exit;
}

// Buscar histórico de agendamentos concluídos ou marcados desta cliente
$stmt_hist = $pdo->prepare("SELECT a.*, p.nome as servico_nome, p.preco_venda 
                            FROM agenda a 
                            LEFT JOIN produtos p ON a.servico_id = p.id 
                            WHERE a.cliente_id = ? 
                            ORDER BY a.data_hora DESC");
$stmt_hist->execute([$cliente_id]);
$historico = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico da Cliente - Fernanda Mozer Depilação</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
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
            <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-users me-2"></i> Clientes</a></li>
            <li class="nav-item"><a class="nav-link" href="../produtos/index.php"><i class="fas fa-box me-2"></i> Produtos & Estoque</a></li>
            <li class="nav-item"><a class="nav-link" href="../caixa/index.php"><i class="fas fa-cash-register me-2"></i> Caixa / PDV</a></li>
            <li class="nav-item"><a class="nav-link" href="../financeiro/index.php"><i class="fas fa-wallet me-2"></i> Financeiro</a></li>
            <li class="nav-item"><a class="nav-link" href="../relatorios/index.php"><i class="fas fa-chart-bar me-2"></i> Relatórios</a></li>
            <li class="nav-item mt-4"><a class="nav-link text-danger" href="../../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
        </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="main-content">
        <div class="flex-grow-1">
            <?php include_once '../../includes/header.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-0"><i class="fas fa-history me-2 text-primary"></i> Histórico de Atendimentos</h4>
                    <small class="text-muted">Cliente: <strong><?= htmlspecialchars($cliente['nome']); ?></strong> (<?= htmlspecialchars($cliente['telefone'] ?? 'Sem telefone'); ?>)</small>
                </div>
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i> Voltar para Clientes
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Data e Hora</th>
                                    <th>Serviço Realizado</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($historico) > 0): ?>
                                    <?php foreach ($historico as $h): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($h['data_hora'])); ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($h['servico_nome'] ?? 'Serviço avulso'); ?></td>
                                            <td class="text-success fw-bold">R$ <?= number_format($h['preco_venda'] ?? 0, 2, ',', '.'); ?></td>
                                            <td>
                                                <span class="badge <?= ($h['status'] == 'Concluído') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                                    <?= $h['status'] ?? 'Agendado'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Nenhum atendimento registrado no histórico desta cliente.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php include_once '../../includes/footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>