const registerServiceWorker = async () => {
  if (typeof navigator !== "undefined" && "serviceWorker" in navigator) {
    try {
      const registration = await navigator.serviceWorker.register("/service-worker.js", {
        scope: "/",
      });
      if (registration.installing) {
        console.log("Service worker installing");
      } else if (registration.waiting) {
        console.log("Service worker installed");
      } else if (registration.active) {
        console.log("Service worker active");
      }
    } catch (error) {
      console.error(`Registration failed with ${error}`);
    }
  }
};

const releaseRipple = (ripple: HTMLElement) => {
  ripple.classList.add("is-releasing");
  ripple.addEventListener("animationend", () => ripple.remove(), { once: true });
};

const createRipple = (element: HTMLElement, x: number, y: number) => {
  if (getComputedStyle(element).position === "static") {
    element.classList.add("ripple-positioned");
  }

  const radius = Math.hypot(
    Math.max(x, element.clientWidth - x),
    Math.max(y, element.clientHeight - y),
  );
  const ripple = document.createElement("span");

  ripple.className = "material-ripple";
  ripple.style.setProperty("--ripple-x", `${x}px`);
  ripple.style.setProperty("--ripple-y", `${y}px`);
  ripple.style.setProperty("--ripple-radius", `${radius}px`);
  element.append(ripple);
  void ripple.offsetWidth;
  ripple.classList.add("is-active");

  return ripple;
};

const getRippleTarget = (target: EventTarget | null): HTMLElement | null => {
  if (!(target instanceof Element)) return null;

  const element = target.closest("a, button");
  if (!(element instanceof HTMLElement) || element.matches(":disabled, [aria-disabled='true']")) {
    return null;
  }

  return element;
};

if (typeof document !== "undefined") {
  const activeRipples = new Map<number, HTMLElement>();

  document.addEventListener("pointerdown", (event) => {
    const element = getRippleTarget(event.target);
    if (!element) return;

    const bounds = element.getBoundingClientRect();
    const ripple = createRipple(element, event.clientX - bounds.left, event.clientY - bounds.top);
    activeRipples.set(event.pointerId, ripple);
  });

  const handlePointerRelease = (event: PointerEvent) => {
    const ripple = activeRipples.get(event.pointerId);
    if (!ripple) return;

    activeRipples.delete(event.pointerId);
    releaseRipple(ripple);
  };

  document.addEventListener("pointerup", handlePointerRelease);
  document.addEventListener("pointercancel", handlePointerRelease);

  document.addEventListener("click", (event) => {
    if (event.detail !== 0) return;

    const element = getRippleTarget(event.target);
    if (!element) return;

    const ripple = createRipple(element, element.clientWidth / 2, element.clientHeight / 2);
    window.setTimeout(() => releaseRipple(ripple), 120);
  });
}

registerServiceWorker();