import { fetchApi } from '$lib/fetchApi.js';
import { error } from '@sveltejs/kit';

export const load = async ({ params }) => {
    const { id } = params;

    // Fetch the delivery note data from the backend using the ID
    const json = await fetchApi(`delivery-notes/${id}`);
    
    if(json.error) {
        error(404, 'Delivery note not found');
    }

    return json;
}