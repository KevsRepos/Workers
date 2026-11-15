import { requestWorkers } from '$lib/api/server/requestWorkers';

export const load = async ({ params, depends }) => {

    const res = await requestWorkers(`delivery-notes/${params.id}/return-unions`);

    depends('delivery-note:return-unions');

    return { deliveryNote: res.deliveryNote, returnUnions: res.returnUnions };
}