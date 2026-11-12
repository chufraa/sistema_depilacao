<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}
require_once '../../config/database.php';

$page_title = "GERENCIAR USUÁRIOS";
$mensagem = '';
$erro = '';

// Descobrir o nome correto da coluna de login na tabela usuarios
try {
    $stmt_cols = $pdo->query("DESCRIBE usuarios");
    $colunas = $stmt_cols->fetchAll(PDO::FETCH_COLUMN);
    $coluna_login = 'usuario';
    if (in_array('nome', $colunas) && !in_array('usuario', $colunas)) { 
        $coluna_login = 'nome'; 
    }
} catch (Exception $e) {
    $coluna_login = 'usuario';
}

// Processar exclusão de usuário
if (isset($_GET['excluir'])) {
    $id_excluir = (int)$_GET['excluir'];
    if ($id_excluir == $_SESSION['usuario_id']) {
        $erro = "Você não pode excluir o seu próprio usuário ativo.";
    } else {
        $stmt_del = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt_del->execute([$id_excluir]);
        header('Location: index.php?sucesso=1');
        exit;
    }
}

// Processar cadastro de novo usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar_usuario'])) {
    $novo_usuario = trim($_POST['usuario']);
    $nova_senha = trim($_POST['senha']);

    if (!empty($novo_usuario) && !empty($nova_senha)) {
        $senha_cripto = md5($nova_senha);

        try {
            $stmt_ins = $pdo->prepare("INSERT INTO usuarios ({$coluna_login}, senha) VALUES (?, ?)");
            $stmt_ins->execute([$novo_usuario, $senha_cripto]);
            header('Location: index.php?sucesso=1');
            exit;
        } catch (Exception $e) {
            $erro = "Erro ao cadastrar: este nome de usuário já existe ou ocorreu um conflito.";
        }
    } else {
        $erro = "Preencha todos os campos para cadastrar.";
    }
}

// Buscar todos os usuários cadastrados
$stmt_usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY id ASC");
$lista_usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Fernanda Mozer Depilação</title>
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
            <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-users-cog me-2"></i> Gerenciar Usuários</a></li>
            <li class="nav-item"><a class="nav-link" href="../ajuda/index.php"><i class="fas fa-question-circle me-2"></i> Ajuda & Manual</a></li>
            <li class="nav-item mt-4"><a class="nav-link text-danger" href="../../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="flex-grow-1">
            <?php include_once '../../includes/header.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-0"><i class="fas fa-users-cog me-2 text-primary"></i> Gerenciamento de Usuários</h4>
                    <small class="text-muted">Cadastre novos operadores e visualize quem possui acesso ao sistema</small>
                </div>
                <button class="btn text-white rounded-pill px-4 shadow-sm" style="background-color: #4a148c;" data-bs-toggle="modal" data-bs-target="#modalNovoUsuario">
                    <i class="fas fa-user-plus me-2"></i> Novo Usuário
                </button>
            </div>

            <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Operação realizada com sucesso!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?= $erro; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Tabela de Usuários Cadastrados -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome de Usuário (Login)</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($lista_usuarios) > 0): ?>
                                    <?php foreach ($lista_usuarios as $u): ?>
                                        <tr>
                                            <td>#<?= $u['id']; ?></td>
                                            <td class="fw-bold text-dark">
                                                <i class="fas fa-user me-2 text-secondary"></i> 
                                                <?= htmlspecialchars($u[$coluna_login] ?? ($u['usuario'] ?? ($u['nome'] ?? 'Admin'))); ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                                                    <a href="index.php?excluir=<?= $u['id']; ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('Deseja realmente excluir este usuário?');">
                                                        <i class="fas fa-trash-alt me-1"></i> Excluir
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Usuário Atual</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">Nenhum usuário cadastrado.</td>
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

    <!-- Modal Novo Usuário -->
    <div class="modal fade" id="modalNovoUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background-color: #4a148c;">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i> Cadastrar Novo Usuário</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="index.php" method="POST">
                    <input type="hidden" name="cadastrar_usuario" value="1">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nome de Usuário (Login) *</label>
                            <input type="text" name="usuario" class="form-control" placeholder="Ex: recepcao, atendente..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Senha de Acesso *</label>
                            <input type="password" name="senha" class="form-control" placeholder="Digite a senha" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #6a1b9a;">Salvar Usuário</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>