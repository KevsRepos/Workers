import type { Customer, DeliveryNoteProductDto } from "$lib/formDtos/deliveryNote.types";

interface DeliveryNoteFormLike {
    customer: Customer | null;
    deliveryDate: string;
    pickupDate: string | null;
    delivery: boolean | null | undefined;
    products: DeliveryNoteProductDto[];
    shortDescription: string;
    privateDescription: string;
    assignment: string;
    shippingAddressId: string | null;
    billingAddressId: string | null;
    adultGuests: number | null;
    childGuests: number | null;
    callBefore: boolean;
}

interface DeliveryNoteFormState {
    customer: Customer | null;
    deliveryDate: string;
    pickupDate: string | null;
    delivery: boolean | null;
    products: DeliveryNoteProductDto[];
    shortDescription: string;
    privateDescription: string;
    assignment: string;
    shippingAddressId: string | null;
    billingAddressId: string | null;
    adultGuests: number | null;
    childGuests: number | null;
    callBefore: boolean;
}

interface CreateDeliveryNotePayload {
    customerId: string | undefined;
    deliveryDate: string;
    pickupDate: string | null;
    deliveryNoteProducts: Array<{ productId: string; quantity: number | null }>;
    delivery: boolean | null;
    shortDescription: string | null;
    privateDescription: string | null;
    assignment: string | null;
    shippingAddressId: string | null;
    billingAddressId: string | null;
    adultGuests: number | null;
    childGuests: number | null;
    callBefore: boolean;
}

interface UpdateDeliveryNoteProductPayload {
    id?: string;
    productId: string;
    quantity: number | null;
}

interface UpdateDeliveryNotePayload {
    customerId: string | undefined;
    deliveryDate: string;
    pickupDate: string | null;
    deliveryNoteProducts: UpdateDeliveryNoteProductPayload[];
    delivery: boolean | null;
    removedProductIds: string[];
    shortDescription: string | null;
    privateDescription: string | null;
    assignment: string | null;
    shippingAddressId: string | null;
    billingAddressId: string | null;
    adultGuests: number | null;
    childGuests: number | null;
    callBefore: boolean;
}

interface ApiDeliveryNoteProduct {
    id: string;
    quantity: number | null;
    product?: {
        id: string;
        name: string;
    } | null;
}

interface ApiDeliveryNoteForEdit {
    customer?: Customer | null;
    deliveryDate?: string | null;
    pickupDate?: string | null;
    delivery?: boolean | null;
    deliveryNoteProducts?: ApiDeliveryNoteProduct[];
    shortDescription?: string | null;
    privateDescription?: string | null;
    assignment?: string | null;
    shippingAddress?: { id?: string | null } | null;
    billingAddress?: { id?: string | null } | null;
    adultGuests?: number | null;
    childGuests?: number | null;
    callBefore?: boolean | null;
}

interface DeliveryNoteDraftObject {
    customerId: string | null;
    customerFirstName: string;
    customerSurname: string;
    displayName: string;
    customerAddresses: Customer["addresses"];
    customerCompanyName: string;
    customerCompany: boolean;
    customerDefaultShippingAddress: Customer["defaultShippingAddress"] | null;
    customerDefaultBillingAddress: Customer["defaultBillingAddress"] | null;
    deliveryDate: string;
    pickupDate: string | null;
    delivery: boolean | null;
    products: DeliveryNoteProductDto[];
    shortDescription: string;
    privateDescription: string;
    assignment: string;
    shippingAddressId: string | null;
    billingAddressId: string | null;
    adultGuests: number | null;
    childGuests: number | null;
    callBefore: boolean;
}

type DeliveryNoteDraftCustomer = Omit<Customer, "id"> & { id: string };

const normalizeNullableText = (value: string): string | null => {
    const trimmed = value.trim();
    return trimmed.length > 0 ? trimmed : null;
};

const cloneProducts = (products: DeliveryNoteProductDto[]): DeliveryNoteProductDto[] => products.map((product) => ({
    id: product.id,
    productId: product.productId,
    quantity: product.quantity,
    name: product.name,
}));

