import { fetchApi } from "$lib/api/fetchApi";

export interface ProductInput {
    name: string;
    salesPrice: number | null;
    sellable: boolean;
    rentable: boolean;
    depositId: string | null;
    quantityInCrate: number | null;
}

export const searchProducts = (query: string) =>
    fetchApi(`products/search?productName=${encodeURIComponent(query)}`, "GET");

export const createProduct = (payload: ProductInput) =>
    fetchApi("products", "POST", payload);

export const updateProduct = (id: string, payload: ProductInput) =>
    fetchApi(`products/${id}`, "PUT", payload);
