<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

$page_title = "CAIXA / PDV";

// Buscar clientes e itens (produtos e serviços) para a vitrine e carrinho
$clientes = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome ASC")->fetchAll();
$produtos = $pdo->query("SELECT id, nome, tipo, preco_venda, quantidade_estoque FROM produtos ORDER BY nome ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frente de Caixa (PDV) - Fernanda Mozer Depilação</title>
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
        .card-produto-vitrine {
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid #dee2e6;
        }
        .card-produto-vitrine:hover {
            border-color: #6a1b9a;
            background-color: #f8f9fa;
            transform: translateY(-2px);
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
            <li class="nav-item"><a class="nav-link" href="../produtos/index.php"><i class="fas fa-box me-2"></i> Produtos & Estoque</a></li>
            <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-cash-register me-2"></i> Caixa / PDV</a></li>
            <li class="nav-item"><a class="nav-link" href="../financeiro/index.php"><i class="fas fa-wallet me-2"></i> Financeiro</a></li>
            <li class="nav-item mt-4"><a class="nav-link text-danger" href="../../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a></li>
        </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <div class="main-content">
        <div>
            <?php include_once '../../includes/header.php'; ?>
            <?php include_once '../../includes/ticker.php'; ?>

            <!-- Barra de Status do Caixa Superior -->
            <div class="d-flex justify-content-between align-items-center bg-dark text-white px-4 py-2 rounded-3 mb-3 shadow-sm">
                <div>
                    <h5 class="mb-0 fw-bold"><i class="fas fa-cash-register me-2 text-warning"></i> Frente de Caixa (PDV)</h5>
                    <small class="text-white-50" style="font-size: 0.8rem;">Dica: Clique no item da vitrine para adicionar ao carrinho.</small>
                </div>
                <div>
                    <span class="badge bg-success px-3 py-2 fs-6"><i class="fas fa-check-circle me-1"></i> CAIXA ABERTO</span>
                </div>
            </div>

            <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Venda finalizada com sucesso!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="processar_venda.php" method="POST" id="formVenda">
                <div class="row g-3">
                    <!-- Coluna Esquerda: Busca, Vitrine e Carrinho -->
                    <div class="col-lg-8">
                        <!-- Barra de Pesquisa -->
                        <div class="input-group mb-3 shadow-sm">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" placeholder="Digite para filtrar o produto ou serviço... (Atalho: F2)" id="buscaProduto">
                        </div>

                        <!-- Vitrine de Produtos / Serviços (Cards) -->
                        <div class="card border-0 shadow-sm rounded-3 mb-3 p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-secondary fs-6"><i class="fas fa-th-large me-1"></i> Vitrine de Serviços & Produtos</span>
                                <small class="text-muted"><span id="contadorItens"><?= count($produtos); ?></span> itens</small>
                            </div>
                            <div class="row g-2 overflow-auto" style="max-height: 220px;" id="vitrineContainer">
                                <?php foreach ($produtos as $p): ?>
                                    <div class="col-md-3 item-vitrine" data-nome="<?= strtolower($p['nome']); ?>">
                                        <div class="p-2 rounded card-produto-vitrine h-100 d-flex flex-column justify-content-between" 
                                             onclick="adicionarAoCarrinho(<?= $p['id']; ?>, '<?= htmlspecialchars($p['nome'], ENT_QUOTES); ?>', <?= $p['preco_venda']; ?>)">
                                            <div>
                                                <div class="fw-bold text-dark text-truncate" style="font-size: 0.85rem;" title="<?= htmlspecialchars($p['nome']); ?>"><?= htmlspecialchars($p['nome']); ?></div>
                                                <small class="text-muted" style="font-size: 0.75rem;"><?= $p['tipo']; ?></small>
                                            </div>
                                            <div class="text-success fw-bold mt-2" style="font-size: 0.9rem;">
                                                R$ <?= number_format($p['preco_venda'], 2, ',', '.'); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Tabela do Carrinho -->
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body p-0">
                                <div class="table-responsive" style="min-height: 200px;">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr style="font-size: 0.85rem;">
                                                <th>PRODUTO / SERVIÇO</th>
                                                <th style="width: 120px;">QTD</th>
                                                <th>PREÇO UNIT.</th>
                                                <th>SUBTOTAL</th>
                                                <th class="text-center">AÇÃO</th>
                                            </tr>
                                        </thead>
                                        <tbody id="carrinhoBody">
                                            <tr id="carrinhoVazio">
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <i class="fas fa-shopping-cart fa-2x mb-2 text-black-50"></i><br>
                                                    O carrinho está vazio. Clique acima na vitrine para adicionar um item.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna Direita: Totais, Troco e Fechamento -->
                    <div class="col-lg-4">
                        <!-- Total a Pagar -->
                        <div class="card border-0 shadow-sm rounded-3 bg-dark text-white p-3 mb-3">
                            <small class="text-white-50 text-uppercase fw-semibold" style="font-size: 0.75rem;">Total a Pagar</small>
                            <h1 class="fw-bold mb-0 text-success" style="font-size: 2.5rem;" id="displayTotal">R$ 0,00</h1>
                            <input type="hidden" name="valor_total" id="inputValorTotal" value="0.00">
                        </div>

                        <!-- Calculadora de Troco -->
                        <div class="card border-0 shadow-sm rounded-3 p-3 mb-3 bg-white">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;"><i class="fas fa-calculator me-1"></i> Calculadora de Troco</label>
                            <div class="mb-2">
                                <small class="text-muted" style="font-size: 0.75rem;">Valor Recebido (Dinheiro)</small>
                                <input type="number" step="0.01" class="form-control form-control-sm" placeholder="0.00" id="valorRecebido">
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Troco:</span>
                                <span class="fw-bold text-success fs-5" id="displayTroco">R$ 0,00</span>
                            </div>
                        </div>

                        <!-- Fechamento da Venda -->
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white mb-3">
                            <h6 class="fw-bold text-secondary mb-3" style="font-size: 0.9rem;"><i class="fas fa-file-invoice-dollar me-1"></i> Fechamento da Venda</h6>
                            
                            <div class="mb-2">
                                <label class="form-label text-muted" style="font-size: 0.75rem;">Cliente da Venda (Opcional)</label>
                                <select name="cliente_id" class="form-select form-select-sm">
                                    <option value="">-- Consumidor Geral / Balcão --</option>
                                    <?php foreach ($clientes as $cli): ?>
                                        <option value="<?= $cli['id']; ?>"><?= htmlspecialchars($cli['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted" style="font-size: 0.75rem;">Forma de Pagamento</label>
                                <select name="forma_pagamento" class="form-select form-select-sm" required>
                                    <option value="Pix">Pix</option>
                                    <option value="Dinheiro">Dinheiro</option>
                                    <option value="Cartão Crédito">Cartão de Crédito</option>
                                    <option value="Cartão Débito">Cartão de Débito</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2 fw-bold mb-2 shadow-sm">
                                <i class="fas fa-check-circle me-1"></i> Finalizar Venda (F4)
                            </button>
                            <a href="index.php" class="btn btn-outline-danger w-100 py-1" style="font-size: 0.85rem;">
                                <i class="fas fa-times me-1"></i> Cancelar Venda (F9)
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Rodapé -->
        <?php include_once '../../includes/footer.php'; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script de Filtro e Carrinho Interativo -->
    <script>
        let carrinho = [];

        function adicionarAoCarrinho(id, nome, preco) {
            let itemExistente = carrinho.find(item => item.id === id);
            if (itemExistente) {
                itemExistente.quantidade += 1;
            } else {
                carrinho.push({ id: id, nome: nome, preco: preco, quantidade: 1 });
            }
            atualizarCarrinho();
        }

        function alterarQtd(id, delta) {
            let item = carrinho.find(item => item.id === id);
            if (item) {
                item.quantidade += delta;
                if (item.quantidade <= 0) {
                    carrinho = carrinho.filter(i => i.id !== id);
                }
            }
            atualizarCarrinho();
        }

        function removerItem(id) {
            carrinho = carrinho.filter(item => item.id !== id);
            atualizarCarrinho();
        }

        function atualizarCarrinho() {
            let tbody = document.getElementById("carrinhoBody");
            tbody.innerHTML = "";

            if (carrinho.length === 0) {
                tbody.innerHTML = `
                    <tr id="carrinhoVazio">
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-shopping-cart fa-2x mb-2 text-black-50"></i><br>
                            O carrinho está vazio. Clique acima na vitrine para adicionar um item.
                        </td>
                    </tr>
                `;
                document.getElementById("displayTotal").innerText = "R$ 0,00";
                document.getElementById("inputValorTotal").value = "0.00";
                calcularTroco();
                return;
            }

            let totalGeral = 0;

            carrinho.forEach(item => {
                let subtotal = item.preco * item.quantidade;
                totalGeral += subtotal;

                tbody.innerHTML += `
                    <tr>
                        <td class="fw-bold text-dark">
                            ${item.nome}
                            <input type="hidden" name="produtos_id[]" value="${item.id}">
                            <input type="hidden" name="quantidades[]" value="${item.quantidade}">
                        </td>
                        <td>
                            <div class="input-group input-group-sm" style="width: 100px;">
                                <button type="button" class="btn btn-outline-secondary px-2" onclick="alterarQtd(${item.id}, -1)">-</button>
                                <input type="text" class="form-control text-center bg-white" value="${item.quantidade}" readonly>
                                <button type="button" class="btn btn-outline-secondary px-2" onclick="alterarQtd(${item.id}, 1)">+</button>
                            </div>
                        </td>
                        <td class="text-muted">R$ ${item.preco.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        <td class="text-success fw-bold">R$ ${subtotal.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle" onclick="removerItem(${item.id})">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            document.getElementById("displayTotal").innerText = `R$ ${totalGeral.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
            document.getElementById("inputValorTotal").value = totalGeral.toFixed(2);
            calcularTroco();
        }

        function calcularTroco() {
            let total = parseFloat(document.getElementById("inputValorTotal").value) || 0;
            let recebido = parseFloat(document.getElementById("valorRecebido").value) || 0;
            let troco = recebido - total;

            let displayTroco = document.getElementById("displayTroco");
            if (troco >= 0) {
                displayTroco.innerText = `R$ ${troco.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                displayTroco.className = "fw-bold text-success fs-5";
            } else {
                displayTroco.innerText = "Falta valor";
                displayTroco.className = "fw-bold text-danger fs-5";
            }
        }

        document.getElementById("valorRecebido").addEventListener("input", calcularTroco);

        // Filtro instantâneo na vitrine
        document.addEventListener("DOMContentLoaded", function () {
            const inputBusca = document.getElementById("buscaProduto");
            const itensVitrine = document.querySelectorAll(".item-vitrine");

            inputBusca.addEventListener("input", function () {
                let termo = this.value.toLowerCase().trim();

                itensVitrine.forEach(function (item) {
                    let nomeProduto = item.getAttribute("data-nome");
                    if (nomeProduto.includes(termo)) {
                        item.style.display = "";
                    } else {
                        item.style.display = "none";
                    }
                });
            });

            document.addEventListener("keydown", function (e) {
                if (e.key === "F2") {
                    e.preventDefault();
                    inputBusca.focus();
                }
            });
        });
    </script>
</body>
</html>