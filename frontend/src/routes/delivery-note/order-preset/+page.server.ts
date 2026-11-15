import { requestWorkers } from "$lib/api/server/requestWorkers";

export const load = async () => {
    const res = await requestWorkers('order-presets');

    return {
        orderPresets: res
    };
};