import { requestWorkers } from "$lib/api/requestWorkers";

export const createDeliveryNote = (payload: object) =>
    requestWorkers("delivery-notes", "POST", payload);

export const updateDeliveryNote = (id: string | null, payload: object) =>
    requestWorkers(`delivery-notes/${id}`, "PUT", payload);

export const updateDeliveryNoteStatus = (id: string, status: number) =>
    requestWorkers(`delivery-note/${id}/status/${status}`, "PUT");

export const updateDeliveryNoteProductQuantities = (
    id: string,
    products: Array<{ id: string; quantity: number; productId: string }>,
) => requestWorkers(`delivery-notes/${id}/products/quantities`, "PUT", { products });

export const createReturnNote = (id: string, payload: object) =>
    requestWorkers(`delivery-notes/${id}/return-note`, "POST", payload);