const createStateBase = (state: DeliveryNoteFormState) => {
    const { customer, products, ...rest } = state;
    return rest;
};

const parseNumberOrNull = (value: unknown, fallback: number | null): number | null => {
    if (typeof value === "number" && Number.isFinite(value)) {
        return value;
    }

    return fallback;
};

const parseBooleanOrFallback = (value: unknown, fallback: boolean): boolean => {
    if (typeof value === "boolean") {
        return value;
    }

    return fallback;
};

const parseStringOrFallback = (value: unknown, fallback: string): string => {
    if (typeof value === "string") {
        return value;
    }

    return fallback;
};

const parseStringOrNull = (value: unknown): string | null => {
    if (typeof value === "string") {
        return value;
    }

    return null;
};

const parseCustomerFromDraft = (value: unknown): DeliveryNoteDraftCustomer | null => {
    if (!value || typeof value !== "object") {
        return null;
    }

    const draft = value as Partial<DeliveryNoteDraftObject>;
    if (typeof draft.customerId !== "string" || draft.customerId.length === 0) {
        return null;
    }

    return {
        id: draft.customerId,
        firstName: parseStringOrFallback(draft.customerFirstName, ""),
        surname: parseStringOrFallback(draft.customerSurname, ""),
        displayName: parseStringOrFallback(draft.displayName, ""),
        addresses: Array.isArray(draft.customerAddresses) ? draft.customerAddresses : [],
        companyName: parseStringOrFallback(draft.customerCompanyName, ""),
        company: parseBooleanOrFallback(draft.customerCompany, false),
        defaultShippingAddress: draft.customerDefaultShippingAddress ?? undefined,
        defaultBillingAddress: draft.customerDefaultBillingAddress ?? undefined,
    };
};

const parseProductsFromDraft = (value: unknown): DeliveryNoteProductDto[] => {
    if (!Array.isArray(value)) {
        return [];
    }

    return value
        .filter((item): item is Partial<DeliveryNoteProductDto> => Boolean(item) && typeof item === "object")
        .map((product) => ({
            id: typeof product.id === "string" ? product.id : undefined,
            productId: parseStringOrFallback(product.productId, ""),
            quantity: typeof product.quantity === "number" || product.quantity === null ? product.quantity : null,
            name: parseStringOrFallback(product.name, ""),
        }))
        .filter((product) => product.productId.length > 0);
};

const buildCustomerDisplayName = (customer: Customer | null): string => {
    if (!customer) {
        return "";
    }

    if (customer.company && customer.companyName) {
        return customer.companyName;
    }

    return `${customer.firstName} ${customer.surname}`.trim();
};

const deliveryNoteFormToState = (form: DeliveryNoteFormLike): DeliveryNoteFormState => ({
    customer: form.customer,
    deliveryDate: form.deliveryDate,
    pickupDate: form.pickupDate,
    delivery: form.delivery ?? null,
    products: cloneProducts(form.products),
    shortDescription: form.shortDescription,
    privateDescription: form.privateDescription,
    assignment: form.assignment,
    shippingAddressId: form.shippingAddressId,
    billingAddressId: form.billingAddressId,
    adultGuests: form.adultGuests,
    childGuests: form.childGuests,
    callBefore: form.callBefore,
});

const applyStateToDeliveryNoteForm = (form: DeliveryNoteFormLike, state: DeliveryNoteFormState): void => {
    Object.assign(form, createStateBase(state));
    form.customer = state.customer;
    form.products = cloneProducts(state.products);
};

const stateToCreatePayload = (state: DeliveryNoteFormState): CreateDeliveryNotePayload => ({
    customerId: state.customer?.id,
    ...createStateBase(state),
    deliveryNoteProducts: state.products.map((product) => ({
        productId: product.productId,
        quantity: product.quantity,
    })),
    shortDescription: normalizeNullableText(state.shortDescription),
    privateDescription: normalizeNullableText(state.privateDescription),
    assignment: normalizeNullableText(state.assignment),
});

