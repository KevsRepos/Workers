import { PUBLIC_BACKEND_URL } from '$env/static/public';

export const load = async ({ cookies, fetch }) => {
    if (!cookies.get('jwt_token')) return { deliveryNotes: [] };

    const response = await fetch(`${PUBLIC_BACKEND_URL}/delivery-notes/1`);

    if (response.ok) {
        const deliveryNotes = await response.json();
                
        return { deliveryNotes };
    }

    return { deliveryNotes: [] };
};