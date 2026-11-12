<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

$page_title = "FORNECEDORES";

// Processar exclusão
if (isset($_GET['excluir'])) {
    $id_excluir = (int)$_GET['excluir'];
    $stmt_del = $pdo->prepare("DELETE FROM fornecedores WHERE id = ?");
    $stmt_del->execute([$id_excluir]);
    header('Location: index.php?sucesso=1');
    exit;
}

// Processar cadastro de novo fornecedor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar_fornecedor'])) {
    $nome_empresa = trim($_POST['nome_empresa']);
    $contato_nome = trim($_POST['contato_nome']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $cnpj = trim($_POST['cnpj']);
    $observacoes = trim($_POST['observacoes']);

    $stmt_ins = $pdo->prepare("INSERT INTO fornecedores (nome_empresa, contato_nome, telefone, email, cnpj, observacoes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_ins->execute([$nome_empresa, $contato_nome, $telefone, $email, $cnpj, $observacoes]);

    header('Location: index.php?sucesso=1');
    exit;
}

// Configuração da Paginação
$limite = 8; // Quantos por página
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_atual < 1) $pagina_atual = 1;
$inicio = ($pagina_atual - 1) * $limite;

// Contar total de registros
$total_registros = $pdo->query("SELECT COUNT(*) FROM fornecedores")->fetchColumn();
$total_paginas = ceil($total_registros / $limite);

// Buscar fornecedores com limite e deslocamento
$stmt = $pdo->prepare("SELECT * FROM fornecedores ORDER BY nome_empresa ASC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limite, PDO::PARAM_INT);
$stmt->bindValue(2, $inicio, PDO::PARAM_INT);
$stmt->execute();
$fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fornecedores - Fernanda Mozer Depilação</title>
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
            <li class="nav-item"><a class="nav-link" href="../clientes/index.php"><i class="fas fa-users me-2"></i> Clientes</a></li>
            <li class="nav-item"><a class="nav-link" href="../produtos/index.php"><i class="fas fa-box me-2"></i> Produtos & Estoque</a></li>
            <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-truck me-2"></i> Fornecedores</a></li>
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

            <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Operação realizada com sucesso!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-0"><i class="fas fa-truck me-2 text-primary"></i> Gestão de Fornecedores</h4>
                    <small class="text-muted">Cadastre, edite e acompanhe os parceiros de fornecimento</small>
                </div>
                <button class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #4a148c;" data-bs-toggle="modal" data-bs-target="#modalNovoFornecedor">
                    <i class="fas fa-plus me-2"></i> Novo Fornecedor
                </button>
            </div>

            <!-- Tabela de Fornecedores -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Empresa / Razão Social</th>
                                    <th>Pessoa de Contato</th>
                                    <th>Telefone</th>
                                    <th>E-mail</th>
                                    <th>CNPJ</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($fornecedores) > 0): ?>
                                    <?php foreach ($fornecedores as $f): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($f['nome_empresa']); ?></td>
                                            <td><?= htmlspecialchars($f['contato_nome'] ?? '-'); ?></td>
                                            <td>
                                                <?php if (!empty($f['telefone'])): ?>
                                                    <a href="https://wa.me/55<?= preg_replace('/[^0-9]/', '', $f['telefone']); ?>" target="_blank" class="text-decoration-none text-success fw-semibold">
                                                        <i class="fab fa-whatsapp me-1"></i> <?= htmlspecialchars($f['telefone']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($f['email'] ?? '-'); ?></td>
                                            <td><?= htmlspecialchars($f['cnpj'] ?? '-'); ?></td>
                                            <td class="text-center">
                                                <a href="editar.php?id=<?= $f['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2 me-1" title="Editar">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <a href="index.php?excluir=<?= $f['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill px-2" onclick="return confirm('Deseja realmente remover este fornecedor?');" title="Excluir">
                                                    <i class="fas fa-trash-alt"></i> Excluir
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Nenhum fornecedor cadastrado ainda.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Paginação -->
            <?php if ($total_paginas > 1): ?>
                <nav aria-label="Navegação de páginas">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?= ($pagina_atual == $i) ? 'active' : ''; ?>">
                                <a class="page-link <?= ($pagina_atual == $i) ? 'text-white' : 'text-dark'; ?>" <?= ($pagina_atual == $i) ? 'style="background-color: #4a148c; border-color: #4a148c;"' : ''; ?> href="index.php?pagina=<?= $i; ?>">
                                    <?= $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        </div>

        <?php include_once '../../includes/footer.php'; ?>
    </div>

    <!-- Modal Novo Fornecedor -->
    <div class="modal fade" id="modalNovoFornecedor" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background-color: #4a148c;">
                    <h5 class="modal-title fw-bold"><i class="fas fa-truck me-2"></i> Cadastrar Fornecedor</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="index.php" method="POST">
                    <input type="hidden" name="cadastrar_fornecedor" value="1">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nome da Empresa / Marca *</label>
                            <input type="text" name="nome_empresa" class="form-control" placeholder="Ex: Ceras Mozer & Cia" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nome do Contato</label>
                                <input type="text" name="contato_nome" class="form-control" placeholder="Ex: Roberto Vendas">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Telefone / WhatsApp</label>
                                <input type="text" name="telefone" class="form-control" placeholder="(00) 00000-0000">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">E-mail</label>
                                <input type="email" name="email" class="form-control" placeholder="contato@empresa.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">CNPJ</label>
                                <input type="text" name="cnpj" class="form-control" placeholder="00.000.000/0001-00">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Observações / Produtos Fornecidos</label>
                            <textarea name="observacoes" class="form-control" rows="2" placeholder="Ex: Fornecedor principal de cera quente e papel depilatório."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #6a1b9a;">Salvar Fornecedor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>