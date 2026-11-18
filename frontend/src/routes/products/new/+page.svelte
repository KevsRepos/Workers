<script lang="ts">
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import ProductForm from '$lib/components/products/ProductForm.svelte';

	let { data } = $props();
</script>

<svelte:head><title>Neuer Artikel · Workers</title></svelte:head>
<PageHeader title="Neuer Artikel" back="/products" />
<ProductForm deposits={data.deposits} />
