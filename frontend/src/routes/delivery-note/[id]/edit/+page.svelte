<script lang="ts">
import { goto } from "$app/navigation";
import EditDeliveryNote from "$lib/components/deliveryNote/EditDeliveryNote.svelte";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import { fetchApi } from "$lib/fetchApi.js";
import { DeliveryNoteForm } from "$lib/formDtos/deliveryNote.svelte";

const { data } = $props();

const deliveryNoteForm = new DeliveryNoteForm(
    data.id,
    data.customer,
    data.deliveryDate,
    data.delivery,
    data.deliveryNoteProducts.map((p: any) => ({ id: p.id, productId: p.product.id, quantity: p.quantity, name: p.product.name }))
);

const removedProductIds: string[] = $state([]);

$inspect(removedProductIds);

const saveEdits = async () => {
    console.log(deliveryNoteForm);
    
    try {
        const json = await fetchApi(`delivery-notes/${deliveryNoteForm.id}`, 'PUT', {
            customerId: deliveryNoteForm.customer?.id,
            deliveryDate: deliveryNoteForm.deliveryDate,
            deliveryNoteProducts: deliveryNoteForm.products.map(p => ({ id: p.id, productId: p.productId, quantity: p.quantity })),
            delivery: deliveryNoteForm.delivery,
            removedProductIds: removedProductIds,
        });

        console.log(json);
        

        goto(`/delivery-note/${json.data.id}`);
    } catch (e) {
        console.error(e);
    }
}
</script>

<PageHeadline>Lieferschein bearbeiten</PageHeadline>

<EditDeliveryNote deliveryNoteForm={deliveryNoteForm} saveDeliveryNote={saveEdits} removedProductIds={removedProductIds} />