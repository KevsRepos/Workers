<script lang="ts">
import { goto } from "$app/navigation";
import EditDeliveryNote from "$lib/components/deliveryNote/EditDeliveryNote.svelte";
import { fetchApi } from "$lib/fetchApi.js";
import { DeliveryNoteForm } from "$lib/formDtos/deliveryNote.svelte";

const { data } = $props();

const deliveryNoteForm = new DeliveryNoteForm(
    data.id,
    data.customer,
    data.deliveryDate,
    data.delivery,
    data.products.map((p: any) => ({ productId: p.product.id, quantity: p.quantity, name: p.product.name }))
);

const saveEdits = async () => {
    console.log(deliveryNoteForm);
    
    try {
        const json = await fetchApi(`delivery-notes/${deliveryNoteForm.id}`, 'PUT', {
            deliveryDate: deliveryNoteForm.deliveryDate,
            deliveryNoteProducts: deliveryNoteForm.products.map(p => ({ productId: p.productId, quantity: p.quantity })),
            delivery: deliveryNoteForm.delivery,
        });

        console.log(json);
        

        goto(`/delivery-note/${json.data.id}`);
    } catch (e) {
        console.error(e);
    }
}
</script>

<EditDeliveryNote deliveryNoteForm={deliveryNoteForm} saveDeliveryNote={saveEdits} />