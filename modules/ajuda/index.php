<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}
require_once '../../config/database.php';
$page_title = "AJUDA & MANUAL";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajuda e Manual - Fernanda Mozer Depilação</title>
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
            <li class="nav-item"><a class="nav-link" href="../fornecedores/index.php"><i class="fas fa-truck me-2"></i> Fornecedores</a></li>
            <li class="nav-item"><a class="nav-link" href="../caixa/index.php"><i class="fas fa-cash-register me-2"></i> Caixa / PDV</a></li>
            <li class="nav-item"><a class="nav-link" href="../financeiro/index.php"><i class="fas fa-wallet me-2"></i> Financeiro</a></li>
            <li class="nav-item"><a class="nav-link" href="../relatorios/index.php"><i class="fas fa-chart-bar me-2"></i> Relatórios</a></li>
            <li class="nav-item"><a class="nav-link" href="../usuario/index.php"><i class="fas fa-user-cog me-2"></i> Meu Perfil</a></li>
            <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-question-circle me-2"></i> Ajuda & Manual</a></li>
            <li class="nav-item mt-4"><a class="nav-link text-danger" href="../../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="flex-grow-1">
            <?php include_once '../../includes/header.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-0"><i class="fas fa-book me-2 text-success"></i> Manual e Funcionalidades do Sistema</h4>
                    <small class="text-muted">Guia completo de operação do ERP/PDV Fernanda Mozer</small>
                </div>
            </div>

            <div class="row g-4">
                <!-- Agenda -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body">
                            <h5 class="fw-bold text-primary mb-3"><i class="fas fa-calendar-alt me-2"></i> 1. Módulo Agenda</h5>
                            <p class="text-muted">Gerencie os horários dos procedimentos de depilação. Permite filtrar por data, marcar status (Agendado ou Concluído) e aciona automaticamente o registro financeiro ao concluir um atendimento.</p>
                        </div>
                    </div>
                </div>

                <!-- Clientes e CRM -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body">
                            <h5 class="fw-bold text-warning mb-3"><i class="fas fa-users me-2"></i> 2. Módulo Clientes & CRM</h5>
                            <p class="text-muted">Cadastro completo de clientes com histórico individual de atendimento. O painel principal exibe automaticamente os aniversariantes do mês para ações de fidelização.</p>
                        </div>
                    </div>
                </div>

                <!-- Caixa e PDV -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body">
                            <h5 class="fw-bold text-success mb-3"><i class="fas fa-cash-register me-2"></i> 3. Módulo Caixa / PDV</h5>
                            <p class="text-muted">Frente de caixa ágil para registro de vendas de serviços e produtos avulsos, gerando automaticamente entradas no financeiro e baixas no estoque.</p>
                        </div>
                    </div>
                </div>

                <!-- Financeiro -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body">
                            <h5 class="fw-bold text-danger mb-3"><i class="fas fa-wallet me-2"></i> 4. Módulo Financeiro & Contas</h5>
                            <p class="text-muted">Controle total de Entradas, Saídas e Contas a Pagar com datas de vencimento, status (Pago/Pendente), botão rápido de quitação e edição completa de lançamentos.</p>
                        </div>
                    </div>
                </div>

                <!-- Produtos e Estoque -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body">
                            <h5 class="fw-bold text-purple mb-3" style="color: #7b1fa2;"><i class="fas fa-box me-2"></i> 5. Produtos & Estoque</h5>
                            <p class="text-muted">Controle de itens utilizados e vendidos no salão com paginação, alertas de quantidade e cadastro simplificado.</p>
                        </div>
                    </div>
                </div>

                <!-- Fornecedores -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body">
                            <h5 class="fw-bold text-info mb-3"><i class="fas fa-truck me-2"></i> 6. Gestão de Fornecedores</h5>
                            <p class="text-muted">Cadastro de marcas e distribuidores com integração direta de chat via WhatsApp, CNPJ, paginação e dados de contato.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?php include_once '../../includes/footer.php'; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>