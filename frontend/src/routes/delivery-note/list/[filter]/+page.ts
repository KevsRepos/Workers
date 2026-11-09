import { browser } from "$app/environment";
import { invalidateAll } from "$app/navigation";
import { PUBLIC_BACKEND_URL } from "$env/static/public";
import { auth } from "$lib/state/auth.svelte";
import { error } from "@sveltejs/kit";

export const load = async ({ parent, fetch, params}) => {
    const { token } = await parent();
    const { filter } = params;
    
    // if (!token) {
    //     return { deliveryNotes: [] };
    // }

    const res = await fetch(`${PUBLIC_BACKEND_URL}/delivery-notes/${filter}`, {
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

    // if (!res.ok) {
        // const errorText = await res.text();
        // throw new Error(`API request failed: ${res.status} ${res.statusText} - ${errorText}`);
    // }

    const json = await res.json();


    
    if (json.error) {
        error(404, 'Ein Fehler ist aufgetreten');
    }    

    return { deliveryNotes: json, filter };
};