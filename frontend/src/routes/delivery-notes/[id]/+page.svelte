<script lang="ts">
	import { invalidate } from '$app/navigation';
	import {
		CalendarDays,
		Car,
		History,
		MapPin,
		Navigation,
		Pencil,
		Phone,
		PhoneOutgoing,
		Printer,
		RotateCcw,
		Truck,
		Users
	} from '@lucide/svelte';
	import { api } from '$lib/api/client';
	import { userMessage } from '$lib/api/errors';
	import { DeliveryNoteStatus, type DeliveryNote } from '$lib/api/types';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Alert from '$lib/components/ui/Alert.svelte';
	import Badge from '$lib/components/ui/Badge.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import { STATUS_OPTIONS, crateDepositCents, statusInfo } from '$lib/domain/deliveryNote';
	import { toast } from '$lib/stores/toast.svelte';
	import { formatCents, formatDateTime, formatNumber, formatWeekdayDate } from '$lib/utils/format';

	let { data } = $props();

	const note = $derived(data.note);
	const status = $derived(statusInfo(note.status));
	const missingQuantities = $derived(note.lines.filter((l) => l.quantity === null).length);
	const hasReturns = $derived(note.lines.some((l) => l.returnEntry !== null));

	let editingQuantities = $state(false);
	let quantities = $state<Record<string, string>>({});
	let savingQuantities = $state(false);
	let changingStatus = $state(false);

	const mapsUrl = $derived(
		note.shippingAddress
			? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(note.shippingAddress.singleLine)}`
			: null
	);

	const startQuantityEdit = () => {
		quantities = Object.fromEntries(note.lines.map((l) => [l.id, l.quantity === null ? '' : String(l.quantity)]));
		editingQuantities = true;
	};

	const saveQuantities = async () => {
		savingQuantities = true;
		try {
			await api.put<DeliveryNote>(`delivery-notes/${note.id}/quantities`, {
				lines: note.lines.map((l) => ({
					id: l.id,
					quantity:
						quantities[l.id] === '' || quantities[l.id] === undefined
							? null
							: Number(String(quantities[l.id]).replace(',', '.'))
				}))
			});
			editingQuantities = false;
			toast.success('Mengen gespeichert.');
			await invalidate('app:delivery-note');
		} catch (e) {
			toast.error(userMessage(e));
		} finally {
			savingQuantities = false;
		}
	};

	const changeStatus = async (value: DeliveryNoteStatus) => {
		if (value === note.status) return;
		if (value === DeliveryNoteStatus.Canceled && !confirm('Lieferschein wirklich stornieren?')) return;
		changingStatus = true;
		try {
			await api.put<DeliveryNote>(`delivery-notes/${note.id}/status`, { status: value });
			toast.success(`Status: ${statusInfo(value).label}`);
			await invalidate('app:delivery-note');
		} catch (e) {
			toast.error(userMessage(e));
		} finally {
			changingStatus = false;
		}
	};
</script>

<svelte:head><title>{note.number} · {note.customer.displayName} · Workers</title></svelte:head>

<PageHeader title={note.customer.displayName} subtitle="Lieferschein {note.number}" back="/delivery-notes">
	{#snippet meta()}
		<Badge tone={status.tone}>{status.label}</Badge>
		{#if note.assignment}<Badge>{note.assignment}</Badge>{/if}
		{#if note.callBefore}<Badge tone="warning"><PhoneOutgoing class="size-3" />Vorher anrufen</Badge>{/if}
	{/snippet}
	{#snippet actions()}
		<Button variant="secondary" href="/delivery-notes/{note.id}/edit"><Pencil class="size-4" />Bearbeiten</Button>
		<Button variant="secondary" href="/delivery-notes/{note.id}/return"><RotateCcw class="size-4" />Rückgabe</Button>
		<Button href="/delivery-notes/{note.id}/print?copy=delivery"><Printer class="size-4" />Drucken</Button>
	{/snippet}
</PageHeader>

{#if missingQuantities > 0}
	<div class="mb-4">
		<Alert tone="warning" title="Mengen unvollständig">Bei {missingQuantities} Artikel(n) fehlt noch die Menge.</Alert>
	</div>
{/if}

<div class="grid gap-6 lg:grid-cols-3">
	<div class="flex flex-col gap-6 lg:col-span-2">
		<section class="card overflow-hidden" aria-labelledby="lines-title">
			<div class="flex items-center justify-between border-b border-stone-200 px-4 py-3">
				<h2 id="lines-title" class="text-base">Artikel</h2>
				{#if !editingQuantities}
					<Button size="sm" variant="ghost" onclick={startQuantityEdit}><Pencil class="size-4" />Mengen</Button>
				{/if}
			</div>
			<div class="overflow-x-auto">
				<table class="w-full text-sm">
					<thead class="bg-stone-50 text-left text-xs tracking-wide text-stone-500 uppercase">
						<tr>
							<th class="px-4 py-2 font-medium">Artikel</th>
							<th class="px-4 py-2 text-right font-medium">Menge</th>
							{#if hasReturns}<th class="px-4 py-2 text-right font-medium">Zurück</th><th
									class="px-4 py-2 text-right font-medium">davon voll</th
								>{/if}
						</tr>
					</thead>
					<tbody class="divide-y divide-stone-100">
						{#each note.lines as line (line.id)}
							<tr>
								<td class="px-4 py-2.5">
									{line.productName}
									{#if line.deposit}<span class="block text-xs text-stone-500"
											>Pfand {formatCents(crateDepositCents(line.deposit, line.quantityInCrate))}{line.quantityInCrate
												? ` / Kiste (${line.quantityInCrate} Fl.)`
												: ''}</span
										>{/if}
								</td>
								<td class="px-4 py-2.5 text-right">
									{#if editingQuantities}
										<label class="sr-only" for="q-{line.id}">Menge {line.productName}</label>
										<input
											id="q-{line.id}"
											class="field-input ml-auto w-24 text-right"
											type="number"
											inputmode="decimal"
											min="0"
											step="any"
											bind:value={quantities[line.id]}
										/>
									{:else if line.quantity === null}
										<Badge tone="warning">offen</Badge>
									{:else}
										{formatNumber(line.quantity)}
									{/if}
								</td>
								{#if hasReturns}
									<td class="px-4 py-2.5 text-right"
										>{line.returnEntry ? formatNumber(line.returnEntry.returnedTotal) : '–'}</td
									>
									<td class="px-4 py-2.5 text-right"
										>{line.returnEntry ? formatNumber(line.returnEntry.returnedFull) : '–'}</td
									>
								{/if}
							</tr>
						{/each}
					</tbody>
				</table>
			</div>
			{#if editingQuantities}
				<div class="flex justify-end gap-2 border-t border-stone-200 px-4 py-3">
					<Button variant="secondary" size="sm" onclick={() => (editingQuantities = false)}>Abbrechen</Button>
					<Button size="sm" loading={savingQuantities} onclick={saveQuantities}>Mengen speichern</Button>
				</div>
			{/if}
		</section>

		{#if note.shortDescription || note.privateDescription}
			<section class="grid gap-4 sm:grid-cols-2">
				{#if note.shortDescription}
					<div class="card p-4">
						<h3 class="mb-1 text-sm text-stone-500">Hinweis für den Kunden</h3>
						<p class="text-sm whitespace-pre-line">{note.shortDescription}</p>
					</div>
				{/if}
				{#if note.privateDescription}
					<div class="card border-amber-200 bg-amber-50/50 p-4">
						<h3 class="mb-1 text-sm text-amber-800">Interne Notiz</h3>
						<p class="text-sm whitespace-pre-line">{note.privateDescription}</p>
					</div>
				{/if}
			</section>
		{/if}
	</div>

	<aside class="flex flex-col gap-6">
		<section class="card p-4" aria-labelledby="status-title">
			<h2 id="status-title" class="mb-3 text-base">Status</h2>
			<div class="grid grid-cols-2 gap-2">
				{#each STATUS_OPTIONS as option (option.value)}
					<button
						type="button"
						disabled={changingStatus}
						class="rounded-lg border px-3 py-2 text-sm font-medium transition-colors {note.status === option.value
							? 'border-brand-600 bg-brand-50 text-brand-900'
							: 'border-stone-200 hover:bg-stone-50'}"
						aria-pressed={note.status === option.value}
						onclick={() => changeStatus(option.value)}>{option.label}</button
					>
				{/each}
			</div>
		</section>

		<section class="card p-4 text-sm" aria-labelledby="details-title">
			<h2 id="details-title" class="mb-3 text-base">Details</h2>
			<dl class="flex flex-col gap-3">
				<div class="flex gap-3">
					<dt class="text-stone-400">
						{#if note.delivery}<Truck class="size-4" aria-label="Art" />{:else}<Car
								class="size-4"
								aria-label="Art"
							/>{/if}
					</dt>
					<dd>
						{note.delivery ? 'Lieferung' : 'Abholung durch Kunden'} am
						<strong>{formatWeekdayDate(note.deliveryDate)}</strong>
					</dd>
				</div>
				{#if note.pickupDate}
					<div class="flex gap-3">
						<dt class="text-stone-400"><CalendarDays class="size-4" aria-label="Rückgabe" /></dt>
						<dd>Rückgabe am <strong>{formatWeekdayDate(note.pickupDate)}</strong></dd>
					</div>
				{/if}
				{#if note.adultGuests || note.childGuests}
					<div class="flex gap-3">
						<dt class="text-stone-400"><Users class="size-4" aria-label="Gäste" /></dt>
						<dd>{note.adultGuests ?? 0} Erwachsene, {note.childGuests ?? 0} Kinder</dd>
					</div>
				{/if}
				{#if note.customer.phone}
					<div class="flex gap-3">
						<dt class="text-stone-400"><Phone class="size-4" aria-label="Telefon" /></dt>
						<dd><a class="text-brand-700 underline" href="tel:{note.customer.phone}">{note.customer.phone}</a></dd>
					</div>
				{/if}
				{#if note.shippingAddress}
					<div class="flex gap-3">
						<dt class="text-stone-400"><MapPin class="size-4" aria-label="Lieferadresse" /></dt>
						<dd class="flex-1">
							{note.shippingAddress.street}
							{note.shippingAddress.houseNumber}<br />{note.shippingAddress.postalCode}
							{note.shippingAddress.city}
							{#if note.shippingAddress.note}<span class="block text-xs text-stone-500"
									>{note.shippingAddress.note}</span
								>{/if}
						</dd>
						{#if mapsUrl}<a
								href={mapsUrl}
								target="_blank"
								rel="noopener noreferrer"
								class="self-start rounded p-1 text-brand-700 hover:bg-brand-50"
								aria-label="In Karten öffnen"><Navigation class="size-4" /></a
							>{/if}
					</div>
				{/if}
			</dl>
			<div class="mt-4 border-t border-stone-100 pt-3 text-xs text-stone-500">
				<p>Angelegt {formatDateTime(note.createdAt)}{note.createdBy ? ` von ${note.createdBy}` : ''}</p>
				{#if note.updatedAt}<p>Geändert {formatDateTime(note.updatedAt)}</p>{/if}
			</div>
			<div class="mt-3 flex flex-wrap gap-2">
				<a class="text-sm font-medium text-brand-700 hover:underline" href="/customers/{note.customer.id}">Zum Kunden</a
				>
				{#if data.user?.isAdmin}
					<a
						class="flex items-center gap-1 text-sm font-medium text-brand-700 hover:underline"
						href="/admin/audit-log?subjectType=DeliveryNote&subjectId={note.id}"
						><History class="size-3.5" />Änderungsverlauf</a
					>
				{/if}
			</div>
		</section>

		<section class="card p-4" aria-labelledby="print-title">
			<h2 id="print-title" class="mb-3 text-base">Drucken</h2>
			<div class="flex flex-col gap-2">
				<Button variant="secondary" href="/delivery-notes/{note.id}/print?copy=delivery"
					><Printer class="size-4" />Lieferschein (Kunde + intern)</Button
				>
				<Button variant="secondary" href="/delivery-notes/{note.id}/print?copy=return"
					><Printer class="size-4" />Rückgabeschein</Button
				>
			</div>
		</section>
	</aside>
</div>
