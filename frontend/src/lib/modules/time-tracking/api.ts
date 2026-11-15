import { requestWorkers } from "$lib/api/requestWorkers";

export const createCurrentTimeSheet = (month: number, year: number) =>
    requestWorkers("time-sheets/current/create", "POST", { month, year });

export const createTimeSheetEntry = (timeSheetId: string | null, payload: object) =>
    requestWorkers(`time-sheets/${timeSheetId}/entries`, "POST", payload);

export const updateTimeSheetEntry = (entryId: string, payload: object) =>
    requestWorkers(`time-sheets/entries/${entryId}`, "PUT", payload);
