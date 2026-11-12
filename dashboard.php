<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Inclui a conexão com o banco de dados
require_once 'config/database.php';

$page_title = "INÍCIO";

// Buscar contagens e valores reais do banco de dados
try {
    $total_clientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    $total_agendamentos = $pdo->query("SELECT COUNT(*) FROM agenda")->fetchColumn();
    $total_produtos = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
    
    // Calcula o saldo líquido do financeiro para exibir no Caixa Atual (Entradas - Saídas) com blindagem
    $stmt_fin = $pdo->query("SELECT * FROM financeiro");
    $todos_fin = $stmt_fin->fetchAll(PDO::FETCH_ASSOC);
    
    $total_entradas = 0;
    $total_saidas = 0;

    foreach ($todos_fin as $f) {
        $tipo = trim($f['tipo']);
        if (strpos($f['descricao'], 'Venda PDV') !== false) {
            $tipo = 'Entrada';
        }

        if (strcasecmp($tipo, 'Entrada') === 0) {
            $total_entradas += floatval($f['valor']);
        } else {
            $total_saidas += floatval($f['valor']);
        }
    }
    $caixa_atual = $total_entradas - $total_saidas;

    // Buscar aniversariantes do mês atual
    $mes_atual = date('m');
    $stmt_aniv = $pdo->prepare("SELECT nome, telefone, data_nascimento FROM clientes WHERE MONTH(data_nascimento) = ? ORDER BY DAY(data_nascimento) ASC");
    $stmt_aniv->execute([$mes_atual]);
    $aniversariantes = $stmt_aniv->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $total_clientes = 0;
    $total_agendamentos = 0;
    $total_produtos = 0;
    $caixa_atual = 0.00;
    $aniversariantes = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Fernanda Mozer Depilação</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome para ícones -->
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
            top: 0;
            bottom: 0;
            left: 0;
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
            transition: all 0.2s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #6a1b9a;
            color: #fff;
        }
        .main-content {
            margin-left: 260px; /* Compensa a largura do menu fixo */
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .card-clicavel {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-clicavel:hover {
            transform: translateY(-4px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
        @media (max-width: 992px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            .main-content {
                margin-left: 0;
            }
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
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php"><i class="fas fa-home me-2"></i> Início / Resumo</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="modules/agenda/index.php"><i class="fas fa-calendar-alt me-2"></i> Agenda</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="modules/clientes/index.php"><i class="fas fa-users me-2"></i> Clientes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="modules/produtos/index.php"><i class="fas fa-box me-2"></i> Produtos & Estoque</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="modules/fornecedores/index.php"><i class="fas fa-truck me-2"></i> Fornecedores</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="modules/caixa/index.php"><i class="fas fa-cash-register me-2"></i> Caixa / PDV</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="modules/financeiro/index.php"><i class="fas fa-wallet me-2"></i> Financeiro</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="modules/relatorios/index.php"><i class="fas fa-chart-bar me-2"></i> Relatórios</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="modules/usuario/index.php"><i class="fas fa-user-cog me-2"></i> Meu Perfil</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="modules/ajuda/index.php"><i class="fas fa-question-circle me-2"></i> Ajuda & Manual</a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a>
            </li>
        </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="main-content">
        
        <div class="flex-grow-1">
            <!-- 1. Cabeçalho Superior -->
            <?php include_once 'includes/header.php'; ?>

            <!-- Título da Área -->
            <div class="mb-3">
                <h4 class="fw-bold mb-0 text-dark"><i class="fas fa-tachometer-alt me-2 text-success"></i>Painel de Controle</h4>
                <small class="text-muted">Visão geral e desempenho operacional da loja.</small>
            </div>

            <!-- 2. Letreiro Digital (PDV & ERP) -->
            <?php include_once 'includes/ticker.php'; ?>

            <!-- 4 Cards Principais Clicáveis -->
            <div class="row text-white g-3 mb-4">
                <!-- Card de Agendamentos -->
                <div class="col-md-3">
                    <a href="modules/agenda/index.php" class="text-decoration-none">
                        <div class="card bg-primary p-3 shadow-sm border-0 rounded-3 text-white h-100 card-clicavel">
                            <h6 class="text-white-50 fs-6">Agendamentos</h6>
                            <h2 class="fw-bold mb-0"><?= $total_agendamentos; ?></h2>
                            <small class="text-white-50 mt-2" style="font-size: 0.75rem;"><i class="fas fa-arrow-right me-1"></i> Acessar Agenda</small>
                        </div>
                    </a>
                </div>

                <!-- Card de Caixa / Financeiro -->
                <div class="col-md-3">
                    <a href="modules/financeiro/index.php" class="text-decoration-none">
                        <div class="card bg-success p-3 shadow-sm border-0 rounded-3 text-white h-100 card-clicavel">
                            <h6 class="text-white-50 fs-6">Caixa Atual</h6>
                            <h2 class="fw-bold mb-0 fs-3">R$ <?= number_format($caixa_atual, 2, ',', '.'); ?></h2>
                            <small class="text-white-50 mt-2" style="font-size: 0.75rem;"><i class="fas fa-arrow-right me-1"></i> Acessar Financeiro</small>
                        </div>
                    </a>
                </div>

                <!-- Card de Clientes -->
                <div class="col-md-3">
                    <a href="modules/clientes/index.php" class="text-decoration-none">
                        <div class="card bg-warning text-dark p-3 shadow-sm border-0 rounded-3 h-100 card-clicavel">
                            <h6 class="text-dark-50 fs-6">Clientes</h6>
                            <h2 class="fw-bold mb-0"><?= $total_clientes; ?></h2>
                            <small class="text-muted mt-2" style="font-size: 0.75rem;"><i class="fas fa-arrow-right me-1"></i> Gerenciar Clientes</small>
                        </div>
                    </a>
                </div>

                <!-- Card de Produtos & Estoque -->
                <div class="col-md-3">
                    <a href="modules/produtos/index.php" class="text-decoration-none">
                        <div class="card text-white p-3 shadow-sm border-0 rounded-3 h-100 card-clicavel" style="background-color: #7b1fa2;">
                            <h6 class="text-white-50 fs-6">Produtos & Serviços</h6>
                            <h2 class="fw-bold mb-0"><?= $total_produtos; ?></h2>
                            <small class="text-white-50 mt-2" style="font-size: 0.75rem;"><i class="fas fa-arrow-right me-1"></i> Gerenciar Estoque</small>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Bloco de Aniversariantes do Mês -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-birthday-cake me-2 text-warning"></i> Aniversariantes do Mês</h5>
                    <span class="badge bg-warning text-dark"><?= count($aniversariantes); ?> cliente(s) este mês</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Data de Aniversário</th>
                                    <th>Cliente</th>
                                    <th>Telefone / Contato</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($aniversariantes) > 0): ?>
                                    <?php foreach ($aniversariantes as $ani): ?>
                                        <tr>
                                            <td class="fw-bold text-primary">
                                                <i class="fas fa-gift me-1"></i> <?= date('d/m', strtotime($ani['data_nascimento'])); ?>
                                            </td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($ani['nome']); ?></td>
                                            <td><?= htmlspecialchars($ani['telefone'] ?? 'Não informado'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">Nenhum aniversário cadastrado para este mês.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- 3. Rodapé Integrado -->
        <?php include_once 'includes/footer.php'; ?>

    </div>

</body>
</html>