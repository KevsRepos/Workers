<script lang="ts">
import { goto } from "$app/navigation";
import DeliveryNoteFormular from "$lib/modules/delivery-note/components/create/DeliveryNoteFormular.svelte";
import PageHeadline from "$lib/components/layout/PageHeadline.svelte";
import { apiDeliveryNoteToState, applyStateToDeliveryNoteForm, deliveryNoteFormToState, stateToUpdatePayload, type ApiDeliveryNoteForEdit } from "$lib/modules/delivery-note/mapping/formMapper";
import { updateDeliveryNote } from "$lib/modules/delivery-note/api";
import { DeliveryNoteForm } from "$lib/modules/delivery-note/state/DeliveryNoteForm.svelte";

const { data } = $props();

const deliveryNoteForm = new DeliveryNoteForm();
let initialized = $state(false);

$effect(() => {
    if (initialized) {
        return;
    }

    const deliveryNoteId = typeof data.id === 'string' ? data.id : null;
    deliveryNoteForm.id = deliveryNoteId;
    applyStateToDeliveryNoteForm(deliveryNoteForm, apiDeliveryNoteToState(data as ApiDeliveryNoteForEdit));
    initialized = true;
});

const removedProductIds: string[] = $state([]);

const saveEdits = async () => {
    try {
        const state = deliveryNoteFormToState(deliveryNoteForm);
        const payload = stateToUpdatePayload(state, removedProductIds);
        const json = await updateDeliveryNote(deliveryNoteForm.id, payload);

        goto(`/delivery-note/${json.data.id}`);
    } catch (e) {
        console.error(e);
    }
}
</script>

<PageHeadline>Lieferschein bearbeiten</PageHeadline>

<DeliveryNoteFormular deliveryNoteForm={deliveryNoteForm} saveDeliveryNote={saveEdits} removedProductIds={removedProductIds} />