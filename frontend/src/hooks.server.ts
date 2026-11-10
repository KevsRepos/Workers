import type { Handle, HandleFetch } from "@sveltejs/kit";
import { PUBLIC_BACKEND_URL } from "$env/static/public";

export const handle: Handle = async ({ event, resolve }) => {
    const token = event.cookies.get('jwt_token') ?? null;
    event.locals.loggedIn = !!token;
    event.locals.token = token;

    return await resolve(event);
}

export const handleFetch: HandleFetch = async ({ event, request, fetch }) => {
    const isBackendRequest = new URL(request.url).origin === new URL(PUBLIC_BACKEND_URL).origin;

    if (isBackendRequest && event.locals.token) {
        request.headers.set('Authorization', `Bearer ${event.locals.token}`);
    }

    const response = await fetch(request);

    // JWT expired - clear token and set loggedIn to false
    if (isBackendRequest && response.status === 401) {
        event.cookies.delete('jwt_token', { path: '/' });
        event.locals.loggedIn = false;
        event.locals.token = null;
    }

    return response;
}