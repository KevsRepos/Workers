import type { Handle, HandleFetch } from "@sveltejs/kit";
import { PUBLIC_BACKEND_URL } from "$env/static/public";
import { env } from "$env/dynamic/private";

export const handle: Handle = async ({ event, resolve }) => {
    const token = event.cookies.get('jwt_token') ?? null;
    event.locals.loggedIn = !!token;
    event.locals.token = token;

    return await resolve(event);
}

export const handleFetch: HandleFetch = async ({ event, request, fetch }) => {
    const requestUrl = new URL(request.url);
    const backendBaseUrl = new URL(PUBLIC_BACKEND_URL);
    const backendBasePath = backendBaseUrl.pathname.replace(/\/+$/, '');
    const isBackendPath = backendBasePath === ''
        || requestUrl.pathname === backendBasePath
        || requestUrl.pathname.startsWith(`${backendBasePath}/`);
    const isBackendRequest = requestUrl.origin === backendBaseUrl.origin && isBackendPath;
    let backendRequest = request;

    if (isBackendRequest && env.BACKEND_INTERNAL_URL) {
        const internalBaseUrl = new URL(env.BACKEND_INTERNAL_URL);
        const internalBasePath = internalBaseUrl.pathname.replace(/\/+$/, '');
        const endpointPath = requestUrl.pathname.slice(backendBasePath.length);
        const internalUrl = new URL(internalBaseUrl.origin);
        internalUrl.pathname = `${internalBasePath}${endpointPath}` || '/';
        internalUrl.search = requestUrl.search;
        backendRequest = new Request(internalUrl, request);
    }

    if (isBackendRequest && event.locals.token) {
        const headers = new Headers(backendRequest.headers);
        headers.set('Authorization', `Bearer ${event.locals.token}`);
        backendRequest = new Request(backendRequest, { headers });
    }

    const response = await fetch(backendRequest);

    // JWT expired - clear token and set loggedIn to false
    if (isBackendRequest && response.status === 401) {
        event.cookies.delete('jwt_token', { path: '/' });
        event.locals.loggedIn = false;
        event.locals.token = null;
    }

    return response;
}