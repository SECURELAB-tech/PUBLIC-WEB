// ============================================================
//  SecureLab — main.js
//  Canvas: red de nodos animada (estilo cyberseguridad)
// ============================================================

(function () {
    const canvas = document.getElementById('canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let W, H, nodes = [], RAF;
    const NODE_COUNT = 60;
    const MAX_DIST   = 130;
    const COLOR      = '0, 245, 196';

    function resize() {
        W = canvas.width  = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }

    function initNodes() {
        nodes = [];
        for (let i = 0; i < NODE_COUNT; i++) {
            nodes.push({
                x:  Math.random() * W,
                y:  Math.random() * H,
                vx: (Math.random() - .5) * .4,
                vy: (Math.random() - .5) * .4,
                r:  Math.random() * 2 + 1
            });
        }
    }

    function draw() {
        ctx.clearRect(0, 0, W, H);

        // Mover nodos
        for (const n of nodes) {
            n.x += n.vx;
            n.y += n.vy;
            if (n.x < 0 || n.x > W) n.vx *= -1;
            if (n.y < 0 || n.y > H) n.vy *= -1;
        }

        // Dibujar conexiones
        for (let i = 0; i < nodes.length; i++) {
            for (let j = i + 1; j < nodes.length; j++) {
                const dx   = nodes[i].x - nodes[j].x;
                const dy   = nodes[i].y - nodes[j].y;
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < MAX_DIST) {
                    const alpha = (1 - dist / MAX_DIST) * 0.4;
                    ctx.beginPath();
                    ctx.strokeStyle = `rgba(${COLOR}, ${alpha})`;
                    ctx.lineWidth   = .6;
                    ctx.moveTo(nodes[i].x, nodes[i].y);
                    ctx.lineTo(nodes[j].x, nodes[j].y);
                    ctx.stroke();
                }
            }
        }

        // Dibujar nodos
        for (const n of nodes) {
            ctx.beginPath();
            ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(${COLOR}, 0.6)`;
            ctx.fill();
        }

        RAF = requestAnimationFrame(draw);
    }

    window.addEventListener('resize', () => {
        resize();
        initNodes();
    });

    resize();
    initNodes();
    draw();
})();
