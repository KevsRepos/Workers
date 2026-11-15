import { requestWorkers } from "$lib/api/server/requestWorkers";
import { error } from "@sveltejs/kit";

export const load = async ({ params }) => {
    const { page, filter } = params;

    const res = await requestWorkers(`products/list/${filter}/${page}`);

    if (res.error) {
        error(500, 'Ein Fehler ist aufgetreten');
    }

    return { products: res, filter };
};