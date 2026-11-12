<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

$page_title = "EDITAR CLIENTE";
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome            = trim($_POST['nome']);
    $telefone        = trim($_POST['telefone']);
    $cpf             = trim($_POST['cpf']);
    $data_nascimento = !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : null;
    $anamnese        = trim($_POST['anamnese']);

    if (!empty($nome) && $id > 0) {
        $sql = "UPDATE clientes 
                SET nome = :nome, telefone = :telefone, cpf = :cpf, data_nascimento = :data_nascimento, anamnese = :anamnese 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'nome'            => $nome,
            'telefone'        => $telefone,
            'cpf'             => $cpf,
            'data_nascimento' => $data_nascimento,
            'anamnese'        => $anamnese,
            'id'              => $id
        ]);

        header('Location: index.php?sucesso=1');
        exit;
    }
}

$sql = "SELECT * FROM clientes WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente - Fernanda Mozer Depilação</title>
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
            <li class="nav-item"><a class="nav-link" href="/sistema_depilacao/dashboard.php"><i class="fas fa-home me-2"></i> Início / Resumo</a></li>
            <li class="nav-item"><a class="nav-link" href="/sistema_depilacao/modules/agenda/index.php"><i class="fas fa-calendar-alt me-2"></i> Agenda</a></li>
            <li class="nav-item"><a class="nav-link active" href="/sistema_depilacao/modules/clientes/index.php"><i class="fas fa-users me-2"></i> Clientes</a></li>
            <li class="nav-item"><a class="nav-link" href="/sistema_depilacao/modules/produtos/index.php"><i class="fas fa-box me-2"></i> Produtos & Estoque</a></li>
            <li class="nav-item"><a class="nav-link" href="/sistema_depilacao/modules/caixa/index.php"><i class="fas fa-cash-register me-2"></i> Caixa / PDV</a></li>
            <li class="nav-item"><a class="nav-link" href="/sistema_depilacao/modules/financeiro/index.php"><i class="fas fa-wallet me-2"></i> Financeiro</a></li>
            <li class="nav-item mt-4"><a class="nav-link text-danger" href="/sistema_depilacao/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
        </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="main-content">
        <div class="flex-grow-1">
            <?php include_once '../../includes/header.php'; ?>
            <?php include_once '../../includes/ticker.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-dark mb-0"><i class="fas fa-user-edit me-2 text-primary"></i> Editar Cliente</h4>
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fas fa-arrow-left me-2"></i> Voltar
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-4">
                <form action="editar.php?id=<?= $cliente['id']; ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nome Completo *</label>
                        <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($cliente['nome']); ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Telefone / WhatsApp</label>
                            <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($cliente['telefone']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">CPF</label>
                            <input type="text" name="cpf" class="form-control" value="<?= htmlspecialchars($cliente['cpf']); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Data de Nascimento</label>
                        <input type="date" name="data_nascimento" class="form-control" value="<?= $cliente['data_nascimento']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ficha de Anamnese / Observações</label>
                        <textarea name="anamnese" class="form-control" rows="4"><?= htmlspecialchars($cliente['anamnese']); ?></textarea>
                    </div>
                    <div class="text-end">
                        <a href="index.php" class="btn btn-light rounded-pill px-4 me-2">Cancelar</a>
                        <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #6a1b9a;">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>

        <?php include_once '../../includes/footer.php'; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>