import { PUBLIC_BACKEND_URL } from '$env/static/public';
import { error } from '@sveltejs/kit';

export const load = async ({ parent, params, fetch }) => {
    const { token } = await parent();

    const res = await fetch(`${PUBLIC_BACKEND_URL}/delivery-notes/${params.id}/return-unions`, {
        method: 'GET',
        headers: { 
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: null
    });

    if (!res.ok) {
        error(res.status, 'Fehler beim Laden');
    }

    const data = await res.json();

    return { deliveryNote: data.deliveryNote, returnUnions: data.returnUnions };
}