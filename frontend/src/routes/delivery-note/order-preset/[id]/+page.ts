import { backendApiUrl } from '$lib/api/backendApiUrl';
import { OrderPreset } from '$lib/modules/order-preset/model/OrderPreset';

export const load = async ({ params, fetch }) => {
    const res = await fetch(backendApiUrl(`order-presets/${params.id}`), {
        method: 'GET',
        headers: { 
            'Content-Type': 'application/json'
        },
        body: null
    });

    const data = await res.json();

    const orderPreset = new OrderPreset(data.id, data.name, data.items);

    return { orderPreset };
}