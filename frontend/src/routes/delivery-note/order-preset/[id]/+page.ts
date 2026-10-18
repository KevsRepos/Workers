import { PUBLIC_BACKEND_URL } from '$env/static/public';
import { OrderPreset } from '$lib/dto/orderPreset/OrderPreset';

export const load = async ({ parent, params, fetch }) => {
    const { token } = await parent();

    const res = await fetch(`${PUBLIC_BACKEND_URL}/order-presets/${params.id}`, {
        method: 'GET',
        headers: { 
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: null
    });

    const data = await res.json();

    const orderPreset = new OrderPreset(data.id, data.name, data.items);

    return { orderPreset };
}