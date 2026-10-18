<script lang="ts">
import OrderPresetForm from "$lib/components/deliveryNote/orderPreset/OrderPresetForm.svelte";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import { OrderPreset } from "$lib/dto/orderPreset/OrderPreset";
import { fetchApi } from "$lib/fetchApi";
import { goto } from "$app/navigation";

const orderPreset = new OrderPreset(null, "", []);

const handleSubmit = async (submittedOrderPreset: OrderPreset) => {
    const response = await fetchApi("order-presets", "POST", {
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