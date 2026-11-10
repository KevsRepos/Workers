import { browser } from "$app/environment";
import { backendApiUrl } from "$lib/api/backendApiUrl";
import { auth } from "$lib/state/auth.svelte";

export const load = async ({ fetch }) => {

    const res = await fetch(backendApiUrl('product-unions'), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        },
        body: null
    });

    if (res.status === 401 && browser) {
        await auth.logout();
    }

    const json = res.ok ? await res.json() : [];

    return { productUnions: json };
};
