interface DeliveryNoteProductDto {
    productId: number;
    quantity: number;
    name: string;
}

interface Customer {
    id: number;
    firstName: string;
    surname: string;
}

class DeliveryNoteForm {
    customer: Customer | null = $state(null);
    deliveryDate: string = $state('');
    delivery: boolean = $state(true);
    products: DeliveryNoteProductDto[] = $state([]);

    get customerId(): number | null {
        return this.customer?.id ?? null;
    }

    setCustomer(customer: Customer) {
        this.customer = customer;
    }

    addProduct(productId: number, quantity: number = 1, name: string) {
        const existing = this.products.find(p => p.productId === productId);
        if (existing) {
            existing.quantity += quantity;
        } else {
            this.products.push({ productId, quantity, name });
        }
    }

    removeProduct(productId: number) {
        this.products = this.products.filter(p => p.productId !== productId);
    }

    updateQuantity(productId: number, quantity: number) {
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
}

export const deliveryNoteForm = new DeliveryNoteForm();
export type { DeliveryNoteProductDto, Customer };
