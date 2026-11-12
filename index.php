<?php
session_start();
require_once 'config/database.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_input = trim($_POST['email'] ?? '');
    $senha_input = trim($_POST['senha'] ?? '');

    if (!empty($login_input) && !empty($senha_input)) {
        try {
            // Consulta focada estritamente na coluna 'email' da sua tabela de usuários
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$login_input]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['email'] ?? 'Administrador';
                
                header('Location: dashboard.php');
                exit;
            } else {
                $erro = "E-mail não encontrado!";
            }
        } catch (Exception $e) {
            $erro = "Erro interno ao tentar autenticar.";
        }
    } else {
        $erro = "Preencha todos os campos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Fernanda Mozer Depilação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #4a148c;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold" style="color: #4a148c;">✨ Fernanda Mozer</h3>
            <p class="text-muted small">Sistema de Gestão & Depilação</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger text-center py-2" role="alert">
                <small><?= $erro; ?></small>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST">
            <div class="mb-3">
                <label class="form-label fw-semibold">E-mail de Acesso</label>
                <input type="email" name="email" class="form-control" placeholder="Digite seu e-mail" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Senha</label>
                <input type="password" name="senha" class="form-control" placeholder="Digite sua senha" required>
            </div>
            <button type="submit" class="btn w-100 text-white py-2 rounded-pill fw-bold shadow-sm" style="background-color: #6a1b9a;">
                Entrar
            </button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>