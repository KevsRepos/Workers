<script lang="ts">
import OrderPresetForm from "$lib/modules/order-preset/components/OrderPresetForm.svelte";
import PageHeadline from "$lib/components/layout/PageHeadline.svelte";
import { OrderPreset } from "$lib/modules/order-preset/model/OrderPreset";
import { createOrderPreset } from "$lib/modules/order-preset/api";
import { goto } from "$app/navigation";

const orderPreset = new OrderPreset(null, "", []);

const handleSubmit = async (submittedOrderPreset: OrderPreset) => {
    const response = await createOrderPreset({
        name: submittedOrderPreset.name,
        productIds: submittedOrderPreset.items.map((item) => item.product.id)
    });

    goto(`/delivery-note/order-preset/${response.data.id}`);
};
</script>

<PageHeadline>Bestellvorlage erstellen</PageHeadline>

<main class="lg:max-w-200 mx-auto px-4">
    <OrderPresetForm
        {orderPreset}
        onSubmit={handleSubmit}
    />
</main>