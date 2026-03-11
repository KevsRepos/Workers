import { fetchApi } from '$lib/fetchApi.js';
import { error } from '@sveltejs/kit';

export const load = async ({ params, fetch }) => {
    const res = await fetchApi(`delivery-notes/${params.id}`);

    if (res.error) {
        error(404, 'Delivery note not found');
    }   

    return res;
}