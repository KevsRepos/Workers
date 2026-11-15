import { json, type RequestHandler } from '@sveltejs/kit';
import { PUBLIC_BACKEND_URL } from '$env/static/public';
import { dev } from '$app/environment';

export const POST: RequestHandler = async ({ request, cookies, fetch }) => {
    const { username, password } = await request.json();

    const response = await fetch(`${PUBLIC_BACKEND_URL.replace(/\/+$/, '')}/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });

    if (!response.ok) {
        return json({ error: await response.text() }, { status: 401 });
    }

    const httpCookies = await response.headers.getSetCookie();

    for (const cookie of httpCookies) {
        const [nameValue, ...attributes] = cookie.split(';');
        const separatorIndex = nameValue.indexOf('=');

        const name = nameValue.slice(0, separatorIndex).trim();
        const value = nameValue.slice(separatorIndex + 1).trim();


        const pathAttribute = attributes.find(a =>
            a.trim().toLowerCase().startsWith('path=')
        );

        const path = pathAttribute?.split('=')[1].trim() ?? '/';

        if(name !== 'refresh_token' && name !== 'jwt_token') {
            continue;
        }

        const options = {
            path: path,
            httpOnly: attributes.some(a => a.trim().toLowerCase() === 'httponly'),
            secure: !dev,
            sameSite: 'lax' as const,
            expires: undefined as Date | undefined
        };

        const expires = attributes.find(a => a.trim().toLowerCase().startsWith('expires='));

        if (expires) {
            options.expires = new Date(expires.trim().slice(8));
        }

        cookies.set(name, value, options);
    }

    return json({ success: true });
};
