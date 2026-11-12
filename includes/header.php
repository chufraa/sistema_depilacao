<div class="d-flex justify-content-between align-items-center py-2 px-3 bg-white border-bottom shadow-sm rounded-3 mb-3">
    <div class="d-flex align-items-center gap-2">
        <i class="fas fa-tachometer-alt text-success fs-5"></i>
        <span class="fw-bold text-dark text-uppercase tracking-wide" style="font-size: 0.95rem;">
            MÓDULO: <?= isset($page_title) ? htmlspecialchars($page_title) : 'INÍCIO'; ?>
        </span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- Botão de Modo Escuro -->
        <button id="btnModoEscuro" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 d-flex align-items-center gap-2">
            <i class="fas fa-moon fs-6"></i>
            <span class="d-none d-sm-inline">Modo Escuro</span>
        </button>

        <span class="badge bg-light text-dark border d-flex align-items-center gap-2 px-3 py-2 rounded-pill">
            <i class="fas fa-user-circle text-success fs-6"></i>
            <strong class="text-dark"><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Administrador'); ?></strong>
            <span class="text-muted"> (<?= ucfirst($_SESSION['usuario_nivel'] ?? 'Admin'); ?>)</span>
        </span>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnModoEscuro = document.getElementById('btnModoEscuro');
        
        // Verifica se o modo escuro já estava ativo na sessão anterior do navegador
        if (localStorage.getItem('modo_escuro') === 'ativo') {
            aplicarModoEscuro(true);
        }

        if (btnModoEscuro) {
            btnModoEscuro.addEventListener('click', function() {
                let ativoAtual = localStorage.getItem('modo_escuro') === 'ativo';
                aplicarModoEscuro(!ativoAtual);
            });
        }

        function aplicarModoEscuro(ativar) {
            if (ativar) {
                localStorage.setItem('modo_escuro', 'ativo');
                document.body.style.backgroundColor = '#121212';
                document.body.style.color = '#e0e0e0';
                
                // Ajusta elementos de fundo branco comuns do Bootstrap
                document.querySelectorAll('.card, .bg-white').forEach(el => {
                    el.style.backgroundColor = '#1e1e1e';
                    el.style.color = '#e0e0e0';
                    el.style.borderColor = '#333';
                });
                if (btnModoEscuro) {
                    btnModoEscuro.classList.remove('btn-outline-secondary');
                    btnModoEscuro.classList.add('btn-outline-light');
                    btnModoEscuro.innerHTML = '<i class="fas fa-sun fs-6 text-warning"></i> <span class="d-none d-sm-inline">Modo Claro</span>';
                }
            } else {
                localStorage.setItem('modo_escuro', 'inativo');
                location.reload(); // Recarrega para voltar perfeitamente ao tema original claro
            }
        }
    });
</script>