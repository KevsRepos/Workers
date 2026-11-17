<script lang="ts">
	import { invalidate } from '$app/navigation';
	import { Pencil, Plus, Trash2 } from '@lucide/svelte';
	import { api } from '$lib/api/client';
	import { ApiError, userMessage } from '$lib/api/errors';
	import type { OrderPreset } from '$lib/api/types';
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
	let editing = $state<OrderPreset | null>(null);
	let name = $state('');
	let products = $state<Array<{ id: string; name: string }>>([]);
	let errors = $state<Record<string, string>>({});
	let saving = $state(false);
	let deleting = $state<OrderPreset | null>(null);
	let deleteOpen = $state(false);

	const edit = (preset: OrderPreset | null) => {
		editing = preset;
		name = preset?.name ?? '';
		products = preset?.products.map((p) => ({ id: p.id, name: p.name })) ?? [];
		errors = {};
		open = true;
	};

	const save = async () => {
		saving = true;
		errors = {};
		try {
			const body = { name, productIds: products.map((p) => p.id) };
			if (editing) await api.put(`order-presets/${editing.id}`, body);
			else await api.post('order-presets', body);
			open = false;
			toast.success('Vorlage gespeichert.');
			await invalidate('app:presets');
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
			await api.delete(`order-presets/${deleting.id}`);
			toast.success('Vorlage gelöscht.');
			deleteOpen = false;
			await invalidate('app:presets');
		} catch (e) {
			toast.error(userMessage(e));
		}
	};
</script>

<svelte:head><title>Bestellvorlagen · Workers</title></svelte:head>

<PageHeader
	title="Bestellvorlagen"
	subtitle="Häufige Artikelzusammenstellungen, die beim Lieferschein mit einem Klick eingefügt werden."
>
	{#snippet actions()}<Button onclick={() => edit(null)}><Plus class="size-4" />Neue Vorlage</Button>{/snippet}
</PageHeader>

<div class="grid gap-4 sm:grid-cols-2">
	{#each data.presets as preset (preset.id)}
		<div class="card p-4">
			<div class="flex items-start justify-between gap-2">
				<h2 class="text-base">{preset.name}</h2>
				<div class="flex">
					<button
						class="rounded p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-700"
						aria-label="{preset.name} bearbeiten"
						onclick={() => edit(preset)}><Pencil class="size-4" /></button
					>
					<button
						class="rounded p-1.5 text-stone-400 hover:bg-red-50 hover:text-red-600"
						aria-label="{preset.name} löschen"
						onclick={() => {
							deleting = preset;
							deleteOpen = true;
						}}><Trash2 class="size-4" /></button
					>
				</div>
			</div>
			<p class="mt-2 text-sm text-stone-600">{preset.products.map((p) => p.name).join(' · ')}</p>
		</div>
	{:else}
		<div class="card sm:col-span-2"><EmptyState title="Noch keine Vorlagen" /></div>
	{/each}
</div>

<Dialog bind:open title={editing ? 'Vorlage bearbeiten' : 'Neue Vorlage'} size="lg">
	<form
		id="preset-form"
		class="flex flex-col gap-4"
		onsubmit={(e) => {
			e.preventDefault();
			save();
		}}
		novalidate
	>
		{#if errors.productIds}<Alert tone="danger">{errors.productIds}</Alert>{/if}
		<TextInput
			id="preset-name"
			label="Name"
			required
			bind:value={name}
			error={errors.name}
			placeholder="z. B. Vereinsfest"
		/>
		<ProductMultiSelect bind:selected={products} id="preset-products" />
	</form>
	{#snippet footer()}
		<Button variant="secondary" onclick={() => (open = false)}>Abbrechen</Button>
		<Button type="submit" form="preset-form" loading={saving}>Speichern</Button>
	{/snippet}
</Dialog>

<ConfirmDialog
	bind:open={deleteOpen}
	title="Vorlage löschen?"
	message="Die Vorlage wird gelöscht. Bestehende Lieferscheine sind nicht betroffen."
	confirmLabel="Löschen"
	danger
	onconfirm={remove}
/>
