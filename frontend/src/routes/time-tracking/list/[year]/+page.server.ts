import { requestWorkers } from "$lib/api/server/requestWorkers";

export const load = async ({ params }) => {
    const { year } = params;

    const [sheetsRes, yearsRes] = await Promise.all([
        requestWorkers(`time-sheets/${year}`),
        requestWorkers('time-sheets/years')
    ]);

    return { timeSheets: sheetsRes, years: yearsRes, year };
};