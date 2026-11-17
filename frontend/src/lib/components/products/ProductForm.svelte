<script lang="ts">
	import { goto } from '$app/navigation';
	import { api } from '$lib/api/client';
	import { ApiError, userMessage } from '$lib/api/errors';
	import type { Deposit, Product, ProductInput } from '$lib/api/types';
	import Alert from '$lib/components/ui/Alert.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import Checkbox from '$lib/components/ui/Checkbox.svelte';
	import Select from '$lib/components/ui/Select.svelte';
	import TextInput from '$lib/components/ui/TextInput.svelte';
	import { toast } from '$lib/stores/toast.svelte';
	import { centsToEuroInput, formatCents, parseEuroToCents } from '$lib/utils/format';

	let {
		product = null,
		deposits,
		readonly = false
	}: { product?: Product | null; deposits: Deposit[]; readonly?: boolean } = $props();

	// svelte-ignore state_referenced_locally
	let name = $state(product?.name ?? '');
	// svelte-ignore state_referenced_locally
	let price = $state(centsToEuroInput(product?.salesPrice));
	// svelte-ignore state_referenced_locally
	let depositId = $state<string | null>(product?.deposit?.id ?? null);
	// svelte-ignore state_referenced_locally
	let sellable = $state(product?.sellable ?? true);
	// svelte-ignore state_referenced_locally
	let rentable = $state(product?.rentable ?? false);
	// svelte-ignore state_referenced_locally
	let quantityInCrate = $state<string>(product?.quantityInCrate ? String(product.quantityInCrate) : '');
	// svelte-ignore state_referenced_locally
	let archived = $state(product?.archived ?? false);
	let errors = $state<Record<string, string>>({});
	let error = $state<string | null>(null);
	let saving = $state(false);

	const depositOptions = $derived([
		{ value: null, label: 'Kein Pfand' },
		...deposits.map((d) => ({
			value: d.id,
			label: `${d.label ? d.label + ' – ' : ''}${formatCents(d.singleAmount)} / Stück${d.crateAmount ? `, Kiste ${formatCents(d.crateAmount)}` : ''}`
		}))
	]);

	const save = async () => {
		errors = {};
		error = null;
		const salesPrice = parseEuroToCents(price);
		if (price.trim() !== '' && salesPrice === null) {
			errors = { salesPrice: 'Ungültiger Betrag.' };
			return;
		}
		const input: ProductInput = {
			name,
			salesPrice,
			depositId,
			sellable,
			rentable,
			quantityInCrate: quantityInCrate === '' ? null : Number(quantityInCrate),
			archived
		};
		saving = true;
		try {
			const saved = product
				? await api.put<Product>(`products/${product.id}`, input)
				: await api.post<Product>('products', input);
			toast.success(`Artikel „${saved.name}“ gespeichert.`);
			await goto('/products');
		} catch (e) {
			if (e instanceof ApiError && e.status === 422) errors = e.fieldErrors;
			error = userMessage(e);
		} finally {
			saving = false;
		}
	};
</script>

<form
	class="card max-w-2xl p-4 sm:p-6"
	onsubmit={(e) => {
		e.preventDefault();
		save();
	}}
	novalidate
>
	{#if error}<div class="mb-4"><Alert tone="danger">{error}</Alert></div>{/if}
	{#if readonly}<div class="mb-4">
			<Alert tone="info">Artikel können nur von der Verwaltung geändert werden.</Alert>
		</div>{/if}
	<fieldset class="flex flex-col gap-4" disabled={readonly}>
		<TextInput
			id="name"
			label="Bezeichnung"
			required
			bind:value={name}
			error={errors.name}
			placeholder="z. B. Pils 20x0,5l"
		/>
		<div class="grid gap-4 sm:grid-cols-2">
			<TextInput
				id="salesPrice"
				label="Verkaufspreis (€)"
				inputmode="decimal"
				bind:value={price}
				error={errors.salesPrice}
				placeholder="0,00"
			/>
			<TextInput
				id="quantityInCrate"
				label="Einheiten pro Kiste"
				type="number"
				min="1"
				bind:value={quantityInCrate}
				error={errors.quantityInCrate}
			/>
		</div>
		<Select id="depositId" label="Pfand" bind:value={depositId} options={depositOptions} error={errors.depositId} />
		<div class="flex flex-col gap-3">
			<Checkbox id="sellable" label="Verkäuflich" bind:checked={sellable} />
			<Checkbox id="rentable" label="Vermietbar (Leihartikel)" bind:checked={rentable} />
			{#if product}<Checkbox
					id="archived"
					label="Archiviert"
					description="Archivierte Artikel erscheinen nicht mehr in der Auswahl."
					bind:checked={archived}
				/>{/if}
		</div>
		{#if errors[''] || errors.sellable}<p class="text-sm text-red-600">{errors[''] ?? errors.sellable}</p>{/if}
	</fieldset>
	{#if !readonly}
		<div class="mt-6 flex justify-end gap-2">
			<Button variant="secondary" href="/products">Abbrechen</Button>
			<Button type="submit" loading={saving}>Speichern</Button>
		</div>
	{/if}
</form>
