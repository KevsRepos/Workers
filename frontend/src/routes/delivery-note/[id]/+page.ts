import { PUBLIC_BACKEND_URL } from '$env/static/public';

export const load = async ({ parent, params, fetch, depends }) => {
    const { token } = await parent();

    const res = await fetch(`${PUBLIC_BACKEND_URL}/delivery-notes/${params.id}/return-unions`, {
        method: 'GET',
        headers: { 
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: null
    });

    const data = await res.json();

    depends('delivery-note:return-unions');

    return { deliveryNote: data.deliveryNote, returnUnions: data.returnUnions };
}