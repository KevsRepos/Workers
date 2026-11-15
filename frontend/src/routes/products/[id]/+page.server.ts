import { requestWorkers } from "$lib/api/server/requestWorkers.js";

export const load = async ({ params }) => {
    const { id } = params;

    const res = await requestWorkers(`products/${id}`);

    const depositRes = await requestWorkers('deposits');

    return { product: res, deposits: depositRes };
};