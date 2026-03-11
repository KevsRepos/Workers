import { PUBLIC_BACKEND_URL } from '$env/static/public'

export const load = async ({ params, fetch }) => {
    const res = await fetch(`${PUBLIC_BACKEND_URL}/delivery-note/${params.id}`);

    if(res.ok) {
        const deliveryNote = await res.json();
        return { deliveryNote };
    }

    console.error('Failed to fetch delivery note:', await res.text());
    return { deliveryNote: null };
}