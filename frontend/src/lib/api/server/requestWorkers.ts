import { getRequestEvent } from '$app/server';

export const requestWorkers = async (endpoint: string, method: string = 'GET', body: any = null) => {
    const { cookies, fetch } = getRequestEvent();

    try {
        if(!cookies.get('jwt_token') || isTokenExpired(cookies.get('jwt_token') ?? '')) {
            await refreshToken(fetch);
        }

        endpoint = endpoint.replace(/^\/+/, '');

        const res = await fetch(`/workers/${endpoint}`, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: body ? JSON.stringify(body) : null
        });

        if(!res.ok) {
            throw new Error(await res.text());
        }
        
        const json = await res.json();

        return json;
    } catch(error) {
        console.log(error);
        return { error: `API request failed: ${error}` };
    }
}

const isTokenExpired = (token: string): boolean => {
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));

        return !payload.exp || payload.exp <= Math.floor(Date.now() / 1000);
    } catch {
        return true;
    }
}

const refreshToken = async (fetch: (input: RequestInfo | URL, init?: RequestInit | undefined) => Promise<Response>) => {
    const res = await fetch(`/workers/refresh-token`, {
        method: 'GET',
        credentials: 'same-origin'
    });

    if (!res.ok) {
        const errorText = await res.text();
        throw new Error(`API request failed: ${res.status} ${res.statusText} - ${errorText} `);
    }

    return true;
}