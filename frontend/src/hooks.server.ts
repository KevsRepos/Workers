import type { Handle, HandleFetch } from "@sveltejs/kit";

export const handle: Handle = async ({ event, resolve }) => {
    event.locals.jwt = event.cookies.get('jwt_token');

    return await resolve(event);
}

export const handleFetch: HandleFetch = async ({ event, request, fetch }) => {
    if (event.locals.jwt) {
        request.headers.set('Authorization', `Bearer ${event.locals.jwt}`);
    }

    return fetch(request);
}