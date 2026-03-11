import { json, type RequestHandler } from '@sveltejs/kit';

export const POST: RequestHandler = async ({ cookies }) => {
    cookies.delete('jwt_token', { path: '/' });

    return json({ success: true });
};
