import { backendApiUrl } from '$lib/api/backendApiUrl';
import { error } from '@sveltejs/kit';

export const load = async ({ params, fetch }) => {
    const { id } = params;

    const res = await fetch(backendApiUrl(`delivery-note/${id}`), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        },
        body: null
    });

    if (!res.ok) {
        error(res.status === 404 ? 404 : 500, 'Delivery note not found');
    }

    return res.json();
}