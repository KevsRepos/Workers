interface DeliveryNoteProductDto {
    id?: string;
    productId: string;
    quantity: number;
    name: string;
}

interface Customer {
    id: string;
    firstName: string;
    surname: string;
}

export class DeliveryNoteForm {
    id: string|null = $state(null);
    customer: Customer | null = $state(null);
    deliveryDate: string = $state('');
    delivery = $state<boolean|null>();
    products: DeliveryNoteProductDto[] = $state([]);

    constructor(id: string|null = null, customer: Customer | null = null, deliveryDate: string = '', delivery: boolean = true, products: DeliveryNoteProductDto[] = []) {
        this.id = id;
        this.customer = customer;
        this.deliveryDate = deliveryDate;
        this.delivery = delivery;
        this.products = products;
    }

    get customerId(): string | null {
        return this.customer?.id ?? null;
    }

    setCustomer(customer: Customer) {
        this.customer = customer;
    }

    addProduct(productId: string, quantity: number = 1, name: string) {
        const existing = this.products.find(p => p.productId === productId);
        if (existing) {
            existing.quantity += quantity;
        } else {
            this.products.push({ productId, quantity, name });
        }
    }

    removeProduct(productId: string) {
        this.products = this.products.filter(p => p.productId !== productId);
    }

    updateQuantity(productId: string, quantity: number) {
        const product = this.products.find(p => p.productId === productId);
        if (product) {
            product.quantity = quantity;
        }
    }

    toJson() {
        return JSON.stringify({
            customerId: this.customerId,
            deliveryDate: this.deliveryDate,
            delivery: this.delivery,
            products: this.products
        });
    }

    get isValid(): boolean {
        return this.customerId !== null 
            && this.deliveryDate !== '' 
            && this.products.length > 0;
    }

    toStorageObject() {
        return {
            customerId: this.customer?.id ?? null,
            customerFirstName: this.customer?.firstName ?? '',
            customerSurname: this.customer?.surname ?? '',
            customerName: this.customer ? `${this.customer.firstName} ${this.customer.surname}` : '',
            deliveryDate: this.deliveryDate,
            delivery: this.delivery,
            products: this.products.map(p => ({ productId: p.productId, quantity: p.quantity, name: p.name })),
        };
    }

    static fromStorageObject(obj: any): DeliveryNoteForm | null {
        if (!obj || obj.customerId == null) return null;

        const customer: Customer = {
            id: obj.customerId,
            firstName: obj.customerFirstName ?? '',
            surname: obj.customerSurname ?? '',
        };

        const products: DeliveryNoteProductDto[] = (obj.products ?? []).map((p: any) => ({
            productId: p.productId,
            quantity: p.quantity,
            name: p.name,
        }));

        return new DeliveryNoteForm(null, customer, obj.deliveryDate ?? '', obj.delivery ?? true, products);
    }

    private static DRAFT_KEY = 'deliveryNoteDraft';

    static saveDraft(form: DeliveryNoteForm) {
        localStorage.setItem(DeliveryNoteForm.DRAFT_KEY, JSON.stringify(form.toStorageObject()));
    }

    static loadDraft(): DeliveryNoteForm | null {
        const raw = localStorage.getItem(DeliveryNoteForm.DRAFT_KEY);
        if (!raw) return null;
        try {
            return DeliveryNoteForm.fromStorageObject(JSON.parse(raw));
        } catch {
            return null;
        }
    }

    static clearDraft() {
        localStorage.removeItem(DeliveryNoteForm.DRAFT_KEY);
    }

    static getDraftCustomerName(): string | null {
        const raw = localStorage.getItem(DeliveryNoteForm.DRAFT_KEY);
        if (!raw) return null;
        try {
            const obj = JSON.parse(raw);
            return obj.customerName || null;
        } catch {
            return null;
        }
    }
}

// export const deliveryNoteForm = new DeliveryNoteForm();
export type { DeliveryNoteProductDto, Customer };
