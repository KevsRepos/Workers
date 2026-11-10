import { browser } from "$app/environment";
import { backendApiUrl } from "$lib/api/backendApiUrl";
import { auth } from "$lib/state/auth.svelte";

export const load = async ({ fetch, params }) => {
    const { id } = params;

    const res = await fetch(backendApiUrl(`product-unions/${id}`), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        },
        body: null
    });

    if (res.status === 401 && browser) {
        await auth.logout();
    }

    if (!res.ok) {
        throw new Error(`API request failed: ${res.status}`);
    }

    const json = await res.json();

    return { productUnion: json };
};