const stateToUpdatePayload = (
    state: DeliveryNoteFormState,
    removedProductIds: string[],
): UpdateDeliveryNotePayload => ({
    customerId: state.customer?.id,
    ...createStateBase(state),
    removedProductIds,
    deliveryNoteProducts: state.products.map((product) => ({
        id: product.id,
        productId: product.productId,
        quantity: product.quantity,
    })),
    shortDescription: normalizeNullableText(state.shortDescription),
    privateDescription: normalizeNullableText(state.privateDescription),
    assignment: normalizeNullableText(state.assignment),
});

const apiDeliveryNoteToState = (data: ApiDeliveryNoteForEdit): DeliveryNoteFormState => ({
    customer: data.customer ?? null,
    deliveryDate: data.deliveryDate ?? "",
    pickupDate: data.pickupDate ?? null,
    delivery: data.delivery ?? null,
    products: (data.deliveryNoteProducts ?? [])
        .filter((product) => product.product?.id)
        .map((product) => ({
            id: product.id,
            productId: product.product?.id ?? "",
            quantity: product.quantity,
            name: product.product?.name ?? "",
        })),
    shortDescription: data.shortDescription ?? "",
    privateDescription: data.privateDescription ?? "",
    assignment: data.assignment ?? "",
    shippingAddressId: data.shippingAddress?.id ?? null,
    billingAddressId: data.billingAddress?.id ?? null,
    adultGuests: data.adultGuests ?? null,
    childGuests: data.childGuests ?? null,
    callBefore: data.callBefore ?? false,
});

const stateToDraftObject = (state: DeliveryNoteFormState): DeliveryNoteDraftObject => ({
    ...createStateBase(state),
    customerId: state.customer?.id ?? null,
    customerFirstName: state.customer?.firstName ?? "",
    customerSurname: state.customer?.surname ?? "",
    displayName: buildCustomerDisplayName(state.customer),
    customerAddresses: state.customer?.addresses ?? [],
    customerCompanyName: state.customer?.companyName ?? "",
    customerCompany: state.customer?.company ?? false,
    customerDefaultShippingAddress: state.customer?.defaultShippingAddress ?? null,
    customerDefaultBillingAddress: state.customer?.defaultBillingAddress ?? null,
    products: cloneProducts(state.products),
});

const draftObjectToState = (obj: unknown): DeliveryNoteFormState | null => {
    const customer = parseCustomerFromDraft(obj);
    if (!customer) {
        return null;
    }

    const draft = obj as Partial<DeliveryNoteDraftObject>;

    return {
        customer,
        deliveryDate: parseStringOrFallback(draft.deliveryDate, ""),
        pickupDate: parseStringOrNull(draft.pickupDate),
        delivery: typeof draft.delivery === "boolean" || draft.delivery === null ? draft.delivery : true,
        products: parseProductsFromDraft(draft.products),
        shortDescription: parseStringOrFallback(draft.shortDescription, ""),
        privateDescription: parseStringOrFallback(draft.privateDescription, ""),
        assignment: parseStringOrFallback(draft.assignment, ""),
        shippingAddressId: parseStringOrNull(draft.shippingAddressId),
        billingAddressId: parseStringOrNull(draft.billingAddressId),
        adultGuests: parseNumberOrNull(draft.adultGuests, 0),
        childGuests: parseNumberOrNull(draft.childGuests, 0),
        callBefore: parseBooleanOrFallback(draft.callBefore, false),
    };
};

const createAutosaveSnapshot = (state: DeliveryNoteFormState): string => JSON.stringify(stateToDraftObject(state));

export {
    apiDeliveryNoteToState,
    applyStateToDeliveryNoteForm,
    createAutosaveSnapshot,
    deliveryNoteFormToState,
    draftObjectToState,
    stateToCreatePayload,
    stateToUpdatePayload,
    stateToDraftObject,
};

export type { CreateDeliveryNotePayload, DeliveryNoteFormState, UpdateDeliveryNotePayload, ApiDeliveryNoteForEdit };
