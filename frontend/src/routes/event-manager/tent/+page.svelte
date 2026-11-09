<script lang="ts">
    import { Tent10, Leg, GableSupport, type DoorDirection, Tent20 } from '$lib/modules/tent-planner/model/Tent.js';

    const DOOR_WIDTH_M = 2.5;
    const tent = new Tent20(9);

    const SCALE   = 18;
    const PAD     = 60;
    const FIELD_M = 5;
    const W = tent.width * SCALE;
    const L = tent.length * SCALE;

    let canvas = $state<HTMLCanvasElement>();
    let placingDoor = $state(false);

    type Snap = { attachPoint: Leg | GableSupport; direction: DoorDirection } | null;
    let snap    = $state<Snap>(null);
    let view3D  = $state(false);

    // ─── 3D Konstanten ────────────────────────────────────────────────────────
    const cos30  = Math.cos(Math.PI / 6);
    const sin30  = Math.sin(Math.PI / 6);
    const W_m    = tent.width;
    const L_m    = tent.length;
    const EAVE_H = 2.5;
    const RIDGE_H = EAVE_H + (W_m / 2) * Math.tan(25 * Math.PI / 180);
    const ISO_S  = Math.min(14, Math.floor(420 / ((W_m + L_m) * cos30)));
    const PAD3D  = 40;
    const c3W    = Math.ceil((W_m + L_m) * cos30 * ISO_S) + PAD3D * 2;
    const c3H    = Math.ceil((W_m + L_m) * sin30 * ISO_S + RIDGE_H * ISO_S) + PAD3D * 2;
    const isoOX  = Math.ceil(L_m * cos30 * ISO_S) + PAD3D;
    const isoOY  = Math.ceil(RIDGE_H * ISO_S) + PAD3D;

    function ip(wx: number, wy: number, wz: number): [number, number] {
        return [(wx - wy) * cos30 * ISO_S + isoOX, (wx + wy) * sin30 * ISO_S - wz * ISO_S + isoOY];
    }

    function gableWX(g: GableSupport): number {
        if (g.position === 'corner-left')  return 0;
        if (g.position === 'corner-right') return W_m;
        if (g.position === 'left')         return W_m * 0.25;
        if (g.position === 'right')        return W_m * 0.75;
        return W_m * 0.5;
    }

    // ─── Positionshelfer ─────────────────────────────────────────────────────
    const legX = (l: Leg) => l.side === 'left' ? PAD : PAD + W;
    const legY = (l: Leg) => PAD + l.boundaryIndex * FIELD_M * SCALE;

    const gableX = (g: GableSupport) =>
        g.position === 'corner-left'  ? PAD :
        g.position === 'corner-right' ? PAD + W :
        g.position === 'left'         ? PAD + tent.width * 0.25 * SCALE :
        g.position === 'right'        ? PAD + tent.width * 0.75 * SCALE :
                                        PAD + tent.width * 0.5  * SCALE;
    const gableY = (g: GableSupport) => g.end === 'front' ? PAD : PAD + L;

    // ─── Snapping ────────────────────────────────────────────────────────────
    function findWallSnap(px: number, py: number): Snap {
        const SNAP = 28;

        // Seitenwände – Ecken ausschließen, damit Giebelecken-Snap greift
        for (const side of ['left', 'right'] as const) {
            const wx = side === 'left' ? PAD : PAD + W;
            if (Math.abs(px - wx) < SNAP && py > PAD + SNAP && py < PAD + L - SNAP) {
                const legsOnSide = tent.legs.filter(l => l.side === side);
                const nearest = legsOnSide.reduce((a, b) =>
                    Math.abs(py - legY(a)) <= Math.abs(py - legY(b)) ? a : b);
                return { attachPoint: nearest, direction: py >= legY(nearest) ? 'forward' : 'backward' };
            }
        }

        // Giebelwände (oben/unten)
        for (const end of ['front', 'back'] as const) {
            const wy = end === 'front' ? PAD : PAD + L;
            if (Math.abs(py - wy) < SNAP && px >= PAD && px <= PAD + W) {
                const supports = tent.gableSupports.filter(g => g.end === end);
                const nearest = supports.reduce((a, b) =>
                    Math.abs(px - gableX(a)) <= Math.abs(px - gableX(b)) ? a : b);
                return { attachPoint: nearest, direction: px >= gableX(nearest) ? 'forward' : 'backward' };
            }
        }

        return null;
    }

    // ─── Validierung ─────────────────────────────────────────────────────────
    function sectionKey(ap: Leg | GableSupport, dir: DoorDirection): string {
        if (ap instanceof Leg) {
            const fi = dir === 'forward' ? ap.boundaryIndex : ap.boundaryIndex - 1;
            return `side:${ap.side}:${fi}`;
        }
        return `gable:${ap.end}:${ap.position}:${dir}`;
    }

    function isValidSnap(s: Snap): boolean {
        if (!s) return false;
        const { attachPoint: ap, direction: dir } = s;
        if (ap instanceof Leg) {
            if (dir === 'backward' && ap.boundaryIndex === 0) return false;
            if (dir === 'forward'  && ap.boundaryIndex === tent.fields) return false;
        }
        if (ap instanceof GableSupport) {
            if (ap.position === 'corner-left'  && dir === 'backward') return false;
            if (ap.position === 'corner-right' && dir === 'forward')  return false;
        }
        const key = sectionKey(ap, dir);
        return !tent.doors.some(d => d.sectionKey === key);
    }

    // ─── Zeichnen ─────────────────────────────────────────────────────────────
    function drawDoorSegment(
        ctx: CanvasRenderingContext2D,
        ap: Leg | GableSupport,
        dir: DoorDirection,
        color: string,
        dashed = false,
    ) {
        const doorPx = DOOR_WIDTH_M * SCALE;
        ctx.strokeStyle = color;
        ctx.lineWidth   = 8;
        ctx.lineCap     = 'round';
        if (dashed) ctx.setLineDash([6, 4]);
        ctx.beginPath();

        if (ap instanceof Leg) {
            const x = legX(ap), y = legY(ap);
            const sy = dir === 'forward' ? y : y - doorPx;
            ctx.moveTo(x, sy); ctx.lineTo(x, sy + doorPx);
        } else {
            const x = gableX(ap), y = gableY(ap);
            const sx = dir === 'forward' ? x : x - doorPx;
            ctx.moveTo(sx, y); ctx.lineTo(sx + doorPx, y);
        }

        ctx.stroke();
        ctx.setLineDash([]);
        ctx.lineCap = 'butt';
    }

    function draw() {
        if (!canvas) return;
        const ctx = canvas.getContext('2d')!;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Zelt-Umriss
        ctx.strokeStyle = '#1e293b'; ctx.lineWidth = 2;
        ctx.strokeRect(PAD, PAD, W, L);

        // Feld-Trennlinien
        ctx.setLineDash([5, 5]); ctx.strokeStyle = '#94a3b8'; ctx.lineWidth = 1;
        for (let i = 1; i < tent.fields; i++) {
            const y = PAD + i * FIELD_M * SCALE;
            ctx.beginPath(); ctx.moveTo(PAD, y); ctx.lineTo(PAD + W, y); ctx.stroke();
        }
        ctx.setLineDash([]);

        // Gesetzte Türen
        for (const door of tent.doors) {
            drawDoorSegment(ctx, door.attachesTo, door.direction, '#16a34a');
        }

        // Füsse (■)
        for (const leg of tent.legs) {
            ctx.fillStyle = '#1e293b';
            ctx.fillRect(legX(leg) - 5, legY(leg) - 5, 10, 10);
        }

        // Giebelträger (●)
        for (const g of tent.gableSupports) {
            ctx.fillStyle = '#0ea5e9';
            ctx.beginPath(); ctx.arc(gableX(g), gableY(g), 6, 0, Math.PI * 2); ctx.fill();
        }

        // Vorschau
        if (placingDoor && snap) {
            const valid = isValidSnap(snap);
            drawDoorSegment(ctx, snap.attachPoint, snap.direction, valid ? '#86efac' : '#fca5a5', true);
        }

        // Legende
        ctx.fillStyle = '#64748b'; ctx.font = '12px monospace';
        ctx.fillText(`■ Fuss  ● Giebelträger  ━ Tür (${DOOR_WIDTH_M}m)`, PAD, PAD - 14);
    }

    function onMove(e: MouseEvent) {
        if (!canvas || view3D) return;
        const r = canvas.getBoundingClientRect();
        snap = placingDoor ? findWallSnap(e.clientX - r.left, e.clientY - r.top) : null;
    }

    function onClick() {
        if (!placingDoor || !snap || !isValidSnap(snap) || view3D) return;
        tent.attachDoor(snap.attachPoint, snap.direction, DOOR_WIDTH_M);
        draw();
    }

    // ─── 3D Zeichnen ──────────────────────────────────────────────────────────
    function edge3D(
        ctx: CanvasRenderingContext2D,
        a: [number,number], b: [number,number],
        color: string, width: number, dashed = false,
    ) {
        ctx.strokeStyle = color; ctx.lineWidth = width; ctx.lineCap = 'round';
        if (dashed) ctx.setLineDash([4, 4]);
        ctx.beginPath(); ctx.moveTo(a[0], a[1]); ctx.lineTo(b[0], b[1]); ctx.stroke();
        ctx.setLineDash([]); ctx.lineCap = 'butt';
    }

    function doorRect3D(ctx: CanvasRenderingContext2D, corners: [[number,number],[number,number],[number,number],[number,number]], color: string) {
        ctx.strokeStyle = color; ctx.lineWidth = 4; ctx.lineCap = 'round';
        ctx.beginPath();
        ctx.moveTo(corners[0][0], corners[0][1]); ctx.lineTo(corners[1][0], corners[1][1]);
        ctx.moveTo(corners[2][0], corners[2][1]); ctx.lineTo(corners[3][0], corners[3][1]);
        ctx.moveTo(corners[0][0], corners[0][1]); ctx.lineTo(corners[2][0], corners[2][1]);
        ctx.moveTo(corners[1][0], corners[1][1]); ctx.lineTo(corners[3][0], corners[3][1]);
        ctx.stroke(); ctx.lineCap = 'butt';
    }

    function draw3D() {
        if (!canvas) return;
        const ctx = canvas.getContext('2d')!;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const RX = W_m / 2;
        const D = '#1e293b', M = '#64748b', LT = '#cbd5e1';

        // Boden
        for (const [a, b] of [
            [ip(0,0,0), ip(W_m,0,0)], [ip(W_m,0,0), ip(W_m,L_m,0)],
            [ip(W_m,L_m,0), ip(0,L_m,0)], [ip(0,L_m,0), ip(0,0,0)]
        ] as [[number,number],[number,number]][]) edge3D(ctx, a, b, LT, 1, true);

        // Felder-Trennlinien auf Wänden + Dach
        for (let i = 0; i <= tent.fields; i++) {
            const wy = i * FIELD_M;
            edge3D(ctx, ip(0, wy, 0),   ip(0, wy, EAVE_H),   M, 0.75);
            edge3D(ctx, ip(W_m, wy, 0), ip(W_m, wy, EAVE_H), M, 0.75);
            if (i > 0 && i < tent.fields) {
                edge3D(ctx, ip(RX, wy, RIDGE_H), ip(0,   wy, EAVE_H), LT, 0.75, true);
                edge3D(ctx, ip(RX, wy, RIDGE_H), ip(W_m, wy, EAVE_H), LT, 0.75, true);
            }
        }

        // Traufen + Eckstiele
        edge3D(ctx, ip(0,0,EAVE_H),   ip(0,L_m,EAVE_H),   D, 1.5);
        edge3D(ctx, ip(W_m,0,EAVE_H), ip(W_m,L_m,EAVE_H), D, 1.5);
        edge3D(ctx, ip(0,0,EAVE_H),   ip(W_m,0,EAVE_H),   D, 1.5);
        edge3D(ctx, ip(0,L_m,EAVE_H), ip(W_m,L_m,EAVE_H), D, 1.5);
        for (const [wx, wy] of [[0,0],[W_m,0],[W_m,L_m],[0,L_m]] as [number,number][])
            edge3D(ctx, ip(wx, wy, 0), ip(wx, wy, EAVE_H), D, 2);

        // Giebelträger
        for (const g of tent.gableSupports) {
            const wy = g.end === 'front' ? 0 : L_m;
            edge3D(ctx, ip(gableWX(g), wy, 0), ip(gableWX(g), wy, EAVE_H), '#0ea5e9', 1.5);
        }

        // First + Dachkanten
        edge3D(ctx, ip(RX,0,RIDGE_H),   ip(RX,L_m,RIDGE_H),   D, 2);
        edge3D(ctx, ip(0,0,EAVE_H),     ip(RX,0,RIDGE_H),     D, 1.5);
        edge3D(ctx, ip(W_m,0,EAVE_H),   ip(RX,0,RIDGE_H),     D, 1.5);
        edge3D(ctx, ip(0,L_m,EAVE_H),   ip(RX,L_m,RIDGE_H),   D, 1.5);
        edge3D(ctx, ip(W_m,L_m,EAVE_H), ip(RX,L_m,RIDGE_H),   D, 1.5);

        // Türen
        for (const door of tent.doors) {
            const ap = door.attachesTo;
            const dir = door.direction;
            const dm = DOOR_WIDTH_M;
            if (ap instanceof Leg) {
                const wx = ap.side === 'left' ? 0 : W_m;
                const y0 = dir === 'forward' ? ap.boundaryIndex * FIELD_M : ap.boundaryIndex * FIELD_M - dm;
                doorRect3D(ctx, [ip(wx,y0,0), ip(wx,y0+dm,0), ip(wx,y0,EAVE_H), ip(wx,y0+dm,EAVE_H)], '#16a34a');
            } else {
                const wy = ap.end === 'front' ? 0 : L_m;
                const x0 = dir === 'forward' ? gableWX(ap) : gableWX(ap) - dm;
                doorRect3D(ctx, [ip(x0,wy,0), ip(x0+dm,wy,0), ip(x0,wy,EAVE_H), ip(x0+dm,wy,EAVE_H)], '#16a34a');
            }
        }
    }

    $effect(() => { snap; view3D; if (view3D) draw3D(); else draw(); });
