import { PUBLIC_BACKEND_URL } from '$env/static/public';
import { error } from '@sveltejs/kit';

export const load = async ({ parent, params, fetch }) => {
    const { token } = await parent();
    // const res = await fetchApi(`delivery-notes/${params.id}`);

    const res = await fetch(`${PUBLIC_BACKEND_URL}/delivery-note/${params.id}`, {
        method: 'GET',
        headers: { 
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: null
    });

    // if (res.error) {
    //     error(404, 'Delivery note not found');
    // }

    const data = await res.json();

    return { deliveryNote: data };
}