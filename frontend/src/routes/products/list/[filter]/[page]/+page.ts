import { browser } from "$app/environment";
import { backendApiUrl } from "$lib/api/backendApiUrl";
import { auth } from "$lib/state/auth.svelte";
import { error } from "@sveltejs/kit";

export const load = async ({ fetch, params }) => {
    const { page, filter } = params;

    const res = await fetch(backendApiUrl(`products/list/${filter}/${page}`), {
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
        const errorText = await res.text();

        console.log(errorText);

        throw new Error(`API request failed: ${res.status} ${res.statusText} - ${errorText}`);
    }

    const json = await res.json();


    
    if (json.error) {
        error(404, 'Ein Fehler ist aufgetreten');
    }

    return { products: json, filter };
};