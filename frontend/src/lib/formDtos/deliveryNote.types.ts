interface DeliveryNoteProductDto {
    id?: string;
    productId: string;
    quantity: number | null;
    name: string;
}

interface Address {
    id: string;
    street: string;
    city: string;
    postalCode: string;
    country: string;
}

interface Customer {
    displayName?: string;
    id: string;
    firstName: string;
    surname: string;
    company: boolean;
    companyName?: string;
    defaultShippingAddress?: Address;
    defaultBillingAddress?: Address;
    addresses: Address[];
}

export type { Address, Customer, DeliveryNoteProductDto };
