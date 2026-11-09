import { fetchApi } from "$lib/api/fetchApi";

export const createCurrentTimeSheet = (month: number, year: number) =>
    fetchApi("time-sheets/current/create", "POST", { month, year });

export const createTimeSheetEntry = (timeSheetId: string | null, payload: object) =>
    fetchApi(`time-sheets/${timeSheetId}/entries`, "POST", payload);

export const updateTimeSheetEntry = (entryId: string, payload: object) =>
    fetchApi(`time-sheets/entries/${entryId}`, "PUT", payload);
