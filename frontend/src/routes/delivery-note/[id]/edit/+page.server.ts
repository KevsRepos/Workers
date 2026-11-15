import { requestWorkers } from '$lib/api/server/requestWorkers';

export const load = async ({ params }) => {
    const { id } = params;

    const res = await requestWorkers(`delivery-note/${id}`);

    return res;
}