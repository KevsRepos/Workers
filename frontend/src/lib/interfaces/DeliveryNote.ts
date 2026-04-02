export interface DeliveryNote {
    id: number;
    deliveryDate: string; // ISO date string
    delivery: boolean;
    customerName: string;
    address: string;
    deliveryNoteProducts: DeliveryNoteProduct[];
    status: number,
    shortDescription?: string | null;
    assignment?: string | null;
}

export interface DeliveryNoteProduct {
    id: number;
    productId: number;
    name: string;
    quantity: number;
    product: Product;
    returnNoteEntry: ReturnNoteEntry | null;
}

export interface ReturnNoteEntry {
    id: string;
    returnedTotal: number | null;
    returnedTotalBottles: number | null;
    returnedFull: number | null;
    returnedFullBottles: number | null;
}

export interface ReturnUnion {
    name: string;
    isUnion: boolean;
    quantity: number;
    rentable: boolean;
    quantityInCrate: number | null;
    deposit: Deposit | null;
    deliveryNoteProductIds: string[];
    returnNoteEntry: ReturnNoteEntry | null;
}

export interface Product {
    id: number;
    name: string;
    salesPrice: number | null;
    deposit: Deposit | null;
    sellable: boolean;
    rentable: boolean;
    quantityInCrate: number | null;
}

export interface Deposit {
    id: number;
    singleAmount: number;
    crateAmount: number | null;
}