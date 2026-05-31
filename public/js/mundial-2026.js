/**
 * MUNDIAL 2026 — Sistema de decoración temporal
 * FIFA World Cup USA / México / Canadá
 * Activo: 1 junio → 19 julio 2026
 */
(function () {
    'use strict';

    const SHOW_FROM = new Date('2026-05-28T00:00:00'); // Previo al torneo (inicia el 11-jun)
    const KICKOFF   = new Date('2026-06-11T00:00:00');
    const FINAL_END = new Date('2026-07-20T00:00:00');
    const DISMISSED_KEY = 'mundial2026_dismissed';

    const now = new Date();

    // Solo activo dentro del período
    if (now < SHOW_FROM || now >= FINAL_END) return;

    // Si fue descartado en esta sesión, no mostrar
    if (sessionStorage.getItem(DISMISSED_KEY)) return;

    document.addEventListener('DOMContentLoaded', function () {
        initBar();
        initBalls();
        document.documentElement.classList.add('mundial-active');
    });

    function initBar() {
        const bar = document.getElementById('mundial-bar');
        if (!bar) return;

        bar.style.display = 'flex';

        // Notifica la altura al CSS para que el nav se desplace
        requestAnimationFrame(function () {
            applyBarOffset(bar.offsetHeight);
        });

        document.documentElement.classList.add('mundial-bar-visible');

        // Countdown / live indicator
        const countdown = document.getElementById('mundial-countdown');
        if (countdown) {
            if (now < KICKOFF) {
                const days = Math.ceil((KICKOFF - now) / 86400000);
                countdown.textContent = days === 1 ? '¡Mañana comienza!' : 'Faltan ' + days + ' días';
            } else {
                countdown.textContent = '⚡ En Vivo';
                countdown.classList.add('is-live');
            }
        }

        // Dismiss
        const closeBtn = bar.querySelector('.mundial-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                bar.style.opacity = '0';
                bar.style.transition = 'opacity 0.25s ease';
                setTimeout(function () {
                    bar.style.display = 'none';
                    applyBarOffset(0);
                    document.documentElement.classList.remove('mundial-bar-visible');
                }, 280);
                sessionStorage.setItem(DISMISSED_KEY, '1');
            });
        }
    }

    function applyBarOffset(px) {
        document.documentElement.style.setProperty('--mundial-bar-h', px + 'px');
    }

    function initBalls() {
        const container = document.createElement('div');
        container.id = 'mundial-balls';
        document.body.appendChild(container);

        // Spawn initial batch staggered
        for (let i = 0; i < 6; i++) {
            setTimeout(function () { spawnBall(container); }, i * 3000 + Math.random() * 1500);
        }

        // Continuous spawn
        setInterval(function () { spawnBall(container); }, 5500);
    }

    function spawnBall(container) {
        const ball = document.createElement('span');
        ball.textContent = '⚽';
        const size   = Math.random() * 18 + 16;
        const left   = Math.random() * 94;
        const dur    = Math.random() * 10 + 14;
        const delay  = Math.random() * 2;

        ball.style.cssText = [
            'position:absolute',
            'top:-60px',
            'left:' + left + '%',
            'font-size:' + size + 'px',
            'opacity:0',
            'animation:mundialFall ' + dur + 's linear ' + delay + 's forwards',
            'will-change:transform,opacity'
        ].join(';');

        container.appendChild(ball);
        setTimeout(function () { ball.remove(); }, (dur + delay + 1) * 1000);
    }
})();
