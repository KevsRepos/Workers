export interface DeliveryNote {
    id: number;
    deliveryDate: string; // ISO date string
    delivery: boolean;
    customerName: string;
    address: string;
    deliveryNoteProducts: DeliveryNoteProduct[];
    status: number,
}

export interface DeliveryNoteProduct {
    id: number;
    productId: number;
    name: string;
    quantity: number;
    product: Product;
    returnedTotal: number | null;
    returnedTotalBottles: number | null;
    returnedFull: number | null;
    returnedFullBottles: number | null;
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