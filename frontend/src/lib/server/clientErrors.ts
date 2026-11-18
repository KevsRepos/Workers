/**
 * Validation, minimisation and rate limiting for error reports sent by browsers.
 */

export interface ClientErrorReport {
	reference: string;
	kind: 'error' | 'unhandledrejection' | 'navigation';
	message: string;
	stack: string | null;
	path: string;
	status: number | null;
	release: string | null;
}

const MAX_MESSAGE = 500;
const MAX_STACK = 4000;
const KINDS = ['error', 'unhandledrejection', 'navigation'] as const;

/** Removes e-mail addresses, tokens and query strings so reports contain no personal data. */
export const scrub = (value: string): string =>
	value
		.replace(/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/g, '[email]')
		.replace(/eyJ[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+/g, '[token]')
		.replace(/\b(Bearer|token|password|passwort)\b(?:\s*[:=]\s*|\s+)(?!\[)\S+/gi, '$1 [redacted]')
		.replace(/(https?:\/\/[^\s?#)]+)\?[^\s#)]*/g, '$1');

const text = (value: unknown, max: number): string | null =>
	typeof value === 'string' && value.trim() !== '' ? scrub(value.trim()).slice(0, max) : null;

export const sanitizeReport = (body: unknown): ClientErrorReport | null => {
	if (typeof body !== 'object' || body === null) return null;
	const raw = body as Record<string, unknown>;

	const reference =
		typeof raw.reference === 'string' && /^[A-Za-z0-9-]{8,64}$/.test(raw.reference) ? raw.reference : null;
	const kind = KINDS.find((k) => k === raw.kind) ?? null;
	const message = text(raw.message, MAX_MESSAGE);
	if (!reference || !kind || !message) return null;

	const path =
		typeof raw.path === 'string' && raw.path.startsWith('/') ? raw.path.split(/[?#]/)[0]!.slice(0, 200) : '/';
	const status =
		typeof raw.status === 'number' && Number.isInteger(raw.status) && raw.status >= 100 && raw.status < 600
			? raw.status
			: null;
	const release = typeof raw.release === 'string' && /^[A-Za-z0-9._-]{1,40}$/.test(raw.release) ? raw.release : null;

	return { reference, kind, message, stack: text(raw.stack, MAX_STACK), path, status, release };
};

/**
 * Fixed-window limiter per key (client address). Keeps a broken page or an abusive client from
 * flooding the logs.
 */
export class RateLimiter {
	private readonly windows = new Map<string, { start: number; count: number }>();

	constructor(
		private readonly limit: number,
		private readonly windowMs: number,
		private readonly now: () => number = Date.now
	) {}

	allow(key: string): boolean {
		const now = this.now();
		if (this.windows.size > 10_000) {
			for (const [k, w] of this.windows) if (now - w.start >= this.windowMs) this.windows.delete(k);
		}
		const window = this.windows.get(key);
		if (!window || now - window.start >= this.windowMs) {
			this.windows.set(key, { start: now, count: 1 });
			return true;
		}
		window.count++;
		return window.count <= this.limit;
	}
}
