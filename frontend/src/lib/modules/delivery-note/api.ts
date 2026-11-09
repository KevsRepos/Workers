import { fetchApi } from "$lib/api/fetchApi";

export const createDeliveryNote = (payload: object) =>
    fetchApi("delivery-notes", "POST", payload);

export const updateDeliveryNote = (id: string | null, payload: object) =>
    fetchApi(`delivery-notes/${id}`, "PUT", payload);

export const updateDeliveryNoteStatus = (id: string, status: number) =>
    fetchApi(`delivery-note/${id}/status/${status}`, "PUT");

export const updateDeliveryNoteProductQuantities = (
    id: string,
    products: Array<{ id: string; quantity: number; productId: string }>,
) => fetchApi(`delivery-notes/${id}/products/quantities`, "PUT", { products });

export const createReturnNote = (id: string, payload: object) =>
    fetchApi(`delivery-notes/${id}/return-note`, "POST", payload);
