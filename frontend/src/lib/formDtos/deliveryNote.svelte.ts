interface DeliveryNoteProductDto {
    id?: string;
    productId: string;
    quantity: number;
    name: string;
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

interface Address {
    id: string;
    street: string;
    city: string;
    postalCode: string;
    country: string;
}

export class DeliveryNoteForm {
    id: string|null = $state(null);
    customer: Customer | null = $state(null);
    deliveryDate: string = $state('');
    delivery = $state<boolean|null>();
    products: DeliveryNoteProductDto[] = $state([]);
    shortDescription: string = $state('');
    assignment: string = $state('');
    shippingAddressId: string|null = $state(null);
    billingAddressId: string|null = $state(null);

    constructor(id: string|null = null, customer: Customer | null = null, deliveryDate: string = '', delivery: boolean = true, products: DeliveryNoteProductDto[] = [], shortDescription: string = '', assignment: string = '', shippingAddressId: string|null = null, billingAddressId: string|null = null) {
        this.id = id;
        this.customer = customer;
        this.deliveryDate = deliveryDate;
        this.delivery = delivery;
        this.products = products;
        this.shortDescription = shortDescription;
        this.assignment = assignment;
        this.shippingAddressId = shippingAddressId;
        this.billingAddressId = billingAddressId;
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
            products: this.products,
            shippingAddressId: this.shippingAddressId,
            billingAddressId: this.billingAddressId
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
            displayName: this.customer ? `${this.customer.firstName} ${this.customer.surname}` : '',
            deliveryDate: this.deliveryDate,
            delivery: this.delivery,
            products: this.products.map(p => ({ productId: p.productId, quantity: p.quantity, name: p.name })),
            shortDescription: this.shortDescription,
            assignment: this.assignment,
            shippingAddressId: this.shippingAddressId,
            billingAddressId: this.billingAddressId,
        };
    }

    get displayName(): string {
        if(!this.customer) {
            return '';
        }
        
        return this.customer.companyName ? this.customer.companyName : `${this.customer.firstName} ${this.customer.surname}`;
    }

    static fromStorageObject(obj: any): DeliveryNoteForm | null {
        if (!obj || obj.customerId == null) return null;

        const customer: Customer = {
            id: obj.customerId,
            firstName: obj.customerFirstName ?? '',
            surname: obj.customerSurname ?? '',
            displayName: obj.displayName ?? '',
            addresses: obj.customerAddresses ?? [],
            companyName: obj.customerCompanyName ?? '',
            company: obj.customerCompany ?? false,
            defaultShippingAddress: obj.customerDefaultShippingAddress ?? undefined,
            defaultBillingAddress: obj.customerDefaultBillingAddress ?? undefined,
        };

        const products: DeliveryNoteProductDto[] = (obj.products ?? []).map((p: any) => ({
            productId: p.productId,
            quantity: p.quantity,
            name: p.name,
        }));

        return new DeliveryNoteForm(null, customer, obj.deliveryDate ?? '', obj.delivery ?? true, products, obj.shortDescription ?? '', obj.assignment ?? '', obj.shippingAddressId ?? null, obj.billingAddressId ?? null);
    }

    private static DRAFT_KEY = 'deliveryNoteDraft';

    static saveDraft(form: DeliveryNoteForm) {
        if (!form.customerId) {
            return;
        }

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
            return obj.displayName || null;
        } catch {
            return null;
        }
    }
}

// export const deliveryNoteForm = new DeliveryNoteForm();
export type { DeliveryNoteProductDto, Customer };
