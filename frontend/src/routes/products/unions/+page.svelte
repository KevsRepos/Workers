<script lang="ts">
	import { invalidate } from '$app/navigation';
	import { Pencil, Plus, Trash2 } from '@lucide/svelte';
	import { api } from '$lib/api/client';
	import { ApiError, userMessage } from '$lib/api/errors';
	import type { ProductUnion } from '$lib/api/types';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import ProductMultiSelect from '$lib/components/products/ProductMultiSelect.svelte';
	import Alert from '$lib/components/ui/Alert.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import ConfirmDialog from '$lib/components/ui/ConfirmDialog.svelte';
	import Dialog from '$lib/components/ui/Dialog.svelte';
	import EmptyState from '$lib/components/ui/EmptyState.svelte';
	import TextInput from '$lib/components/ui/TextInput.svelte';
	import { toast } from '$lib/stores/toast.svelte';

	let { data } = $props();

	let open = $state(false);
	let editing = $state<ProductUnion | null>(null);
	let name = $state('');
	let products = $state<Array<{ id: string; name: string }>>([]);
	let errors = $state<Record<string, string>>({});
	let saving = $state(false);
	let deleting = $state<ProductUnion | null>(null);
	let deleteOpen = $state(false);

	const edit = (union: ProductUnion | null) => {
		editing = union;
		name = union?.name ?? '';
		products = union?.products.map((p) => ({ id: p.id, name: p.name })) ?? [];
		errors = {};
		open = true;
	};

	const save = async () => {
		saving = true;
		errors = {};
		try {
			const body = { name, productIds: products.map((p) => p.id) };
			if (editing) await api.put(`product-unions/${editing.id}`, body);
			else await api.post('product-unions', body);
			open = false;
			toast.success('Verbund gespeichert.');
			await invalidate('app:unions');
		} catch (e) {
			if (e instanceof ApiError && e.status === 422) errors = e.fieldErrors;
			else toast.error(userMessage(e));
		} finally {
			saving = false;
		}
	};

	const remove = async () => {
		if (!deleting) return;
		try {
			await api.delete(`product-unions/${deleting.id}`);
			toast.success('Verbund gelöscht.');
			deleteOpen = false;
			await invalidate('app:unions');
		} catch (e) {
			toast.error(userMessage(e));
		}
	};
</script>

<svelte:head><title>Artikelverbünde · Workers</title></svelte:head>

<PageHeader
	title="Artikelverbünde"
	subtitle="Austauschbare Artikel (z. B. gleiche Kistenart) werden bei der Rückgabe gemeinsam gezählt."
>
	{#snippet actions()}{#if data.user?.isAdmin}<Button onclick={() => edit(null)}
				><Plus class="size-4" />Neuer Verbund</Button
			>{/if}{/snippet}
</PageHeader>

<div class="grid gap-4 sm:grid-cols-2">
	{#each data.unions as union (union.id)}
		<div class="card p-4">
			<div class="flex items-start justify-between gap-2">
				<h2 class="text-base">{union.name}</h2>
				{#if data.user?.isAdmin}
					<div class="flex">
						<button
							class="rounded p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-700"
							aria-label="{union.name} bearbeiten"
							onclick={() => edit(union)}><Pencil class="size-4" /></button
						>
						<button
							class="rounded p-1.5 text-stone-400 hover:bg-red-50 hover:text-red-600"
							aria-label="{union.name} löschen"
							onclick={() => {
								deleting = union;
								deleteOpen = true;
							}}><Trash2 class="size-4" /></button
						>
					</div>
				{/if}
			</div>
			<p class="mt-2 text-sm text-stone-600">{union.products.map((p) => p.name).join(' · ')}</p>
		</div>
	{:else}
		<div class="card sm:col-span-2"><EmptyState title="Noch keine Verbünde" /></div>
	{/each}
</div>

<Dialog bind:open title={editing ? 'Verbund bearbeiten' : 'Neuer Verbund'} size="lg">
	<form
		id="union-form"
		class="flex flex-col gap-4"
		onsubmit={(e) => {
			e.preventDefault();
			save();
		}}
		novalidate
	>
		{#if errors.productIds}<Alert tone="danger">{errors.productIds}</Alert>{/if}
		<TextInput
			id="union-name"
			label="Name"
			required
			bind:value={name}
			error={errors.name}
			placeholder="z. B. Bierkiste 20x0,5l"
		/>
		<ProductMultiSelect bind:selected={products} id="union-products" />
	</form>
	{#snippet footer()}
		<Button variant="secondary" onclick={() => (open = false)}>Abbrechen</Button>
		<Button type="submit" form="union-form" loading={saving}>Speichern</Button>
	{/snippet}
</Dialog>

<ConfirmDialog
	bind:open={deleteOpen}
	title="Verbund löschen?"
	message="Die Artikel bleiben erhalten, werden aber bei Rückgaben wieder einzeln gezählt."
	confirmLabel="Löschen"
	danger
	onconfirm={remove}
/>
