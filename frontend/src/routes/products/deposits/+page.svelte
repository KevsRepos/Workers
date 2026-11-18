<script lang="ts">
	import { invalidate } from '$app/navigation';
	import { Pencil, Plus } from '@lucide/svelte';
	import { api } from '$lib/api/client';
	import { ApiError, userMessage } from '$lib/api/errors';
	import type { Deposit } from '$lib/api/types';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import Dialog from '$lib/components/ui/Dialog.svelte';
	import EmptyState from '$lib/components/ui/EmptyState.svelte';
	import TextInput from '$lib/components/ui/TextInput.svelte';
	import { toast } from '$lib/stores/toast.svelte';
	import { centsToEuroInput, formatCents, parseEuroToCents } from '$lib/utils/format';

	let { data } = $props();

	let open = $state(false);
	let editing = $state<Deposit | null>(null);
	let label = $state('');
	let single = $state('');
	let crate = $state('');
	let errors = $state<Record<string, string>>({});
	let saving = $state(false);

	const edit = (deposit: Deposit | null) => {
		editing = deposit;
		label = deposit?.label ?? '';
		single = centsToEuroInput(deposit?.singleAmount);
		crate = centsToEuroInput(deposit?.crateAmount);
		errors = {};
		open = true;
	};

	const save = async () => {
		const singleAmount = parseEuroToCents(single);
		if (singleAmount === null) {
			errors = { singleAmount: 'Bitte einen Betrag angeben.' };
			return;
		}
		saving = true;
		try {
			const body = { label: label || null, singleAmount, crateAmount: parseEuroToCents(crate) };
			if (editing) await api.put(`deposits/${editing.id}`, body);
			else await api.post('deposits', body);
			open = false;
			toast.success('Pfand gespeichert.');
			await invalidate('app:deposits');
		} catch (e) {
			if (e instanceof ApiError && e.status === 422) errors = e.fieldErrors;
			else toast.error(userMessage(e));
		} finally {
			saving = false;
		}
	};
</script>

<svelte:head><title>Pfand · Workers</title></svelte:head>

<PageHeader title="Pfand">
	{#snippet actions()}{#if data.user?.isAdmin}<Button onclick={() => edit(null)}
				><Plus class="size-4" />Neuer Pfandsatz</Button
			>{/if}{/snippet}
</PageHeader>

<div class="card overflow-hidden">
	{#if data.deposits.length === 0}
		<EmptyState title="Noch keine Pfandsätze" />
	{:else}
		<table class="w-full text-sm">
			<thead class="bg-stone-50 text-left text-xs tracking-wide text-stone-500 uppercase">
				<tr
					><th class="px-4 py-2 font-medium">Bezeichnung</th><th class="px-4 py-2 text-right font-medium"
						>je Flasche/Stück</th
					><th class="px-4 py-2 text-right font-medium">Kiste</th><th class="w-12"></th></tr
				>
			</thead>
			<tbody class="divide-y divide-stone-100">
				{#each data.deposits as deposit (deposit.id)}
					<tr>
						<td class="px-4 py-2.5">{deposit.label ?? '–'}</td>
						<td class="px-4 py-2.5 text-right">{formatCents(deposit.singleAmount)}</td>
						<td class="px-4 py-2.5 text-right">{formatCents(deposit.crateAmount)}</td>
						<td class="px-2"
							>{#if data.user?.isAdmin}<button
									class="rounded p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-700"
									aria-label="Bearbeiten"
									onclick={() => edit(deposit)}><Pencil class="size-4" /></button
								>{/if}</td
						>
					</tr>
				{/each}
			</tbody>
		</table>
	{/if}
</div>

<Dialog bind:open title={editing ? 'Pfandsatz bearbeiten' : 'Neuer Pfandsatz'} size="sm">
	<form
		id="deposit-form"
		class="flex flex-col gap-4"
		onsubmit={(e) => {
			e.preventDefault();
			save();
		}}
		novalidate
	>
		<TextInput
			id="deposit-label"
			label="Bezeichnung"
			bind:value={label}
			error={errors.label}
			placeholder="z. B. Bierkiste"
		/>
		<TextInput
			id="deposit-single"
			label="Pfand je Flasche/Stück (€)"
			inputmode="decimal"
			required
			bind:value={single}
			error={errors.singleAmount}
		/>
		<TextInput
			id="deposit-crate"
			label="Pfand Kiste (€)"
			inputmode="decimal"
			bind:value={crate}
			error={errors.crateAmount}
		/>
	</form>
	{#snippet footer()}
		<Button variant="secondary" onclick={() => (open = false)}>Abbrechen</Button>
		<Button type="submit" form="deposit-form" loading={saving}>Speichern</Button>
	{/snippet}
</Dialog>
