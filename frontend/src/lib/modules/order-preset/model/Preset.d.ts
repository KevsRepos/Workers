import type { Product } from '$lib/modules/product/model/Product';

interface Preset {
    id: string;
    name: string;
    items: PresetItem[];
}

interface PresetItem {
    createdAt: string; // ISO date string
    id: string;
    product: Product;
}