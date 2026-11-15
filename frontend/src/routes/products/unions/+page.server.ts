import { requestWorkers } from "$lib/api/server/requestWorkers.js";
import { error } from "@sveltejs/kit";

export const load = async () => {
    const res = await requestWorkers('product-unions');

    if(res.error) {
        error(500, 'Ein Fehler ist aufgetreten');
    }

    return { productUnions: res };
};
