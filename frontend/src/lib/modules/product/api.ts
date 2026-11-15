import { requestWorkers } from "$lib/api/requestWorkers";

export interface ProductInput {
    name: string;
    salesPrice: number | null;
    sellable: boolean;
    rentable: boolean;
    depositId: string | null;
    quantityInCrate: number | null;
}

export const searchProducts = (query: string) =>
    requestWorkers(`products/search?productName=${encodeURIComponent(query)}`, "GET");

export const createProduct = (payload: ProductInput) =>
    requestWorkers("products", "POST", payload);

export const updateProduct = (id: string, payload: ProductInput) =>
    requestWorkers(`products/${id}`, "PUT", payload);
