import type { Handle, } from "@sveltejs/kit";

export const handle: Handle = async ({ event, resolve }) => {
    const token = event.cookies.get('jwt_token') ?? null;
    event.locals.loggedIn = !!token;

    return await resolve(event);
}