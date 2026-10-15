import {
    draftObjectToState,
    stateToDraftObject,
    type DeliveryNoteFormState,
} from "$lib/deliveryNote/formMapper";

const DELIVERY_NOTE_DRAFT_KEY = "deliveryNoteDraft";

const saveDeliveryNoteDraft = (state: DeliveryNoteFormState): void => {
    if (!state.customer?.id) {
        return;
    }

    localStorage.setItem(DELIVERY_NOTE_DRAFT_KEY, JSON.stringify(stateToDraftObject(state)));
};

const loadDeliveryNoteDraft = (): DeliveryNoteFormState | null => {
    const raw = localStorage.getItem(DELIVERY_NOTE_DRAFT_KEY);
    if (!raw) {
        return null;
    }

    try {
        const parsed = JSON.parse(raw);
        return draftObjectToState(parsed);
    } catch {
        return null;
    }
};

const clearDeliveryNoteDraft = (): void => {
    localStorage.removeItem(DELIVERY_NOTE_DRAFT_KEY);
};

const getDeliveryNoteDraftCustomerName = (): string | null => {
    const raw = localStorage.getItem(DELIVERY_NOTE_DRAFT_KEY);
    if (!raw) {
        return null;
    }

    try {
        const parsed = JSON.parse(raw);
        return parsed.displayName || null;
    } catch {
        return null;
    }
};

export {
    clearDeliveryNoteDraft,
    getDeliveryNoteDraftCustomerName,
    loadDeliveryNoteDraft,
    saveDeliveryNoteDraft,
};
