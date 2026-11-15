import { requestWorkers } from "$lib/api/server/requestWorkers.js";
import { error } from "@sveltejs/kit";

export const load = async ({ params }) => {
    const { filter } = params;

    const res = await requestWorkers(`delivery-notes/${filter}`, 'GET');

    if(res.error) {
        error(500, 'Ein Fehler ist aufgetreten');
    }

    return { deliveryNotes: res, filter };
};