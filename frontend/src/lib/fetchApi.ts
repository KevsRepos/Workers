import { PUBLIC_BACKEND_URL } from "$env/static/public";
import { user } from "$lib/state/user.svelte";
import { invalidateAll } from "$app/navigation";

export const fetchApi = async (endpoint: string, method: string = 'GET', body: string | null = null) => {
    const headers: Record<string, string> = { 
        'Content-Type': 'application/json'
    };

    if (user.jwt) {
        headers['Authorization'] = `Bearer ${user.jwt}`;
    }

    const res = await fetch(`${PUBLIC_BACKEND_URL}/${endpoint}`, {
        method,
        headers,
        body
    });

    if (res.status === 401) {
        user.logout();
        await invalidateAll();
    }

    if(!res.ok) {
        const errorText = await res.text();
        throw new Error(`API request failed: ${res.status} ${res.statusText} - ${errorText}`);
    }

    return res.json();
}