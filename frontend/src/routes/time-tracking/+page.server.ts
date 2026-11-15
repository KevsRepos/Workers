import { requestWorkers } from "$lib/api/server/requestWorkers.js";

export const load = async () => {
    const res = await requestWorkers('time-sheets/current');

    return { timeSheet: res };
};