import { requestWorkers } from "$lib/api/requestWorkers";

export interface ProductUnionInput {
    name: string;
    productIds: string[];
}

export const createProductUnion = (payload: ProductUnionInput) =>
    requestWorkers("product-unions", "POST", payload);

export const updateProductUnion = (id: string, payload: ProductUnionInput) =>
    requestWorkers(`product-unions/${id}`, "PUT", payload);
