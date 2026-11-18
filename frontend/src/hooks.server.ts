import { redirect, type Handle, type HandleServerError } from '@sveltejs/kit';
import { sequence } from '@sveltejs/kit/hooks';
import {
	ACCESS_COOKIE,
	REFRESH_COOKIE,
	clearTokens,
	decodeClaims,
	isFresh,
	storeTokens,
	userFromClaims
} from '$lib/server/session';
import { refreshSession } from '$lib/server/refresh';

const PUBLIC_PATHS = ['/login', '/health', '/client-errors'];

const isPublic = (pathname: string): boolean =>
	PUBLIC_PATHS.some((p) => pathname === p || pathname.startsWith(p + '/'));

const requestId: Handle = async ({ event, resolve }) => {
	const incoming = event.request.headers.get('x-request-id');
	event.locals.requestId = incoming && /^[A-Za-z0-9._-]{8,64}$/.test(incoming) ? incoming : crypto.randomUUID();
	const response = await resolve(event);
	response.headers.set('X-Request-Id', event.locals.requestId);
	return response;
};

const securityHeaders: Handle = async ({ event, resolve }) => {
	const response = await resolve(event);
	const headers = response.headers;
	headers.set('X-Content-Type-Options', 'nosniff');
	headers.set('X-Frame-Options', 'DENY');
	headers.set('Referrer-Policy', 'strict-origin-when-cross-origin');
	headers.set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
	headers.set('Cross-Origin-Opener-Policy', 'same-origin');
	if (event.locals.user && !headers.has('Cache-Control')) {
		// Authenticated pages contain personal data: never store them in shared or browser caches.
		headers.set('Cache-Control', 'private, no-store');
	}
	return response;
};

/**
 * Establishes the session from HttpOnly cookies and transparently renews expired access tokens.
 */
const session: Handle = async ({ event, resolve }) => {
	event.locals.user = null;
	event.locals.accessToken = null;

	const accessToken = event.cookies.get(ACCESS_COOKIE());
	const claims = accessToken ? decodeClaims(accessToken) : null;

	if (accessToken && claims && isFresh(claims)) {
		event.locals.accessToken = accessToken;
		event.locals.user = userFromClaims(claims);
	} else {
		const refreshToken = event.cookies.get(REFRESH_COOKIE());
		if (refreshToken) {
			const pair = await refreshSession(event, refreshToken);
			if (pair) {
				storeTokens(event.cookies, pair);
				const newClaims = decodeClaims(pair.accessToken);
				event.locals.accessToken = pair.accessToken;
				event.locals.user = newClaims ? userFromClaims(newClaims) : null;
			} else {
				clearTokens(event.cookies);
			}
		}
	}

	return resolve(event);
};

const guard: Handle = async ({ event, resolve }) => {
	const { pathname, search } = event.url;
	if (!event.locals.user && !isPublic(pathname)) {
		if (pathname.startsWith('/api/')) {
			return new Response(
				JSON.stringify({
					type: 'urn:workers:problem:unauthenticated',
					title: 'Authentication required',
					status: 401,
					detail: 'Deine Sitzung ist abgelaufen. Bitte melde dich erneut an.',
					requestId: event.locals.requestId
				}),
				{ status: 401, headers: { 'Content-Type': 'application/problem+json' } }
			);
		}
		redirect(303, `/login?next=${encodeURIComponent(pathname + search)}`);
	}
	if (event.locals.user && pathname === '/login') {
		redirect(303, '/');
	}
	return resolve(event);
};

export const handle = sequence(requestId, securityHeaders, session, guard);

export const handleError: HandleServerError = ({ error, event, status, message }) => {
	if (status !== 404) {
		console.error(
			JSON.stringify({
				level: 'error',
				message: 'Unhandled server error',
				status,
				path: event.url.pathname,
				requestId: event.locals.requestId,
				error: error instanceof Error ? { name: error.name, message: error.message, stack: error.stack } : String(error)
			})
		);
	}
	return {
		message:
			status === 404 ? 'Seite nicht gefunden.' : status >= 500 ? 'Ein unerwarteter Fehler ist aufgetreten.' : message,
		requestId: event.locals.requestId,
		status
	};
};
