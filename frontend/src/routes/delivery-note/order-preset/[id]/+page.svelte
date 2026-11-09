<script lang="ts">
import OrderPresetForm from "$lib/modules/order-preset/components/OrderPresetForm.svelte";
import PageHeadline from "$lib/components/layout/PageHeadline.svelte";
import { OrderPreset } from "$lib/modules/order-preset/model/OrderPreset";
import { updateOrderPreset } from "$lib/modules/order-preset/api";

let { data } = $props<{ data: { orderPreset: OrderPreset } }>();

const handleSubmit = async (submittedOrderPreset: OrderPreset) => {
	await updateOrderPreset(submittedOrderPreset.id!, {
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