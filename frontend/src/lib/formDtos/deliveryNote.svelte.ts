import type { Customer, DeliveryNoteProductDto } from "$lib/formDtos/deliveryNote.types";

export class DeliveryNoteForm {
    id: string|null = $state(null);
    customer: Customer | null = $state(null);
    deliveryDate: string = $state('');
    delivery = $state<boolean|null>();
    pickupDate: string | null = $state(null);
    products: DeliveryNoteProductDto[] = $state([]);
    shortDescription: string = $state('');
    privateDescription: string = $state('');
    assignment: string = $state('');
    shippingAddressId: string|null = $state(null);
    billingAddressId: string|null = $state(null);
    adultGuests: number|null = $state(null);
    childGuests: number|null = $state(null);
    callBefore: boolean = $state(false);
}

// export const deliveryNoteForm = new DeliveryNoteForm();
export type { Address, Customer, DeliveryNoteProductDto } from "$lib/formDtos/deliveryNote.types";
