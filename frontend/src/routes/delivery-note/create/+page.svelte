<script>
import { goto } from "$app/navigation";
import DeliveryNoteFormular from "$lib/components/deliveryNote/DeliveryNoteFormular.svelte";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import { applyStateToDeliveryNoteForm, createAutosaveSnapshot, deliveryNoteFormToState, stateToCreatePayload } from "$lib/deliveryNote/formMapper";
import { clearDeliveryNoteDraft, getDeliveryNoteDraftCustomerName, loadDeliveryNoteDraft, saveDeliveryNoteDraft } from "$lib/deliveryNote/draftStore";
import { fetchApi } from "$lib/fetchApi";
import { DeliveryNoteForm } from "$lib/formDtos/deliveryNote.svelte";
import { createToaster, Toast } from "@skeletonlabs/skeleton-svelte";
import { onMount, tick } from "svelte";

const deliveryNoteForm = new DeliveryNoteForm();

const toaster = createToaster({
    placement: 'top-end'
});

let autoSaveEnabled = $state(false);
let draftToastId = $state('');
let formKey = $state(0);

const restoreDraft = () => {
    const draft = loadDeliveryNoteDraft();
    if (!draft) return;
    applyStateToDeliveryNoteForm(deliveryNoteForm, draft);

    if(deliveryNoteForm.customer !== null) {
        const customer = deliveryNoteForm.customer;
        fetchApi(`customers/${encodeURIComponent(deliveryNoteForm.customer.id)}/addresses`, 'GET')
        .then((json) => {
            customer.addresses = json;
        });
    }

    autoSaveEnabled = true;
    formKey++;
};

const discardDraft = () => {
    clearDeliveryNoteDraft();
    autoSaveEnabled = true;
    if (draftToastId) toaster.dismiss(draftToastId);
};

onMount(async () => {
    const customerName = getDeliveryNoteDraftCustomerName();
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
    const _ = createAutosaveSnapshot(deliveryNoteFormToState(deliveryNoteForm));
    if (deliveryNoteForm.customer?.id) {
        saveDeliveryNoteDraft(deliveryNoteFormToState(deliveryNoteForm));
    }
});

const saveDeliveryNote = async () => {    
    try {
        const state = deliveryNoteFormToState(deliveryNoteForm);
        const json = await fetchApi('delivery-notes', 'POST', stateToCreatePayload(state));

        if (autoSaveEnabled) {
            clearDeliveryNoteDraft();
        }

        goto(`/delivery-note/${json.data.id}`);
    } catch (e) {
        console.error(e);
    }
}
</script>

<PageHeadline>Lieferschein erstellen</PageHeadline>

{#key formKey}
    <DeliveryNoteFormular deliveryNoteForm={deliveryNoteForm} saveDeliveryNote={saveDeliveryNote} />
{/key}

<Toast.Group {toaster}>
    {#snippet children(toast)}
        <Toast {toast} class="flex flex-col">
            <Toast.Message class="w-full">
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