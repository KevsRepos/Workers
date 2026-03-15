import { browser } from "$app/environment";
import { PUBLIC_BACKEND_URL } from "$env/static/public";
import { auth } from "$lib/auth.svelte";
import { fetchApi } from "$lib/fetchApi.js";
import { error } from "@sveltejs/kit";

export const load = async ({ parent, fetch}) => {
    const { token } = await parent();
    
    // if (!token) {
    //     return { deliveryNotes: [] };
    // }
    

    const res = await fetch(`${PUBLIC_BACKEND_URL}/delivery-notes/1`, {
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


    
    // const json = await fetchApi(
    //     'delivery-notes/1', 
    //     'GET', 
    //     null, 
    //     token, 
    //     fetch.bind(this, `${PUBLIC_BACKEND_URL}/delivery-notes/1`, undefined)
    // );

    if (json.error) {
        error(404, 'Ein Fehler ist aufgetreten');
    }

    return { deliveryNotes: json };
};