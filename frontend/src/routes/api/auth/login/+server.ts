import { json, type RequestHandler } from '@sveltejs/kit';
import { PUBLIC_BACKEND_URL } from '$env/static/public';
import { dev } from '$app/environment';

export const POST: RequestHandler = async ({ request, cookies }) => {
    const { username, password } = await request.json();

    const response = await fetch(`${PUBLIC_BACKEND_URL}/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });

    if (!response.ok) {
        return json({ error: 'Login fehlgeschlagen' }, { status: 401 });
    }

    const result = await response.json();

    // Set httpOnly cookie - JS can't read it, XSS can't exfiltrate it
    cookies.set('jwt_token', result.token, {
        path: '/',
        httpOnly: true,
        secure: !dev, // only secure in production (HTTPS)
        sameSite: 'lax',
        maxAge: 60 * 60 * 24 * 7 // 7 days
    });

    return json({ token: result.token });
};
