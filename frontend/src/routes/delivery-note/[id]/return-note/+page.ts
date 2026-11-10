import { backendApiUrl } from '$lib/api/backendApiUrl';
import { error } from '@sveltejs/kit';

export const load = async ({ params, fetch }) => {
    const res = await fetch(backendApiUrl(`delivery-notes/${params.id}/return-unions`), {
        method: 'GET',
        headers: { 
            'Content-Type': 'application/json'
        },
        body: null
    });

    if (!res.ok) {
        error(res.status, 'Fehler beim Laden');
    }

    const data = await res.json();

    return { deliveryNote: data.deliveryNote, returnUnions: data.returnUnions };
}