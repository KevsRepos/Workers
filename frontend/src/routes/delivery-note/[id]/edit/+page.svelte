<script lang="ts">
import { goto } from "$app/navigation";
import DeliveryNoteFormular from "$lib/components/deliveryNote/DeliveryNoteFormular.svelte";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import { fetchApi } from "$lib/fetchApi.js";
import { DeliveryNoteForm } from "$lib/formDtos/deliveryNote.svelte";

const { data } = $props();

const deliveryNoteForm = new DeliveryNoteForm(
    data.id,
    data.customer,
    data.deliveryDate,
    data.delivery,
    data.deliveryNoteProducts.map((p: any) => ({ id: p.id, productId: p.product.id, quantity: p.quantity, name: p.product.name })),
    data.shortDescription ?? '',
    data.assignment ?? '',
    data.shippingAddress?.id ?? null,
    data.billingAddress?.id ?? null,
);

const removedProductIds: string[] = $state([]);

const saveEdits = async () => {
    console.log(deliveryNoteForm);
    
    try {
        const json = await fetchApi(`delivery-notes/${deliveryNoteForm.id}`, 'PUT', {
            customerId: deliveryNoteForm.customer?.id,
            deliveryDate: deliveryNoteForm.deliveryDate,
            deliveryNoteProducts: deliveryNoteForm.products.map(p => ({ id: p.id, productId: p.productId, quantity: p.quantity })),
            delivery: deliveryNoteForm.delivery,
            removedProductIds: removedProductIds,
            shortDescription: deliveryNoteForm.shortDescription || null,
            assignment: deliveryNoteForm.assignment || null,
            shippingAddressId: deliveryNoteForm.shippingAddressId,
            billingAddressId: deliveryNoteForm.billingAddressId,
        });

        goto(`/delivery-note/${json.data.id}`);
    } catch (e) {
        console.error(e);
    }
}
</script>

<PageHeadline>Lieferschein bearbeiten</PageHeadline>

<DeliveryNoteFormular deliveryNoteForm={deliveryNoteForm} saveDeliveryNote={saveEdits} removedProductIds={removedProductIds} />