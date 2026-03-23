import { browser } from "$app/environment";
import { PUBLIC_BACKEND_URL } from "$env/static/public";
import { auth } from "$lib/auth.svelte";

export const load = async ({ parent, fetch, params }) => {
    const { token } = await parent();
    const { id } = params;

    const res = await fetch(`${PUBLIC_BACKEND_URL}/product-unions/${id}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
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
