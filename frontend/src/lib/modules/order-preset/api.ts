import { requestWorkers } from "$lib/api/requestWorkers";

export const getOrderPresets = () => 
    requestWorkers("order-presets");

export const createOrderPreset = (payload: { name: string; productIds: string[] }) =>
    requestWorkers("order-presets", "POST", payload);

export const updateOrderPreset = (id: string, payload: { name: string; productIds: string[] }) =>
    requestWorkers(`order-presets/${id}`, "PUT", payload);