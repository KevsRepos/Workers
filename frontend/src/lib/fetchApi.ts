import { PUBLIC_BACKEND_URL } from "$env/static/public";
import { auth } from "$lib/auth.svelte";
import { browser } from "$app/environment";

export const fetchApi = async (
    endpoint: string, 
    method: string = 'GET', 
    body: any = null, 
    token?: string | null,
    customFetch: typeof fetch | null = fetch
) => {
    // On server: use passed token, on client: use auth.token
    const authToken = browser ? auth.token : token;

    const headers: Record<string, string> = { 
        'Content-Type': 'application/json'
    };
    
    if (authToken) {
        headers['Authorization'] = `Bearer ${authToken}`;
    }

    if(!customFetch) {
        customFetch = fetch;
    }

    const res = await customFetch(`${PUBLIC_BACKEND_URL}/${endpoint}`, {
        method,
        headers,
        body: body ? JSON.stringify(body) : null
    });

    if (res.status === 401 && browser) {
        await auth.logout();
    }

    if (!res.ok) {
        const errorText = await res.text();
        throw new Error(`API request failed: ${res.status} ${res.statusText} - ${errorText} `);
    }

    return res.json();
}