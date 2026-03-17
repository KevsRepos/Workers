import { json, type RequestHandler } from '@sveltejs/kit';

export const POST: RequestHandler = async ({ cookies, locals }) => {
    cookies.delete('jwt_token', { path: '/' });

    locals.loggedIn = false;
    locals.token = null;
    
    return json({ success: true });
};
