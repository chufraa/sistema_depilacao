<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

$page_title = "PRODUTOS & SERVIÇOS";

// Paginação
$limite = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) $pagina = 1;
$inicio = ($pagina - 1) * $limite;

// Contar total de itens
$total_itens = $pdo->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
$total_paginas = ceil($total_itens / $limite);

// Buscar itens limitados para a página atual
$stmt = $pdo->prepare("SELECT * FROM produtos ORDER BY id DESC LIMIT :inicio, :limite");
$stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
$stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
$stmt->execute();
$produtos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos e Serviços - Fernanda Mozer Depilação</title>
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
            padding-bottom: 80px; /* Garante o respiro de segurança para o rodapé */
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
            <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-box me-2"></i> Produtos & Estoque</a></li>
            <li class="nav-item"><a class="nav-link" href="../caixa/index.php"><i class="fas fa-cash-register me-2"></i> Caixa / PDV</a></li>
            <li class="nav-item"><a class="nav-link" href="../financeiro/index.php"><i class="fas fa-wallet me-2"></i> Financeiro</a></li>
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
                    <h4 class="fw-bold text-dark mb-0"><i class="fas fa-box me-2 text-warning"></i> Gestão de Produtos & Serviços</h4>
                    <small class="text-muted">Total de <?= $total_itens; ?> itens cadastrados no sistema</small>
                </div>
                <button class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #6a1b9a;" data-bs-toggle="modal" data-bs-target="#modalNovoProduto">
                    <i class="fas fa-plus me-2"></i> Novo Item
                </button>
            </div>

            <!-- Tabela de Produtos / Serviços -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">ID</th>
                                    <th>Foto / Ícone</th>
                                    <th>Nome do Item</th>
                                    <th>Tipo</th>
                                    <th>Preço de Venda</th>
                                    <th>Estoque</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($produtos) > 0): ?>
                                    <?php foreach ($produtos as $p): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-muted">#<?= $p['id']; ?></td>
                                            <td>
                                                <?php if (!empty($p['foto']) && file_exists("../../uploads/" . $p['foto'])): ?>
                                                    <img src="../../uploads/<?= $p['foto']; ?>" alt="Foto" class="rounded-circle shadow-sm" style="width: 38px; height: 38px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 38px; height: 38px; background-color: <?= ($p['tipo'] == 'Serviço') ? '#6a1b9a' : '#2e7d32'; ?>;">
                                                        <i class="fas <?= ($p['tipo'] == 'Serviço') ? 'fa-spa' : 'fa-box-open'; ?> fs-6"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($p['nome']); ?></td>
                                            <td>
                                                <span class="badge" style="<?= ($p['tipo'] == 'Serviço') ? 'background-color: #7b1fa2;' : 'background-color: #2e7d32;'; ?>">
                                                    <?= $p['tipo']; ?>
                                                </span>
                                            </td>
                                            <td class="text-success fw-bold">R$ <?= number_format($p['preco_venda'], 2, ',', '.'); ?></td>
                                            <td><?= ($p['tipo'] == 'Serviço') ? '-' : $p['quantidade_estoque']; ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3 me-1" data-bs-toggle="modal" data-bs-target="#modalEditar<?= $p['id']; ?>">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <a href="excluir.php?id=<?= $p['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('Deseja realmente excluir este item?');">
                                                    <i class="fas fa-trash-alt"></i> Excluir
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- Modal Editar -->
                                        <div class="modal fade" id="modalEditar<?= $p['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header text-white" style="background-color: #4a148c;">
                                                        <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i> Editar Item #<?= $p['id']; ?></h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="editar.php" method="POST" enctype="multipart/form-data">
                                                        <input type="hidden" name="id" value="<?= $p['id']; ?>">
                                                        <div class="modal-body text-start">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Nome do Item *</label>
                                                                <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($p['nome']); ?>" required>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label fw-semibold">Tipo *</label>
                                                                    <select name="tipo" class="form-select" required>
                                                                        <option value="Serviço" <?= ($p['tipo'] == 'Serviço') ? 'selected' : ''; ?>>Serviço</option>
                                                                        <option value="Produto" <?= ($p['tipo'] == 'Produto') ? 'selected' : ''; ?>>Produto</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="form-label fw-semibold">Preço de Venda (R$) *</label>
                                                                    <input type="text" name="preco_venda" class="form-control" value="<?= $p['preco_venda']; ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Quantidade em Estoque (Apenas Produtos)</label>
                                                                <input type="number" name="quantidade_estoque" class="form-control" value="<?= $p['quantidade_estoque']; ?>">
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Alterar Foto / Imagem</label>
                                                                <input type="file" name="foto" class="form-control" accept="image/*">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #6a1b9a;">Salvar Alterações</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Nenhum produto ou serviço cadastrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Paginação -->
                <?php if ($total_paginas > 1): ?>
                    <div class="card-footer bg-white py-3">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?= ($pagina <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?= $pagina - 1; ?>">Anterior</a>
                                </li>
                                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                    <li class="page-item <?= ($pagina == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?pagina=<?= $i; ?>" style="<?= ($pagina == $i) ? 'background-color: #6a1b9a; border-color: #6a1b9a;' : 'color: #6a1b9a;'; ?>"><?= $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($pagina >= $total_paginas) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?= $pagina + 1; ?>">Próxima</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rodapé -->
        <?php include_once '../../includes/footer.php'; ?>
    </div>

    <!-- Modal Novo Item -->
    <div class="modal fade" id="modalNovoProduto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background-color: #4a148c;">
                    <h5 class="modal-title fw-bold"><i class="fas fa-box-open me-2"></i> Novo Produto ou Serviço</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="salvar.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nome do Item *</label>
                            <input type="text" name="nome" class="form-control" required placeholder="Ex: Depilação Virilha / Creme Hidratante">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Tipo *</label>
                                <select name="tipo" class="form-select" required>
                                    <option value="Serviço">Serviço</option>
                                    <option value="Produto">Produto</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Preço de Venda (R$) *</label>
                                <input type="text" name="preco_venda" class="form-control" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Quantidade em Estoque (Apenas Produtos)</label>
                            <input type="number" name="quantidade_estoque" class="form-control" value="0" placeholder="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Foto do Produto / Serviço</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #6a1b9a;">Cadastrar Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>