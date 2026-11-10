import { auth } from "$lib/state/auth.svelte";
import { backendApiUrl } from "$lib/api/backendApiUrl";
import { browser } from "$app/environment";

export const fetchApi = async (
    endpoint: string, 
    method: string = 'GET', 
    body: any = null, 
    customFetch: typeof fetch | null = fetch
) => {
    const headers: Record<string, string> = { 
        'Content-Type': 'application/json'
    };

    if(!customFetch) {
        customFetch = fetch;
    }

    const res = await customFetch(backendApiUrl(endpoint), {
        method,
        headers,
        credentials: 'same-origin',
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