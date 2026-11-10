import { backendApiUrl } from '$lib/api/backendApiUrl';

export const load = async ({ params, fetch, depends }) => {

    const res = await fetch(backendApiUrl(`delivery-notes/${params.id}/return-unions`), {
        method: 'GET',
        headers: { 
            'Content-Type': 'application/json'
        },
        body: null
    });

    const data = await res.json();

    depends('delivery-note:return-unions');

    return { deliveryNote: data.deliveryNote, returnUnions: data.returnUnions };
}