<script>
import { goto } from "$app/navigation";
import EditDeliveryNote from "$lib/components/deliveryNote/EditDeliveryNote.svelte";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import { fetchApi } from "$lib/fetchApi";
import { DeliveryNoteForm } from "$lib/formDtos/deliveryNote.svelte";
import { createToaster, Toast } from "@skeletonlabs/skeleton-svelte";
import { onMount, tick } from "svelte";

const deliveryNoteForm = new DeliveryNoteForm();
const toaster = createToaster();

let autoSaveEnabled = $state(false);
let restoredFromDraft = $state(false);
let draftToastId = $state('');
let formKey = $state(0);

const restoreDraft = () => {
    const draft = DeliveryNoteForm.loadDraft();
    if (!draft) return;
    deliveryNoteForm.customer = draft.customer;
    deliveryNoteForm.deliveryDate = draft.deliveryDate;
    deliveryNoteForm.delivery = draft.delivery;
    deliveryNoteForm.products = draft.products;
    deliveryNoteForm.shortDescription = draft.shortDescription;
    deliveryNoteForm.assignment = draft.assignment;
    restoredFromDraft = true;
    autoSaveEnabled = true;
    formKey++;
};

const discardDraft = () => {
    DeliveryNoteForm.clearDraft();
    autoSaveEnabled = true;
    if (draftToastId) toaster.dismiss(draftToastId);
};

onMount(async () => {
    const customerName = DeliveryNoteForm.getDraftCustomerName();
    if (customerName) {
        await tick();
        draftToastId = toaster.create({
            title: 'Entwurf vorhanden',
            description: `Lieferschein für ${customerName} weiter bearbeiten?`,
            type: 'info',
            duration: 60 * 60 * 1000,
            action: {
                label: 'Wiederherstellen',
                onClick: restoreDraft,
            },
        });
    } else {
        autoSaveEnabled = true;
    }
});

$effect(() => {
    if (!autoSaveEnabled) return;
    const _ = [
        deliveryNoteForm.customer,
        deliveryNoteForm.deliveryDate,
        deliveryNoteForm.delivery,
        deliveryNoteForm.shortDescription,
        deliveryNoteForm.assignment,
        JSON.stringify(deliveryNoteForm.products),
    ];
    if (deliveryNoteForm.customer) {
        DeliveryNoteForm.saveDraft(deliveryNoteForm);
    }
});

const saveDeliveryNote = async () => {    
    try {
        const json = await fetchApi('delivery-notes', 'POST', {
            customerId: deliveryNoteForm.customer?.id,
            deliveryDate: deliveryNoteForm.deliveryDate,
            deliveryNoteProducts: deliveryNoteForm.products.map(p => ({ productId: p.productId, quantity: p.quantity })),
            delivery: deliveryNoteForm.delivery,
            shortDescription: deliveryNoteForm.shortDescription || null,
            assignment: deliveryNoteForm.assignment || null,
        });

        if (autoSaveEnabled) {
            DeliveryNoteForm.clearDraft();
        }

        goto(`/delivery-note/${json.data.id}`);
    } catch (e) {
        console.error(e);
    }
}
</script>

<PageHeadline>Lieferschein erstellen</PageHeadline>

{#key formKey}
<EditDeliveryNote deliveryNoteForm={deliveryNoteForm} saveDeliveryNote={saveDeliveryNote} />
{/key}

<Toast.Group {toaster}>
    {#snippet children(toast)}
    <Toast {toast} class="flex flex-col">
        <Toast.Message>
            <Toast.Title>{toast.title}</Toast.Title>
            <Toast.Description>{toast.description}</Toast.Description>
        </Toast.Message>
        <div class="flex gap-2 justify-between w-full">
            <div>
                <Toast.ActionTrigger class="btn preset-filled-primary-500 btn-sm">
                    {toast.action?.label}
                </Toast.ActionTrigger>
                <button onclick={discardDraft} type="button" class="btn preset-filled-surface-500 btn-sm">
                    Verwerfen
                </button>
            </div>
            <Toast.CloseTrigger />
        </div>
    </Toast>
    {/snippet}
</Toast.Group>
