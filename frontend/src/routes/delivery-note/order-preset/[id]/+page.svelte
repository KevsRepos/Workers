<script lang="ts">
import OrderPresetForm from "$lib/components/deliveryNote/orderPreset/OrderPresetForm.svelte";
import PageHeadline from "$lib/components/PageHeadline.svelte";
import { OrderPreset } from "$lib/dto/orderPreset/OrderPreset";
import { fetchApi } from "$lib/fetchApi";

let { data } = $props<{ data: { orderPreset: OrderPreset } }>();

const handleSubmit = async (submittedOrderPreset: OrderPreset) => {
	await fetchApi(`order-presets/${submittedOrderPreset.id}`, "PUT", {
		name: submittedOrderPreset.name,
		productIds: submittedOrderPreset.items.map((item) => item.product.id)
	});
};
</script>

<PageHeadline>Bestellvorlage bearbeiten</PageHeadline>

<main class="lg:max-w-200 mx-auto px-4">
	<OrderPresetForm
		orderPreset={data.orderPreset}
		onSubmit={handleSubmit}
	/>
</main>