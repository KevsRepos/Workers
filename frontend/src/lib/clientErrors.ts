import { version } from '$app/environment';

/**
 * Browser-side error reporting to /client-errors. Each report carries a reference that is also
 * shown to the user, so support can find the matching server log entry.
 */

const MAX_REPORTS_PER_PAGE = 10;
const sent = new Set<string>();
let count = 0;

export const newReference = (): string => crypto.randomUUID();

export const reportClientError = (
	kind: 'error' | 'unhandledrejection' | 'navigation',
	error: unknown,
	options: { reference?: string; status?: number } = {}
): string => {
	const reference = options.reference ?? newReference();
	const message =
		error instanceof Error
			? `${error.name}: ${error.message}`
			: typeof error === 'string'
				? error
				: JSON.stringify(error ?? 'unknown');
	const stack = error instanceof Error ? (error.stack ?? null) : null;

	const fingerprint = `${kind}|${message}|${location.pathname}`;
	if (sent.has(fingerprint) || count >= MAX_REPORTS_PER_PAGE) return reference;
	sent.add(fingerprint);
	count++;

	try {
		void fetch('/client-errors', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				reference,
				kind,
				message,
				stack,
				path: location.pathname,
				status: options.status ?? null,
				release: version
			}),
			keepalive: true,
			credentials: 'same-origin'
		}).catch(() => undefined);
	} catch {
		// reporting must never break the page
	}

	return reference;
};
