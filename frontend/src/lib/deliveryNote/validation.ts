import type { Customer, DeliveryNoteProductDto } from "$lib/formDtos/deliveryNote.types";

interface DeliveryNoteValidationInput {
    customer: Customer | null;
    deliveryDate: string;
    delivery: boolean | null;
    products: DeliveryNoteProductDto[];
}

const isDeliveryNoteSubmittable = (input: DeliveryNoteValidationInput): boolean => {
    return Boolean(input.customer)
        && input.deliveryDate.trim().length > 0
        && input.delivery !== null
        && input.products.length > 0;
};

const normalizePickupDate = (deliveryDate: string, pickupDate: string | null): string | null => {
    if (!pickupDate) {
        return pickupDate;
    }

    const deliveryDateValue = new Date(deliveryDate);
    const pickupDateValue = new Date(pickupDate);

    if (Number.isNaN(deliveryDateValue.getTime()) || Number.isNaN(pickupDateValue.getTime())) {
        return pickupDate;
    }

    if (pickupDateValue < deliveryDateValue) {
        return deliveryDate;
    }

    return pickupDate;
};

export { isDeliveryNoteSubmittable, normalizePickupDate };
export type { DeliveryNoteValidationInput };
