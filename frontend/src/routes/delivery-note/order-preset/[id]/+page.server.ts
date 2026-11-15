import { requestWorkers } from '$lib/api/server/requestWorkers';

export const load = async ({ params }) => {
    const res = await requestWorkers(`order-presets/${params.id}`);

    return { orderPreset: res };
}