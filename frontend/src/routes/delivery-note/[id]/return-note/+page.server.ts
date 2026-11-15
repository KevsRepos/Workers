import { requestWorkers } from '$lib/api/server/requestWorkers';

export const load = async ({ params, }) => {
    const res = await requestWorkers(`delivery-note/${params.id}`);

    return { deliveryNote: res.deliveryNote, returnUnions: res.returnUnions };
}