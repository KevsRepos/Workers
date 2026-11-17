<script lang="ts">
	import { ArrowDown, ArrowUp, FileStack, Trash2 } from '@lucide/svelte';
	import { api, query } from '$lib/api/client';
	import type { OrderPreset, Page, Product } from '$lib/api/types';
	import { lineKey, type DraftLine } from '$lib/domain/deliveryNote';
	import Button from '$lib/components/ui/Button.svelte';
	import Combobox from '$lib/components/ui/Combobox.svelte';
	import Dialog from '$lib/components/ui/Dialog.svelte';
	import EmptyState from '$lib/components/ui/EmptyState.svelte';
	import Spinner from '$lib/components/ui/Spinner.svelte';
	import { highlightVolumePart } from '$lib/utils/volumeHighlighter';
	import { toast } from '$lib/stores/toast.svelte';
	import { userMessage } from '$lib/api/errors';
	import { tick } from 'svelte';

	let { lines = $bindable(), errors = {} }: { lines: DraftLine[]; errors?: Record<string, string> } = $props();

	let searchInput = $state<HTMLInputElement | null>(null);
	let quantityInputs = $state<Record<string, HTMLInputElement | undefined>>({});
	let presetsOpen = $state(false);
	let presets = $state<OrderPreset[] | null>(null);

	const searchProducts = async (q: string) =>
		(await api.get<Page<Product>>(`products${query({ q, limit: 12 })}`)).items;

	const add = async (product: Product, quantity: number | null = null, focusQuantity = true) => {
		const existing = lines.find((l) => l.productId === product.id && !l.id);
		if (existing) {
			if (focusQuantity) quantityInputs[existing.key]?.focus();
			return;
		}
		const line: DraftLine = {
			key: lineKey(),
			id: null,
			productId: product.id,
			productName: product.name,
			quantity,
			hasReturn: false
		};
		lines.push(line);
		if (focusQuantity) {
			await tick();
			quantityInputs[line.key]?.focus();
		}
	};

	const remove = (index: number) => {
		const line = lines[index];
		if (line?.hasReturn) {
			toast.error('Diese Position hat bereits eine Rückgabe und kann nicht entfernt werden.');
			return;
		}
		lines.splice(index, 1);
	};

	const move = (index: number, delta: number) => {
		const target = index + delta;
		if (target < 0 || target >= lines.length) return;
		const [line] = lines.splice(index, 1);
		lines.splice(target, 0, line!);
	};

	const openPresets = async () => {
		presetsOpen = true;
		if (presets === null) {
			try {
				presets = await api.get<OrderPreset[]>('order-presets');
			} catch (e) {
				toast.error(userMessage(e));
				presetsOpen = false;
			}
		}
	};

	const applyPreset = async (preset: OrderPreset) => {
		let added = 0;
		for (const product of preset.products) {
			if (!lines.some((l) => l.productId === product.id)) {
				await add(product, null, false);
				added++;
			}
		}
		presetsOpen = false;
		toast.success(`${added} Artikel aus „${preset.name}“ übernommen.`);
		await tick();
		const firstEmpty = lines.find((l) => l.quantity === null);
		if (firstEmpty) quantityInputs[firstEmpty.key]?.focus();
	};

	const onQuantityKey = (event: KeyboardEvent, index: number) => {
		if (event.key !== 'Enter') return;
		event.preventDefault();
		const next = lines[index + 1];
		if (next) quantityInputs[next.key]?.focus();
		else searchInput?.focus();
	};
</script>

