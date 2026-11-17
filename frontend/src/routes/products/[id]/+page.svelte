<script lang="ts">
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import ProductForm from '$lib/components/products/ProductForm.svelte';

	let { data } = $props();
</script>

<svelte:head><title>{data.product.name} · Artikel · Workers</title></svelte:head>
<PageHeader title={data.product.name} back="/products" />
{#key data.product.id}
	<ProductForm product={data.product} deposits={data.deposits} readonly={!data.user?.isAdmin} />
{/key}
