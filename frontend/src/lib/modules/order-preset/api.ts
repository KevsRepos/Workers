import { fetchApi } from "$lib/api/fetchApi";

export const getOrderPresets = () =>
    fetchApi("order-presets", "GET");

export const createOrderPreset = (payload: { name: string; productIds: string[] }) =>
    fetchApi("order-presets", "POST", payload);

export const updateOrderPreset = (id: string, payload: { name: string; productIds: string[] }) =>
    fetchApi(`order-presets/${id}`, "PUT", payload);
