import { fetchApi } from "$lib/fetchApi.js";
import { error } from "@sveltejs/kit";

export const load = async ({ parent, fetch }) => {
    const { token } = await parent();
    
    if (!token) {
        return { deliveryNotes: [] };
    }
    
    const json = await fetchApi('delivery-notes/1', 'GET', null, token, fetch);

    if (json.error) {
        error(404, 'Ein Fehler ist aufgetreten');
    }

    return { deliveryNotes: json };
};