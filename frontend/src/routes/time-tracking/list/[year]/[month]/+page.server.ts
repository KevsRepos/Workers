import { requestWorkers } from "$lib/api/server/requestWorkers";

export const load = async ({ params }) => {
    const { year, month } = params;

    const res = await requestWorkers(`time-sheets/${year}/${month}`);

    return { timeSheet: res, year: Number(year), month: Number(month) };
};