</script>

<main class="p-6">
    <h1 class="text-xl font-bold mb-4">POC – Tent20 · 5 Felder · {tent.length}m lang</h1>

    <div class="flex gap-3 items-center mb-4">
        <button
            onclick={() => { placingDoor = !placingDoor; snap = null; }}
            class="btn {placingDoor ? 'preset-filled-warning-500' : 'preset-filled'}"
            disabled={view3D}
        >
            {placingDoor ? 'Abbrechen' : '+ Tür platzieren'}
        </button>
        {#if placingDoor}
            <span class="text-sm opacity-60">Maus an Seitenwand oder Giebel halten → klicken zum Platzieren</span>
        {/if}
        <button
            onclick={() => { view3D = !view3D; placingDoor = false; snap = null; }}
            class="btn preset-tonal ml-auto"
        >
            {view3D ? '↩ 2D' : '3D Ansicht'}
        </button>
        <span class="text-sm opacity-60">Türen: {tent.doors.length}</span>
    </div>

    <canvas
        bind:this={canvas}
        width={view3D ? c3W : W + PAD * 2}
        height={view3D ? c3H : L + PAD * 2}
        onclick={onClick}
        onmousemove={onMove}
        class="border border-surface-300-700 rounded-lg bg-white"
        style:cursor={placingDoor && !view3D ? 'crosshair' : 'default'}
    ></canvas>
</main>
