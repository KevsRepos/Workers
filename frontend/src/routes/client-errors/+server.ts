import type { RequestHandler } from './$types';
import { version } from '$app/environment';
import { RateLimiter, sanitizeReport } from '$lib/server/clientErrors';
import { clientAddress } from '$lib/server/session';

const MAX_BODY = 16_384;
const limiter = new RateLimiter(20, 60_000);

/**
 * Receives JavaScript errors from browsers and writes them to the server log (JSON, stderr),
 * correlated by the reference the user sees. Public so that errors on the login page are captured.
 */
export const POST: RequestHandler = async (event) => {
	const { request, url, locals } = event;

	if (request.headers.get('origin') !== url.origin) return new Response(null, { status: 403 });
	if (!(request.headers.get('content-type') ?? '').startsWith('application/json'))
		return new Response(null, { status: 415 });

	const ip = clientAddress(event) ?? 'unknown';
	if (!limiter.allow(ip)) return new Response(null, { status: 429 });

	const raw = await request.text();
	if (raw.length > MAX_BODY) return new Response(null, { status: 413 });

	let body: unknown;
	try {
		body = JSON.parse(raw);
	} catch {
		return new Response(null, { status: 400 });
	}

	const report = sanitizeReport(body);
	if (!report) return new Response(null, { status: 400 });

	console.error(
		JSON.stringify({
			level: 'error',
			source: 'browser',
			message: `Client error: ${report.message}`,
			reference: report.reference,
			kind: report.kind,
			path: report.path,
			status: report.status,
			stack: report.stack,
			release: report.release ?? version,
			serverVersion: version,
			userId: locals.user?.id ?? null,
			userAgent: request.headers.get('user-agent')?.slice(0, 200) ?? null,
			requestId: locals.requestId,
			timestamp: new Date().toISOString()
		})
	);

	return new Response(null, { status: 204 });
};
