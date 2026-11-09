import { fetchApi } from "$lib/api/fetchApi";

export interface ProductUnionInput {
    name: string;
    productIds: string[];
}

export const createProductUnion = (payload: ProductUnionInput) =>
    fetchApi("product-unions", "POST", payload);

export const updateProductUnion = (id: string, payload: ProductUnionInput) =>
    fetchApi(`product-unions/${id}`, "PUT", payload);
