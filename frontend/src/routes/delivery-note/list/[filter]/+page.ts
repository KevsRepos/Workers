import { browser } from "$app/environment";
import { invalidateAll } from "$app/navigation";
import { backendApiUrl } from "$lib/api/backendApiUrl";
import { auth } from "$lib/state/auth.svelte";
import { error } from "@sveltejs/kit";

export const load = async ({ fetch, params}) => {
    const { filter } = params;
    
    // if (!token) {
    //     return { deliveryNotes: [] };
    // }

    const res = await fetch(backendApiUrl(`delivery-notes/${filter}`), {
        method: 'GET',
        headers: { 
            'Content-Type': 'application/json'
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