import type { Deposit } from './Deposit';

export interface Product {
    id: string;
    name: string;
    salesPrice: number | null;
    deposit: Deposit | null;
    sellable: boolean;
    rentable: boolean;
    quantityInCrate: number | null;
    createdAt: string;
    updatedAt: string | null;
}
