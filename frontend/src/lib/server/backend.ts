import { error, redirect, type RequestEvent } from '@sveltejs/kit';
import { env } from '$env/dynamic/private';
import { ApiError, errorFromResponse } from '$lib/api/errors';
import { clientAddress } from './session';

const TIMEOUT_MS = 15_000;

export const backendUrl = (path: string): string => {
	const base = (env.BACKEND_URL ?? 'http://localhost:8080').replace(/\/+$/, '');
	return `${base}/api/v1${path.startsWith('/') ? path : '/' + path}`;
};

export interface BackendRequest {
	method?: string;
	body?: unknown;
	token?: string | null;
	headers?: Record<string, string>;
}

/**
 * Low level call to the backend API. Forwards correlation id and client address so that
 * backend logs and the audit trail can be traced back to the original request.
 */
export const backendFetch = async (
	event: RequestEvent,
	path: string,
	request: BackendRequest = {}
): Promise<Response> => {
	const headers: Record<string, string> = {
		Accept: 'application/json',
		'X-Request-Id': event.locals.requestId,
		...request.headers
	};
	const token = request.token === undefined ? event.locals.accessToken : request.token;
	if (token) headers.Authorization = `Bearer ${token}`;
	const ip = clientAddress(event);
	if (ip) headers['X-Forwarded-For'] = ip;
	const userAgent = event.request.headers.get('user-agent');
	if (userAgent) headers['User-Agent'] = userAgent;

	let body: BodyInit | undefined;
	if (request.body !== undefined) {
		headers['Content-Type'] = 'application/json';
		body = typeof request.body === 'string' ? request.body : JSON.stringify(request.body);
	}

	try {
		return await fetch(backendUrl(path), {
			method: request.method ?? 'GET',
			headers,
			body,
			signal: AbortSignal.timeout(TIMEOUT_MS)
		});
	} catch (cause) {
		console.error(
			JSON.stringify({
				level: 'error',
				message: 'Backend unreachable',
				path,
				requestId: event.locals.requestId,
				cause: String(cause)
			})
		);
		return new Response(
			JSON.stringify({
				type: 'urn:workers:problem:backend-unavailable',
				title: 'Service unavailable',
				status: 503,
				detail: 'Der Server ist vorübergehend nicht erreichbar. Bitte versuche es gleich noch einmal.',
				requestId: event.locals.requestId
			}),
			{ status: 503, headers: { 'Content-Type': 'application/problem+json' } }
		);
	}
};

/**
 * Typed JSON call that throws ApiError on failure.
 */
export const backendJson = async <T>(event: RequestEvent, path: string, request: BackendRequest = {}): Promise<T> => {
	const response = await backendFetch(event, path, request);
	if (!response.ok) {
		throw await errorFromResponse(response);
	}
	if (response.status === 204) {
		return undefined as T;
	}
	return (await response.json()) as T;
};

/**
 * Convenience wrapper for load functions: maps API errors to SvelteKit errors/redirects.
 */
export const api = (event: RequestEvent) => ({
	get: <T>(path: string) => loadGuard(event, backendJson<T>(event, path)),
	send: <T>(method: string, path: string, body?: unknown) => backendJson<T>(event, path, { method, body })
});

const loadGuard = async <T>(event: RequestEvent, promise: Promise<T>): Promise<T> => {
	try {
		return await promise;
	} catch (e) {
		if (e instanceof ApiError) {
			if (e.status === 401) {
				redirect(303, `/login?next=${encodeURIComponent(event.url.pathname + event.url.search)}`);
			}
			error(e.status, {
				message: e.status >= 500 ? 'Der Server konnte die Anfrage nicht verarbeiten.' : e.message,
				requestId: e.requestId,
				status: e.status
			});
		}
		throw e;
	}
};
