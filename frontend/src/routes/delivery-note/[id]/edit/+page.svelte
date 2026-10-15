<script lang="ts">
import { goto } from "$app/navigation";
import DeliveryNoteFormular from "$lib/components/deliveryNote/DeliveryNoteFormular.svelte";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import { apiDeliveryNoteToState, applyStateToDeliveryNoteForm, deliveryNoteFormToState, stateToUpdatePayload, type ApiDeliveryNoteForEdit } from "$lib/deliveryNote/formMapper";
import { fetchApi } from "$lib/fetchApi.js";
import { DeliveryNoteForm } from "$lib/formDtos/deliveryNote.svelte";

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
        const json = await fetchApi(`delivery-notes/${deliveryNoteForm.id}`, 'PUT', payload);

        goto(`/delivery-note/${json.data.id}`);
    } catch (e) {
        console.error(e);
    }
}
</script>

<PageHeadline>Lieferschein bearbeiten</PageHeadline>

<DeliveryNoteFormular deliveryNoteForm={deliveryNoteForm} saveDeliveryNote={saveEdits} removedProductIds={removedProductIds} />