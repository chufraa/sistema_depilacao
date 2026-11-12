<div class="ticker-bar d-flex align-items-center justify-content-between px-3 py-2 mb-4 text-white rounded-3 shadow-sm" style="background-color: #1a232a;">
    <div class="d-flex align-items-center me-3">
        <span class="badge bg-success text-white fw-bold px-3 py-2 d-flex align-items-center gap-1 rounded-2">
            <i class="fas fa-rocket"></i> PDV & ERP
        </span>
    </div>

    <!-- Texto desfilante / animado -->
    <div class="flex-grow-1 overflow-hidden me-3" style="white-space: nowrap;">
        <div class="ticker-wrapper">
            <span class="ticker-text text-warning fw-semibold fs-6">
                ✨ Bem-vindo ao Sistema Fernanda Mozer Depilação! | 📅 Lembrete: Verifique a agenda de atendimentos do dia | 💡 Caixa Aberto e Operando Normalmente.
            </span>
        </div>
    </div>

    <div class="d-flex align-items-center">
        <span class="badge bg-dark text-warning border border-warning px-3 py-2 fw-bold d-flex align-items-center gap-1 rounded-2">
            <i class="fas fa-rocket"></i> SISTEMA ATIVO —
        </span>
    </div>
</div>

<style>
/* Animação de texto deslizante (Marquee suave em CSS) */
.ticker-wrapper {
    display: inline-block;
    padding-left: 100%;
    animation: marquee 22s linear infinite;
}
.ticker-wrapper:hover {
    animation-play-state: paused; /* Pausa ao passar o mouse */
}
@keyframes marquee {
    0%   { transform: translate(0, 0); }
    100% { transform: translate(-100%, 0); }
}
</style>