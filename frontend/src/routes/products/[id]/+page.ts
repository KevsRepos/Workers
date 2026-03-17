import { browser } from "$app/environment";
import { PUBLIC_BACKEND_URL } from "$env/static/public";
import { auth } from "$lib/auth.svelte";
import { fetchApi } from "$lib/fetchApi.js";
import { error } from "@sveltejs/kit";

export const load = async ({ parent, fetch, params }) => {
    const { token } = await parent();
    const { id } = params;

    const res = await fetch(`${PUBLIC_BACKEND_URL}/products/${id}`, {
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
        const errorText = await res.text();

        console.log(errorText);

        throw new Error(`API request failed: ${res.status} ${res.statusText} - ${errorText}`);
    }

    const json = await res.json();

    const depositsRes = await fetch(`${PUBLIC_BACKEND_URL}/deposits`, {
        method: 'GET',
        headers: { 
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: null
    });

    const deposits = depositsRes.ok ? await depositsRes.json() : [];

    if (json.error) {
        error(404, 'Ein Fehler ist aufgetreten');
    }

    return { product: json, deposits };
};