<div class="flex flex-col gap-4">
	<div class="flex flex-col gap-2 sm:flex-row sm:items-end">
		<div class="flex-1">
			<Combobox
				id="product-search"
				label="Artikel hinzufügen"
				placeholder="Name eingeben, z. B. Pils, Stehtisch …"
				search={searchProducts}
				itemKey={(p) => p.id}
				onselect={(p) => add(p)}
				bind:input={searchInput}
				error={errors.lines}
			>
				{#snippet item(product)}
					<div class="flex items-center justify-between gap-3">
						<span>
							{#each highlightVolumePart(product.name) as part, i (i)}
								<span class={part.className}>{part.text}</span>
							{/each}
						</span>
						<span class="shrink-0 text-xs text-stone-500"
							>{product.rentable && !product.sellable ? 'Verleih' : product.deposit ? 'mit Pfand' : ''}</span
						>
					</div>
				{/snippet}
			</Combobox>
		</div>
		<Button variant="secondary" onclick={openPresets}><FileStack class="size-4" />Vorlage einfügen</Button>
	</div>

	{#if lines.length === 0}
		<div class="rounded-lg border border-dashed border-stone-300">
			<EmptyState
				title="Noch keine Artikel"
				description="Artikel über die Suche oder eine Bestellvorlage hinzufügen. Mengen können auch später ergänzt werden."
			/>
		</div>
	{:else}
		<ol class="divide-y divide-stone-100 rounded-lg border border-stone-200">
			{#each lines as line, index (line.key)}
				<li class="flex items-center gap-2 px-3 py-2">
					<span class="w-6 shrink-0 text-right text-xs text-stone-400">{index + 1}.</span>
					<span class="min-w-0 flex-1 truncate text-sm font-medium">
						{#each highlightVolumePart(line.productName) as part, i (i)}<span class={part.className}>{part.text}</span
							>{/each}
						{#if line.hasReturn}<span class="ml-1 text-xs font-normal text-stone-500">(Rückgabe erfasst)</span>{/if}
					</span>
					<label class="sr-only" for="qty-{line.key}">Menge für {line.productName}</label>
					<input
						id="qty-{line.key}"
						bind:this={quantityInputs[line.key]}
						type="number"
						inputmode="decimal"
						min="0"
						step="any"
						placeholder="Menge"
						class="field-input w-24 text-right"
						aria-invalid={errors[`lines[${index}].quantity`] ? 'true' : undefined}
						value={line.quantity ?? ''}
						oninput={(e) => {
							const raw = (e.currentTarget as HTMLInputElement).value;
							line.quantity = raw === '' ? null : Number(raw);
						}}
						onkeydown={(e) => onQuantityKey(e, index)}
					/>
					<div class="flex shrink-0">
						<button
							type="button"
							class="rounded p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-700 disabled:opacity-30"
							disabled={index === 0}
							aria-label="Nach oben"
							onclick={() => move(index, -1)}><ArrowUp class="size-4" /></button
						>
						<button
							type="button"
							class="rounded p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-700 disabled:opacity-30"
							disabled={index === lines.length - 1}
							aria-label="Nach unten"
							onclick={() => move(index, 1)}><ArrowDown class="size-4" /></button
						>
						<button
							type="button"
							class="rounded p-1.5 text-stone-400 hover:bg-red-50 hover:text-red-600"
							aria-label="{line.productName} entfernen"
							onclick={() => remove(index)}><Trash2 class="size-4" /></button
						>
					</div>
				</li>
			{/each}
		</ol>
		<p class="text-xs text-stone-500">
			Tipp: Enter springt zur nächsten Menge. Leere Mengen werden als „noch offen“ markiert.
		</p>
	{/if}
</div>

<Dialog bind:open={presetsOpen} title="Bestellvorlage einfügen">
	{#if presets === null}
		<div class="flex justify-center py-8"><Spinner /></div>
	{:else if presets.length === 0}
		<EmptyState
			title="Keine Vorlagen vorhanden"
			description="Vorlagen können unter „Bestellvorlagen“ angelegt werden."
		/>
	{:else}
		<ul class="flex flex-col gap-2">
			{#each presets as preset (preset.id)}
				<li>
					<button
						type="button"
						class="w-full rounded-lg border border-stone-200 p-3 text-left hover:border-brand-400 hover:bg-brand-50"
						onclick={() => applyPreset(preset)}
					>
						<span class="font-medium">{preset.name}</span>
						<span class="mt-0.5 block text-xs text-stone-500">{preset.products.map((p) => p.name).join(' · ')}</span>
					</button>
				</li>
			{/each}
		</ul>
	{/if}
</Dialog>
