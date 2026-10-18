
export type SwipeAxis = 'x' | 'y';
export type SwipeEdge = 'left' | 'right' | 'top' | 'bottom';

export interface SwipeGestureOptions {
    axis: SwipeAxis;
    edge: SwipeEdge;
    open: boolean;
    handle?: HTMLElement | null | (() => HTMLElement | null);
    canOpen?: boolean;
    canClose?: boolean;
    onOpen?: () => void;
    onClose?: () => void;
    edgeSize?: number;
    threshold?: number;
    velocityThreshold?: number;
    animationDuration?: number;
}

const openStates = new WeakMap<HTMLElement, boolean>();

export function swipeGesture(initial: SwipeGestureOptions) {
    return (node: HTMLElement) => {
        const win = node.ownerDocument.defaultView;
        if (!win) return;

        const windowRef: Window = win;

        const doc = node.ownerDocument;

        const previousOpen = openStates.get(node);
        const firstAttachment = previousOpen === undefined;

        let options = { ...initial };
        let isOpen = options.open;

        const openChanged = !firstAttachment && previousOpen !== isOpen;

        openStates.set(node, isOpen);

        let dragging = false;
        let tracking = false;
        let decided = false;
        let pointerId: number | null = null;

        let startX = 0;
        let startY = 0;
        let startTime = 0;
        let lastCoordinate = 0;
        let lastTime = 0;
        let velocity = 0;
        let delta = 0;

        const originalTranslate = node.style.translate;
        const originalTransition = node.style.transition;
        const originalWillChange = node.style.willChange;
        const originalTouchAction = node.style.touchAction;

        node.style.touchAction = options.axis === 'x' ? 'pan-y' : 'pan-x';

        function validate() {
            const valid = options.axis === 'x'
                ? options.edge === 'left' || options.edge === 'right'
                : options.edge === 'top' || options.edge === 'bottom';

            if (!valid) {
                throw new Error(
                    `swipeGesture: edge "${options.edge}" passt nicht zu axis "${options.axis}".`
                );
            }
        }

        validate();

        function getSign() {
            return options.edge === 'left' || options.edge === 'top'
                ? -1
                : 1;
        }

        function getSize() {
            const rect = node.getBoundingClientRect();
            return Math.max(
                options.axis === 'x' ? rect.width : rect.height,
                1
            );
        }

        function getCoordinate(x: number, y: number) {
            return options.axis === 'x' ? x : y;
        }

        function isAtOpeningEdge(x: number, y: number) {
            const edgeSize = options.edgeSize ?? 24;

            switch (options.edge) {
                case 'left':
                    return x <= edgeSize;
                case 'right':
                    return windowRef.innerWidth - x <= edgeSize;
                case 'top':
                    return y <= edgeSize;
                case 'bottom':
                    return windowRef.innerHeight - y <= edgeSize;
            }
        }


        function render(animate = false) {
            const base = isOpen ? 0 : getSign() * getSize();
            const position = base + delta;

            node.style.transition = animate
                ? `translate ${options.animationDuration ?? 220}ms cubic-bezier(0.22, 1, 0.36, 1)`
                : 'none';

            node.style.translate = options.axis === 'x'
                ? `${position}px 0`
                : `0 ${position}px`;

            node.style.willChange = dragging ? 'translate' : originalWillChange;
        }


        function reset(animate = true) {
            tracking = false;
            dragging = false;
            decided = false;
            pointerId = null;
            delta = 0;
            velocity = 0;
            render(animate);
        }

        function onPointerDown(event: PointerEvent) {
            if (tracking || !event.isPrimary) return;
            if (event.pointerType === 'mouse' && event.button !== 0) return;


            if (isOpen) {
                if (options.canClose === false) return;

                const handle =
                    typeof options.handle === 'function'
                        ? options.handle()
                        : options.handle;

                if (!handle || !(event.target instanceof Node)) return;
                if (!handle.contains(event.target)) return;
            } else {
                if (
                    options.canOpen === false ||
                    !isAtOpeningEdge(event.clientX, event.clientY)
                ) {
                    return;
                }
            }

            tracking = true;
            dragging = false;
            decided = false;
            pointerId = event.pointerId;

            startX = event.clientX;
            startY = event.clientY;
            startTime = windowRef.performance.now();
            lastCoordinate = getCoordinate(startX, startY);
            lastTime = startTime;
            velocity = 0;
            delta = 0;
        }

        function onPointerMove(event: PointerEvent) {
            if (!tracking || event.pointerId !== pointerId) return;

            const dx = event.clientX - startX;
            const dy = event.clientY - startY;
            const primary = options.axis === 'x' ? dx : dy;
            const secondary = options.axis === 'x' ? dy : dx;

            if (!decided) {
                if (Math.hypot(dx, dy) < 6) return;

                decided = true;

                // Andere Bewegungsrichtung: normales Scrollen nicht blockieren.
                if (Math.abs(primary) <= Math.abs(secondary)) {
                    reset(false);
                    return;
                }

                dragging = true;
                node.style.transition = 'none';
            }

            if (!dragging) return;

            const coordinate = getCoordinate(event.clientX, event.clientY);
            const now = windowRef.performance.now();
            const elapsed = now - lastTime;

            if (elapsed > 0) {
                velocity = (coordinate - lastCoordinate) / elapsed;
            }

            lastCoordinate = coordinate;
            lastTime = now;

            const sign = getSign();
            const directionAllowed = isOpen
                ? options.canClose !== false && primary * sign > 0
                : options.canOpen !== false && primary * -sign > 0;

            if (!directionAllowed) {
                delta = 0;
                render();
                return;
            }

            delta = Math.max(-getSize(), Math.min(getSize(), primary));
            render();
        }

        function onPointerUp(event: PointerEvent) {
            if (!tracking || event.pointerId !== pointerId) return;

            if (!dragging) {
                reset();
                return;
            }

            const elapsed = windowRef.performance.now() - startTime;
            const progress = Math.abs(delta) / getSize();
            const sign = getSign();
            const opening = delta * -sign > 0;

            const velocityThreshold = options.velocityThreshold ?? 0.5;
            const fast = elapsed <= 250 &&
                Math.abs(velocity) >= velocityThreshold;

            const velocityTowardTarget = opening
                ? velocity * -sign > 0
                : velocity * sign > 0;

            const permitted = opening
                ? options.canOpen !== false
                : options.canClose !== false;

            const shouldToggle = permitted && (
                progress >= (options.threshold ?? 0.35) ||
                (fast && velocityTowardTarget)
            );

            if (shouldToggle && opening !== isOpen) {
                isOpen = opening;

                if (opening) {
                    options.onOpen?.();
                } else {
                    options.onClose?.();
                }
            }

            reset();
        }

        function onPointerCancel(event: PointerEvent) {
            if (event.pointerId === pointerId) reset();
        }

        doc.addEventListener('pointerdown', onPointerDown, true);
        doc.addEventListener('pointermove', onPointerMove, true);
        doc.addEventListener('pointerup', onPointerUp, true);
        doc.addEventListener('pointercancel', onPointerCancel, true);

        if (firstAttachment) {
            render(false);
        } else if (openChanged) {
            render(true);
        }

        return () => {
            doc.removeEventListener('pointerdown', onPointerDown, true);
            doc.removeEventListener('pointermove', onPointerMove, true);
            doc.removeEventListener('pointerup', onPointerUp, true);
            doc.removeEventListener('pointercancel', onPointerCancel, true);

            node.style.touchAction = originalTouchAction;
        };
    };
}
