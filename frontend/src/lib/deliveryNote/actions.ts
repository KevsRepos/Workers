import type { DeliveryNoteProductDto } from "$lib/formDtos/deliveryNote.types";

interface DeliveryNoteProductsContainer {
    products: DeliveryNoteProductDto[];
}

const addProductToDeliveryNote = (
    form: DeliveryNoteProductsContainer,
    productId: string,
    quantity: number | null,
    name: string,
): void => {
    const existing = form.products.find((product) => product.productId === productId);
    if (existing) {
        return;
    }

    form.products.push({ productId, quantity, name });
};

const updateDeliveryNoteProductQuantity = (
    form: DeliveryNoteProductsContainer,
    productId: string,
    quantity: number,
): void => {
    const product = form.products.find((entry) => entry.productId === productId);
    if (!product) {
        return;
    }

    product.quantity = quantity;
};

export { addProductToDeliveryNote, updateDeliveryNoteProductQuantity };
export type { DeliveryNoteProductsContainer };
