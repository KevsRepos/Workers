<script lang="ts">
	import { goto } from '$app/navigation';
	import { api } from '$lib/api/client';
	import { ApiError, userMessage } from '$lib/api/errors';
	import type { DeliveryNote } from '$lib/api/types';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Alert from '$lib/components/ui/Alert.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import Checkbox from '$lib/components/ui/Checkbox.svelte';
	import { depositRefundCents, hasBottles, rowErrors, rowsFromGroups, rowsToEntries } from '$lib/domain/returnNote';
	import { toast } from '$lib/stores/toast.svelte';
	import { formatCents, formatNumber } from '$lib/utils/format';

	let { data } = $props();

	// svelte-ignore state_referenced_locally
	let rows = $state(rowsFromGroups(data.groups));
	let markReturned = $state(true);
	let saving = $state(false);
	let error = $state<string | null>(null);

	const refund = $derived(rows.reduce((sum, row) => sum + depositRefundCents(row), 0));
	const invalid = $derived(rows.some((row) => rowErrors(row) !== null));

	const numberInput = (value: string): number | null => (value === '' ? null : Math.max(0, Math.floor(Number(value))));

	const fillAll = () => {
		for (const row of rows) {
			row.returnedTotal ??= Math.ceil(row.group.quantity);
		}
	};

	const save = async () => {
		error = null;
		const entries = rowsToEntries(rows);
		if (entries.length === 0) {
			error = 'Bitte mindestens eine Rückgabe erfassen.';
			return;
		}
		saving = true;
		try {
			await api.put<DeliveryNote>(`delivery-notes/${data.note.id}/return-note`, { entries, markReturned });
			toast.success('Rückgabe gespeichert.');
			await goto(`/delivery-notes/${data.note.id}`);
		} catch (e) {
			error =
				e instanceof ApiError && e.status === 422
					? (Object.values(e.fieldErrors)[0] ?? userMessage(e))
					: userMessage(e);
		} finally {
			saving = false;
		}
	};
</script>

<svelte:head><title>Rückgabe {data.note.number} · Workers</title></svelte:head>

<PageHeader
	title="Rückgabe erfassen"
	subtitle="{data.note.number} · {data.note.customer.displayName}"
	back="/delivery-notes/{data.note.id}"
>
	{#snippet actions()}
		<Button variant="secondary" onclick={fillAll}>Alles zurück</Button>
	{/snippet}
</PageHeader>

{#if error}<div class="mb-4"><Alert tone="danger">{error}</Alert></div>{/if}

<form
	onsubmit={(e) => {
		e.preventDefault();
		save();
	}}
	class="flex flex-col gap-4 pb-24"
>
	{#each rows as row, index (row.group.key)}
		{@const problem = rowErrors(row)}
		<fieldset class="card p-4">
			<legend class="sr-only">{row.group.name}</legend>
			<div class="mb-3 flex flex-wrap items-baseline justify-between gap-2">
				<div>
					<p class="font-medium">{row.group.name}</p>
					{#if row.group.isUnion}<p class="text-xs text-stone-500">
							Zusammengefasst: {row.group.productNames.join(', ')}
						</p>{/if}
				</div>
				<p class="text-sm text-stone-600">ausgegeben: <strong>{formatNumber(row.group.quantity)}</strong></p>
			</div>
			<div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
				<label class="flex flex-col gap-1 text-sm">
					<span class="font-medium text-stone-700">{hasBottles(row.group) ? 'Kisten zurück' : 'Zurück gesamt'}</span>
					<input
						class="field-input"
						type="number"
						min="0"
						inputmode="numeric"
						value={row.returnedTotal ?? ''}
						oninput={(e) => (row.returnedTotal = numberInput(e.currentTarget.value))}
						id="total-{index}"
					/>
				</label>
				<label class="flex flex-col gap-1 text-sm">
					<span class="font-medium text-stone-700"
						>{hasBottles(row.group) ? 'davon volle Kisten' : 'davon unbenutzt'}</span
					>
					<input
						class="field-input"
						type="number"
						min="0"
						inputmode="numeric"
						value={row.returnedFull ?? ''}
						oninput={(e) => (row.returnedFull = numberInput(e.currentTarget.value))}
						aria-invalid={problem ? 'true' : undefined}
					/>
				</label>
				{#if hasBottles(row.group)}
					<label class="flex flex-col gap-1 text-sm">
						<span class="font-medium text-stone-700">lose Flaschen zurück</span>
						<input
							class="field-input"
							type="number"
							min="0"
							inputmode="numeric"
							value={row.returnedTotalBottles ?? ''}
							oninput={(e) => (row.returnedTotalBottles = numberInput(e.currentTarget.value))}
						/>
					</label>
					<label class="flex flex-col gap-1 text-sm">
						<span class="font-medium text-stone-700">davon volle Flaschen</span>
						<input
							class="field-input"
							type="number"
							min="0"
							inputmode="numeric"
							value={row.returnedFullBottles ?? ''}
							oninput={(e) => (row.returnedFullBottles = numberInput(e.currentTarget.value))}
							aria-invalid={problem ? 'true' : undefined}
						/>
					</label>
				{/if}
			</div>
			{#if problem}<p class="mt-2 text-sm text-red-600" role="alert">{problem}</p>{/if}
			{#if row.group.deposit && depositRefundCents(row) > 0}<p class="mt-2 text-sm text-stone-600">
					Pfanderstattung: {formatCents(depositRefundCents(row))}
				</p>{/if}
		</fieldset>
	{/each}

	<div
		class="no-print fixed inset-x-0 bottom-0 z-20 border-t border-stone-200 bg-white/95 px-4 py-3 backdrop-blur lg:left-64"
	>
		<div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 sm:px-2 lg:px-4">
			<div class="flex flex-wrap items-center gap-4">
				<Checkbox id="markReturned" label="Status auf „Zurückgegeben“ setzen" bind:checked={markReturned} />
				{#if refund > 0}<span class="text-sm font-medium">Pfand gesamt: {formatCents(refund)}</span>{/if}
			</div>
			<div class="flex gap-2">
				<Button variant="secondary" href="/delivery-notes/{data.note.id}">Abbrechen</Button>
				<Button type="submit" loading={saving} disabled={invalid}>Rückgabe speichern</Button>
			</div>
		</div>
	</div>
</form>
