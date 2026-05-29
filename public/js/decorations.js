/**
 * STREAMIFY - SISTEMA DE DECORACIONES TEMÁTICAS
 * Versión: 1.0
 * Fecha: 1 de diciembre de 2025
 */

const Decorations = {
    // Estado actual
    activeDecorations: [],
    animationFrames: [],

    /**
     * NAVIDAD - Nieve cayendo + Luces navideñas
     */
    christmas: {
        snowContainer: null,
        lightsContainer: null,

        init() {
            this.createSnowfall();
            this.createChristmasLights();
            this.createSantaHat();
            return [this.snowContainer, this.lightsContainer];
        },

        createSnowfall() {
            this.snowContainer = document.createElement('div');
            this.snowContainer.id = 'snowfall-container';
            this.snowContainer.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: 9999;
                overflow: hidden;
            `;
            document.body.appendChild(this.snowContainer);

            // Crear copos de nieve
            for (let i = 0; i < 100; i++) {
                setTimeout(() => this.createSnowflake(), i * 100);
            }
        },

        createSnowflake() {
            if (!this.snowContainer) return;

            const snowflake = document.createElement('div');
            const size = Math.random() * 8 + 4;
            const left = Math.random() * 100;
            const duration = Math.random() * 5 + 5;
            const delay = Math.random() * 5;
            const opacity = Math.random() * 0.7 + 0.3;

            // Usar diferentes símbolos de copos
            const snowSymbols = ['❄', '❅', '❆'];
            snowflake.innerHTML = snowSymbols[Math.floor(Math.random() * snowSymbols.length)];

            snowflake.style.cssText = `
                position: absolute;
                color: white;
                text-shadow: 0 0 5px rgba(255, 255, 255, 0.8);
                font-size: ${size}px;
                opacity: ${opacity};
                top: -20px;
                left: ${left}%;
                animation: fall ${duration}s linear ${delay}s infinite;
                z-index: 9999;
            `;

            this.snowContainer.appendChild(snowflake);
        },

        createChristmasLights() {
            this.lightsContainer = document.createElement('div');
            this.lightsContainer.id = 'christmas-lights';
            this.lightsContainer.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 60px;
                pointer-events: none;
                z-index: 9998;
                display: flex;
                justify-content: space-around;
                align-items: flex-start;
            `;

            // Crear luces navideñas
            const colors = ['#ff0000', '#00ff00', '#ffff00', '#0000ff', '#ff00ff'];
            for (let i = 0; i < 30; i++) {
                const light = document.createElement('div');
                light.style.cssText = `
                    width: 12px;
                    height: 12px;
                    background-color: ${colors[i % colors.length]};
                    border-radius: 50%;
                    box-shadow: 0 0 10px ${colors[i % colors.length]};
                    animation: blink ${1 + Math.random()}s ease-in-out infinite;
                    animation-delay: ${Math.random() * 2}s;
                `;
                this.lightsContainer.appendChild(light);
            }

            document.body.appendChild(this.lightsContainer);
        },

        createSantaHat() {
            // Agregar gorro de Santa al logo (si existe)
            const logo = document.querySelector('.navbar-brand');
            if (logo) {
                const hat = document.createElement('span');
                hat.innerHTML = '🎅';
                hat.style.cssText = `
                    position: absolute;
                    top: -10px;
                    right: -15px;
                    font-size: 24px;
                    animation: bounce 2s ease-in-out infinite;
                `;
                logo.style.position = 'relative';
                logo.appendChild(hat);
            }
        },

        destroy() {
            if (this.snowContainer) {
                this.snowContainer.remove();
                this.snowContainer = null;
            }
            if (this.lightsContainer) {
                this.lightsContainer.remove();
                this.lightsContainer = null;
            }
            // Remover gorro de Santa
            document.querySelectorAll('.navbar-brand span').forEach(el => {
                if (el.innerHTML === '🎅') el.remove();
            });
        }
    },

    /**
     * AÑO NUEVO - Confetti explosivo
     */
    newyear: {
        canvas: null,
        animationId: null,

        init() {
            this.canvas = document.createElement('canvas');
            this.canvas.id = 'confetti-canvas';
            this.canvas.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: 9999;
            `;
            document.body.appendChild(this.canvas);

            const ctx = this.canvas.getContext('2d');
            this.canvas.width = window.innerWidth;
            this.canvas.height = window.innerHeight;

            // Partículas de confetti
            const particles = [];
            const colors = ['#ffd700', '#ff6347', '#4169e1', '#32cd32', '#ff69b4', '#ffa500'];

            for (let i = 0; i < 150; i++) {
                particles.push({
                    x: Math.random() * this.canvas.width,
                    y: Math.random() * this.canvas.height - this.canvas.height,
                    r: Math.random() * 6 + 3,
                    d: Math.random() * 10,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    tilt: Math.floor(Math.random() * 10) - 10,
                    tiltAngleIncremental: (Math.random() * 0.07) + 0.05,
                    tiltAngle: 0
                });
            }

            const draw = () => {
                ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

                particles.forEach((p, i) => {
                    ctx.beginPath();
                    ctx.lineWidth = p.r / 2;
                    ctx.strokeStyle = p.color;
                    ctx.moveTo(p.x + p.tilt + p.r, p.y);
                    ctx.lineTo(p.x + p.tilt, p.y + p.tilt + p.r);
                    ctx.stroke();

                    p.tiltAngle += p.tiltAngleIncremental;
                    p.y += (Math.cos(p.d) + 3 + p.r / 2) / 2;
                    p.tilt = Math.sin(p.tiltAngle - (i / 3)) * 15;
                    p.d += 0.1;

                    if (p.y > this.canvas.height) {
                        particles[i] = {
                            x: Math.random() * this.canvas.width,
                            y: -20,
                            r: Math.random() * 6 + 3,
                            d: Math.random() * 10,
                            color: colors[Math.floor(Math.random() * colors.length)],
                            tilt: Math.floor(Math.random() * 10) - 10,
                            tiltAngleIncremental: (Math.random() * 0.07) + 0.05,
                            tiltAngle: 0
                        };
                    }
                });

                this.animationId = requestAnimationFrame(draw);
            };

            draw();
            return this.canvas;
        },

        destroy() {
            if (this.animationId) {
                cancelAnimationFrame(this.animationId);
            }
            if (this.canvas) {
                this.canvas.remove();
                this.canvas = null;
            }
        }
    },

    /**
     * SAN VALENTÍN - Corazones flotantes
     */
    valentine: {
        container: null,

        init() {
            this.container = document.createElement('div');
            this.container.id = 'hearts-container';
            this.container.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                pointer-events: none;
                z-index: 9999;
                overflow: hidden;
            `;
            document.body.appendChild(this.container);

            for (let i = 0; i < 20; i++) {
                setTimeout(() => this.createHeart(), i * 300);
            }

            return this.container;
        },

        createHeart() {
            if (!this.container) return;

            const heart = document.createElement('div');
            const hearts = ['💕', '💖', '💗', '💝', '💘'];
            const size = Math.random() * 30 + 20;
            const left = Math.random() * 100;
            const duration = Math.random() * 8 + 6;
            const delay = Math.random() * 5;

            heart.innerHTML = hearts[Math.floor(Math.random() * hearts.length)];
            heart.style.cssText = `
                position: absolute;
                font-size: ${size}px;
                opacity: ${Math.random() * 0.6 + 0.4};
                bottom: -50px;
                left: ${left}%;
                animation: float-up ${duration}s ease-in ${delay}s infinite;
            `;

            this.container.appendChild(heart);
        },

        destroy() {
            if (this.container) {
                this.container.remove();
                this.container = null;
            }
        }
    },

    /**
     * Activar decoración por tema
     */
    /**
     * MUNDIAL 2026 ⚽🏆 — Pelotas + Confeti Ecuador + Ticker
     */
    mundial2026: {
        container: null,
        ticker: null,
        intervalId: null,

        init() {
            this.createBalls();
            this.createConfetti();
            this.createTicker();
            return [this.container, this.ticker];
        },

        createBalls() {
            this.container = document.createElement('div');
            this.container.id = 'mundial-container';
            this.container.style.cssText = `
                position:fixed;top:0;left:0;width:100%;height:100%;
                pointer-events:none;z-index:9998;overflow:hidden;
            `;
            document.body.appendChild(this.container);

            for (let i = 0; i < 8; i++) {
                setTimeout(() => this.spawnBall(), i * 900);
            }
            this.intervalId = setInterval(() => this.spawnBall(), 4000);
        },

        spawnBall() {
            if (!this.container) return;
            const ball = document.createElement('div');
            const size = Math.random() * 22 + 18;
            const left = Math.random() * 90 + 5;
            const dur  = Math.random() * 5 + 6;
            const delay = Math.random() * 2;
            ball.textContent = '⚽';
            ball.style.cssText = `
                position:absolute;
                font-size:${size}px;
                left:${left}%;
                top:-${size + 10}px;
                animation: mundialFall ${dur}s ease-in ${delay}s forwards;
                filter: drop-shadow(0 3px 6px rgba(0,0,0,0.5));
            `;
            this.container.appendChild(ball);
            setTimeout(() => ball.remove(), (dur + delay + 0.5) * 1000);
        },

        createConfetti() {
            // Confeti en colores Ecuador: amarillo, azul, rojo
            const colors = ['#FFD100','#003893','#D30000','#ffffff','#FFD100','#003893'];
            for (let i = 0; i < 60; i++) {
                setTimeout(() => {
                    if (!this.container) return;
                    const piece = document.createElement('div');
                    const color = colors[Math.floor(Math.random() * colors.length)];
                    const left  = Math.random() * 100;
                    const size  = Math.random() * 8 + 5;
                    const dur   = Math.random() * 6 + 5;
                    const rotate = Math.random() * 360;
                    piece.style.cssText = `
                        position:absolute;
                        width:${size}px;height:${size * 0.5}px;
                        background:${color};
                        left:${left}%;top:-10px;
                        border-radius:2px;
                        animation: mundialConfetti ${dur}s linear forwards;
                        transform: rotate(${rotate}deg);
                        opacity:0.85;
                    `;
                    this.container.appendChild(piece);
                    setTimeout(() => piece.remove(), (dur + 0.5) * 1000);
                }, i * 200);
            }
        },

        createTicker() {
            this.ticker = document.createElement('div');
            this.ticker.id = 'mundial-ticker';
            const messages = [
                '🏆 Mundial 2026 · México · Canadá · USA',
                '🇪🇨 ¡Vamos Ecuador! · La Tri en el Mundial',
                '⚽ FIFA World Cup 2026 · 11 Jun – 19 Jul',
                '🟡🔵🔴 Ecuador en el Mundial · #LaTri',
                '🏟️ Streamify HQ te desea ¡arriba Ecuador!',
            ];
            let idx = 0;
            this.ticker.style.cssText = `
                position:fixed;bottom:0;left:0;right:0;
                background:linear-gradient(135deg,#003893,#001f5c);
                color:#FFD100;font-weight:700;font-size:.82rem;
                padding:7px 20px;z-index:10001;
                display:flex;align-items:center;gap:12px;
                border-top:3px solid #FFD100;
                letter-spacing:.04em;
                animation:mundialTickerIn .4s ease;
                pointer-events:none;
            `;
            this.ticker.innerHTML = `
                <span style="font-size:1.2rem">⚽</span>
                <span id="mundial-ticker-text">${messages[0]}</span>
                <span style="margin-left:auto;font-size:1rem">🇪🇨🇲🇽🇨🇦🇺🇸</span>
            `;
            document.body.appendChild(this.ticker);
            const textEl = this.ticker.querySelector('#mundial-ticker-text');
            setInterval(() => {
                idx = (idx + 1) % messages.length;
                textEl.style.opacity = '0';
                setTimeout(() => {
                    textEl.textContent = messages[idx];
                    textEl.style.opacity = '1';
                    textEl.style.transition = 'opacity .4s';
                }, 300);
            }, 4000);
        },

        destroy() {
            if (this.container) { this.container.remove(); this.container = null; }
            if (this.ticker)    { this.ticker.remove();    this.ticker    = null; }
            if (this.intervalId){ clearInterval(this.intervalId); this.intervalId = null; }
        }
    },

    activate(theme) {
        console.log(`[Decorations] Activando decoración: ${theme}`);
        this.deactivateAll();

        switch(theme) {
            case 'christmas':
                this.activeDecorations = this.christmas.init();
                break;
            case 'newyear':
                this.activeDecorations = [this.newyear.init()];
                break;
            case 'valentine':
                this.activeDecorations = [this.valentine.init()];
                break;
            case 'mundial2026':
                this.activeDecorations = this.mundial2026.init();
                break;
        }
    },

    /**
     * Desactivar todas las decoraciones
     */
    deactivateAll() {
        console.log('[Decorations] Desactivando decoraciones');
        this.christmas.destroy();
        this.newyear.destroy();
        this.valentine.destroy();
        this.mundial2026.destroy();
        this.activeDecorations = [];
    }
};

// CSS para animaciones
const decorationStyles = document.createElement('style');
decorationStyles.textContent = `
    @keyframes fall {
        to {
            transform: translateY(100vh) rotate(360deg);
        }
    }

    @keyframes float-up {
        to {
            transform: translateY(-100vh) rotate(720deg);
            opacity: 0;
        }
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }

    @keyframes mundialFall {
        0%   { transform: translateY(0) rotate(0deg);   opacity: 1; }
        80%  { opacity: 1; }
        100% { transform: translateY(105vh) rotate(540deg); opacity: 0; }
    }

    @keyframes mundialConfetti {
        0%   { transform: translateY(0) rotate(0deg) scaleX(1);   opacity: .9; }
        50%  { transform: translateY(50vh) rotate(180deg) scaleX(-1); opacity: .7; }
        100% { transform: translateY(105vh) rotate(360deg) scaleX(1); opacity: 0; }
    }

    @keyframes mundialTickerIn {
        from { transform: translateY(100%); }
        to   { transform: translateY(0); }
    }
`;
document.head.appendChild(decorationStyles);

console.log('[Decorations] Sistema de decoraciones cargado');
