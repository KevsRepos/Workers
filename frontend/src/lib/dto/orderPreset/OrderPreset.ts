export interface OrderPresetProduct {
    id: string;
    name: string;
}

export interface OrderPresetItem {
    id: string | null;
    product: OrderPresetProduct;
}

export class OrderPreset {
    id: string | null;
    name: string;
    items: OrderPresetItem[];

    constructor(id: string | null, name: string, items: OrderPresetItem[]) {
        this.id = id;
        this.name = name;
        this.items = items;
    }
}