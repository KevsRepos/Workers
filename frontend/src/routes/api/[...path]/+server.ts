import type { RequestHandler } from './$types';
import { backendFetch } from '$lib/server/backend';
import { allowedBackendPath } from '$lib/server/proxy';

const problem = (status: number, title: string, detail: string, requestId: string) =>
	new Response(JSON.stringify({ type: 'urn:workers:problem:proxy', title, status, detail, requestId }), {
		status,
		headers: { 'Content-Type': 'application/problem+json' }
	});

/**
 * Same-origin JSON proxy used by interactive client components. The browser never sees the
 * access token; it is attached here from the HttpOnly session cookie.
 */
const proxy: RequestHandler = async (event) => {
	const { request, params, url, locals } = event;
	const path = allowedBackendPath(params.path);
	if (!path) {
		return problem(404, 'Not found', 'Unbekannter API-Pfad.', locals.requestId);
	}

	let body: string | undefined;
	if (request.method !== 'GET') {
		// CSRF defence in depth: state changing calls must originate from our own pages and be JSON.
		const origin = request.headers.get('origin');
		if (origin !== url.origin) {
			return problem(403, 'Forbidden', 'Ungültiger Ursprung der Anfrage.', locals.requestId);
		}
		const contentType = request.headers.get('content-type') ?? '';
		body = await request.text();
		if (body !== '' && !contentType.startsWith('application/json')) {
			return problem(415, 'Unsupported media type', 'Nur JSON wird akzeptiert.', locals.requestId);
		}
		if (body.length > 1_000_000) {
			return problem(413, 'Payload too large', 'Die Anfrage ist zu groß.', locals.requestId);
		}
	}

	const response = await backendFetch(event, path + url.search, {
		method: request.method,
		body: body === undefined || body === '' ? undefined : body
	});

	const headers = new Headers();
	const contentType = response.headers.get('content-type');
	if (contentType) headers.set('Content-Type', contentType);
	headers.set('Cache-Control', 'no-store');
	return new Response(response.status === 204 ? null : response.body, { status: response.status, headers });
};

export const GET = proxy;
export const POST = proxy;
export const PUT = proxy;
export const DELETE = proxy;
