<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

$page_title = "AGENDA";

// Filtro por data (padrão: dia de hoje)
$filtro_data = isset($_GET['filtro_data']) ? $_GET['filtro_data'] : date('Y-m-d');

// Buscar clientes para o modal
$clientes = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome ASC")->fetchAll();

// Buscar produtos/serviços para o modal
$servicos = $pdo->query("SELECT id, nome, preco_venda FROM produtos ORDER BY nome ASC")->fetchAll();

// Buscar agendamentos filtrados pela data selecionada
$sql = "SELECT a.*, c.nome as cliente_nome, p.nome as servico_nome, p.preco_venda as servico_preco
        FROM agenda a 
        LEFT JOIN clientes c ON a.cliente_id = c.id 
        LEFT JOIN produtos p ON a.servico_id = p.id 
        WHERE DATE(a.data_hora) = ?
        ORDER BY a.data_hora ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$filtro_data]);
$agendamentos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda - Fernanda Mozer Depilação</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
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
            <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-calendar-alt me-2"></i> Agenda</a></li>
            <li class="nav-item"><a class="nav-link" href="../clientes/index.php"><i class="fas fa-users me-2"></i> Clientes</a></li>
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
            <?php include_once '../../includes/ticker.php'; ?>

            <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Operação realizada com sucesso!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-0"><i class="fas fa-calendar-check me-2 text-primary"></i> Agenda de Atendimentos</h4>
                    <small class="text-muted">Acompanhe os horários e status das clientes por dia</small>
                </div>
                <button class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #6a1b9a;" data-bs-toggle="modal" data-bs-target="#modalNovoAgendamento">
                    <i class="fas fa-plus me-2"></i> Novo Agendamento
                </button>
            </div>

            <!-- Filtro por Data -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body">
                    <form method="GET" action="index.php" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Filtrar por Data do Atendimento</label>
                            <input type="date" name="filtro_data" class="form-control" value="<?= htmlspecialchars($filtro_data); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn text-white w-100 rounded-pill shadow-sm" style="background-color: #4a148c;">
                                <i class="fas fa-filter me-1"></i> Filtrar
                            </button>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="badge bg-light text-dark p-2 border fs-6">
                                Exibindo agendamentos para: <strong><?= date('d/m/Y', strtotime($filtro_data)); ?></strong>
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabela de Agendamentos -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Horário</th>
                                    <th>Cliente</th>
                                    <th>Serviço</th>
                                    <th>Valor Estimado</th>
                                    <th>Status</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($agendamentos) > 0): ?>
                                    <?php foreach ($agendamentos as $ag): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><i class="far fa-clock me-1 text-muted"></i> <?= date('H:i', strtotime($ag['data_hora'])); ?></td>
                                            <td><?= htmlspecialchars($ag['cliente_nome'] ?? 'Cliente não encontrado'); ?></td>
                                            <td><?= htmlspecialchars($ag['servico_nome'] ?? 'Serviço não encontrado'); ?></td>
                                            <td class="text-success fw-bold">R$ <?= number_format($ag['servico_preco'] ?? 0, 2, ',', '.'); ?></td>
                                            <td>
                                                <?php 
                                                    $status = $ag['status'] ?? 'Agendado';
                                                    $badge_bg = ($status == 'Concluído') ? 'bg-success' : 'bg-warning text-dark';
                                                ?>
                                                <span class="badge <?= $badge_bg; ?>"><?= $status; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($status != 'Concluído'): ?>
                                                    <a href="concluir.php?id=<?= $ag['id']; ?>" class="btn btn-outline-success btn-sm rounded-pill px-3 me-1" title="Marcar como Concluído">
                                                        <i class="fas fa-check me-1"></i> Concluir
                                                    </a>
                                                <?php endif; ?>
                                                <a href="excluir.php?id=<?= $ag['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('Deseja cancelar este agendamento?');" title="Cancelar">
                                                    <i class="fas fa-times me-1"></i> Cancelar
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Nenhum agendamento encontrado para esta data.</td>
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

    <!-- Modal Novo Agendamento -->
    <div class="modal fade" id="modalNovoAgendamento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background-color: #4a148c;">
                    <h5 class="modal-title fw-bold"><i class="fas fa-calendar-plus me-2"></i> Novo Agendamento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="salvar.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Cliente *</label>
                            <select name="cliente_id" class="form-select" required>
                                <option value="">Selecione a cliente...</option>
                                <?php foreach ($clientes as $cli): ?>
                                    <option value="<?= $cli['id']; ?>"><?= htmlspecialchars($cli['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Serviço *</label>
                            <select name="servico_id" class="form-select" required>
                                <option value="">Selecione o serviço...</option>
                                <?php foreach ($servicos as $srv): ?>
                                    <option value="<?= $srv['id']; ?>"><?= htmlspecialchars($srv['nome']); ?> (R$ <?= number_format($srv['preco_venda'], 2, ',', '.'); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Data e Hora do Atendimento *</label>
                            <input type="datetime-local" name="data_hora" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #6a1b9a;">Confirmar Agendamento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>