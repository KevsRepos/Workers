export interface Address {
    id: string;
    street: string;
    houseNumber: string;
    postalCode: string;
    city: string;
    country: string;
}

export interface Customer {
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
