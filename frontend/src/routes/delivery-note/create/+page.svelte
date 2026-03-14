<script>
import { goto } from "$app/navigation";
import EditDeliveryNote from "$lib/components/deliveryNote/EditDeliveryNote.svelte";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import { fetchApi } from "$lib/fetchApi";
import { DeliveryNoteForm } from "$lib/formDtos/deliveryNote.svelte";

const deliveryNoteForm = new DeliveryNoteForm();

const saveDeliveryNote = async () => {    
    try {
        const json = await fetchApi('delivery-notes', 'POST', {
            customerId: deliveryNoteForm.customer?.id,
            deliveryDate: deliveryNoteForm.deliveryDate,
            deliveryNoteProducts: deliveryNoteForm.products.map(p => ({ productId: p.productId, quantity: p.quantity })),
            delivery: deliveryNoteForm.delivery,
        });

        goto(`/delivery-note/${json.data.id}`);
    } catch (e) {
        console.error(e);
    }
}
</script>

<PageHeadline>Lieferschein erstellen</PageHeadline>

<EditDeliveryNote deliveryNoteForm={deliveryNoteForm} saveDeliveryNote={saveDeliveryNote} />
