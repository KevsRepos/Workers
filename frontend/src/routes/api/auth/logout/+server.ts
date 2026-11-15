import { json, type RequestHandler } from '@sveltejs/kit';

export const POST: RequestHandler = async ({ cookies, locals, fetch }) => {
    const response = await fetch(`/workers/logout`, {
        method: 'GET',
    });

    if (!response.ok) {
        return json({ error: await response.text() }, { status: 401 });
    }

    cookies.delete('jwt_token', { path: '/' });
    cookies.delete('refresh_token', { path: '/' });

    locals.loggedIn = false;
    
    return json({ success: true });
};
