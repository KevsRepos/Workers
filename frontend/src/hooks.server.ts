import type { Handle, HandleFetch } from "@sveltejs/kit";

export const handle: Handle = async ({ event, resolve }) => {
    event.locals.loggedIn = !!event.cookies.get('jwt_token');

    return await resolve(event);
}

export const handleFetch: HandleFetch = async ({ event, request, fetch }) => {
    if (event.cookies.get('jwt_token')) {
        request.headers.set('Authorization', `Bearer ${event.cookies.get('jwt_token')}`);
    }

    const response = await fetch(request);

    // JWT expired - clear token and set loggedIn to false
    if (response.status === 401) {
        event.cookies.delete('jwt_token', { path: '/' });
        event.locals.loggedIn = false;
    }

    return response;
}