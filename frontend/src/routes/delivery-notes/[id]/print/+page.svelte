<script lang="ts">
	import { onMount } from 'svelte';
	import { Printer } from '@lucide/svelte';
	import { api } from '$lib/api/client';
	import { DeliveryNoteStatus } from '$lib/api/types';
	import Button from '$lib/components/ui/Button.svelte';
	import { company as companyConfig } from '$lib/company';
	import { crateDepositCents } from '$lib/domain/deliveryNote';
	import { formatCents, formatDate, formatNumber, todayIso } from '$lib/utils/format';

	let { data } = $props();
	const note = $derived(data.note);
	const company = companyConfig();
	let logoAvailable = $state(false);

	const print = async () => {
		window.print();
		// Printing the delivery note marks an open note as delivered (as before), never downgrades.
		if (data.copy === 'delivery' && note.status === DeliveryNoteStatus.Open) {
			await api
				.put(`delivery-notes/${note.id}/status`, { status: DeliveryNoteStatus.Delivered })
				.catch(() => undefined);
		}
	};

	onMount(() => {
		// Probe the optional logo without an inline onerror handler (blocked by the CSP).
		const probe = new Image();
		probe.onload = () => (logoAvailable = true);
		probe.src = '/img/logo.png';
		const timer = setTimeout(print, 300);
		return () => clearTimeout(timer);
	});
</script>

<svelte:head><title>{note.number} drucken · Workers</title></svelte:head>

<div class="no-print mb-6 flex flex-wrap items-center justify-between gap-2">
	<a href="/delivery-notes/{note.id}" class="text-sm font-medium text-brand-700 hover:underline"
		>← Zurück zum Lieferschein</a
	>
	<div class="flex gap-2">
		<Button
			variant="secondary"
			href="/delivery-notes/{note.id}/print?copy={data.copy === 'return' ? 'delivery' : 'return'}"
			>{data.copy === 'return' ? 'Lieferschein' : 'Rückgabeschein'} anzeigen</Button
		>
		<Button onclick={print}><Printer class="size-4" />Drucken</Button>
	</div>
</div>

{#snippet letterhead()}
	<div class="flex items-start justify-between gap-8">
		<div class="w-[10.5cm]">
			<p class="mb-4 w-fit border-b border-black text-[8pt]">
				{company.name} · {company.street} · {company.postalCode}
				{company.city}
			</p>
			<p class="font-bold">{note.customer.displayName}</p>
			{#if note.delivery && note.shippingAddress}
				<p>
					{note.shippingAddress.street}
					{note.shippingAddress.houseNumber}<br />{note.shippingAddress.postalCode}
					{note.shippingAddress.city}
				</p>
			{:else if note.billingAddress}
				<p>
					{note.billingAddress.street}
					{note.billingAddress.houseNumber}<br />{note.billingAddress.postalCode}
					{note.billingAddress.city}
				</p>
			{/if}
		</div>
		<div class="w-[5.5cm] text-right text-[9pt]">
			{#if logoAvailable}<img src="/img/logo.png" alt="" class="mb-4 ml-auto w-[5cm]" />{/if}
			<p>{company.name}<br />{company.street}<br />{company.postalCode} {company.city}</p>
			<p class="mt-2">
				{#if company.phone}Tel. {company.phone}<br />{/if}
				{#if company.fax}Fax {company.fax}<br />{/if}
				{#if company.email}{company.email}{/if}
			</p>
		</div>
	</div>
{/snippet}

{#snippet meta(title: string)}
	<div class="mt-10 mb-4 flex items-end justify-between">
		<h1 class="text-[18pt] font-bold">{title}</h1>
		<table class="text-[9pt]">
			<tbody>
				<tr><td class="pr-3 text-stone-600">Nr.</td><td class="font-semibold">{note.number}</td></tr>
				<tr><td class="pr-3 text-stone-600">Datum</td><td>{formatDate(todayIso())}</td></tr>
				<tr
					><td class="pr-3 text-stone-600">{note.delivery ? 'Lieferung' : 'Abholung'}</td><td
						>{formatDate(note.deliveryDate)}</td
					></tr
				>
				{#if note.pickupDate}<tr><td class="pr-3 text-stone-600">Rückgabe</td><td>{formatDate(note.pickupDate)}</td></tr
					>{/if}
			</tbody>
		</table>
	</div>
{/snippet}

{#if data.copy === 'delivery'}
	{#each ['customer', 'internal'] as copy (copy)}
		<article class="sheet">
			{@render letterhead()}
			{@render meta(copy === 'customer' ? 'Lieferschein' : 'Lieferschein – interne Kopie')}
			{#if note.shortDescription}<p class="mb-4 whitespace-pre-line">{note.shortDescription}</p>{/if}
			{#if copy === 'internal' && note.privateDescription}<p
					class="mb-4 border-l-4 border-black pl-3 whitespace-pre-line"
				>
					<strong>Intern:</strong>
					{note.privateDescription}
				</p>{/if}
			<table class="w-full border-collapse text-[10pt]">
				<thead>
					<tr class="border-b-2 border-black text-left">
						<th class="py-1">Pos.</th>
						<th class="py-1">Artikel</th>
						<th class="py-1 text-right">Menge</th>
						{#if copy === 'internal'}<th class="py-1 text-right">Zurück</th><th class="py-1 text-right">davon voll</th
							>{/if}
					</tr>
				</thead>
				<tbody>
					{#each note.lines as line, i (line.id)}
						<tr class="border-b border-stone-400">
							<td class="py-1.5 pr-2">{i + 1}</td>
							<td class="py-1.5">{line.productName}</td>
							<td class="py-1.5 text-right">{line.quantity === null ? '' : formatNumber(line.quantity)}</td>
							{#if copy === 'internal'}<td class="py-1.5 text-right">____</td><td class="py-1.5 text-right">____</td
								>{/if}
						</tr>
					{/each}
				</tbody>
			</table>
			{#if copy === 'internal'}
				<div class="mt-6 grid grid-cols-2 gap-6 text-[9pt]">
					<p>Zuständig: {note.assignment ?? '________________'}</p>
					<p>
						{note.callBefore ? 'Bitte vorher anrufen' : ''}{note.customer.phone ? ` · Tel. ${note.customer.phone}` : ''}
					</p>
				</div>
			{/if}
			<div class="mt-16 grid grid-cols-2 gap-12 text-[9pt]">
				<p class="border-t border-black pt-1">Datum, Unterschrift Kunde</p>
				<p class="border-t border-black pt-1">Datum, Unterschrift {company.name || 'Lieferant'}</p>
			</div>
			<p class="mt-10 text-center text-[8pt]">
				Alle gelieferten Waren bleiben bis zur vollständigen Bezahlung Eigentum des Lieferanten.
			</p>
		</article>
	{/each}
{:else}
	<article class="sheet">
		{@render letterhead()}
		{@render meta('Rückgabeschein')}
		<table class="w-full border-collapse text-[10pt]">
			<thead>
				<tr class="border-b-2 border-black text-left">
					<th class="py-1">Artikel</th>
					<th class="py-1 text-right">Ausgegeben</th>
					<th class="py-1 text-right">Zurück</th>
					<th class="py-1 text-right">davon voll</th>
					<th class="py-1 text-right">Pfand</th>
				</tr>
			</thead>
			<tbody>
				{#each data.groups as group (group.key)}
					{@const entry = group.returnEntry}
					{@const crate = crateDepositCents(group.deposit, group.quantityInCrate)}
					<tr class="border-b border-stone-400 align-top">
						<td class="py-1.5"
							>{group.name}{#if group.isUnion}<span class="block text-[8pt] text-stone-600"
									>{group.productNames.join(', ')}</span
								>{/if}</td
						>
						<td class="py-1.5 text-right">{formatNumber(group.quantity)}</td>
						<td class="py-1.5 text-right"
							>{entry?.returnedTotal ?? ''}{#if entry?.returnedTotalBottles}<br />{entry.returnedTotalBottles} Fl.{/if}</td
						>
						<td class="py-1.5 text-right"
							>{entry?.returnedFull ?? ''}{#if entry?.returnedFullBottles}<br />{entry.returnedFullBottles} Fl.{/if}</td
						>
						<td class="py-1.5 text-right">
							{#if group.deposit && entry}
								{#if entry.returnedTotal}{entry.returnedTotal} × {formatCents(crate)}{/if}
								{#if entry.returnedTotalBottles}<br />{entry.returnedTotalBottles} × {formatCents(
										group.deposit.singleAmount
									)}{/if}
							{/if}
						</td>
					</tr>
				{/each}
			</tbody>
		</table>
		<div class="mt-16 grid grid-cols-2 gap-12 text-[9pt]">
			<p class="border-t border-black pt-1">Datum, Unterschrift Kunde</p>
			<p class="border-t border-black pt-1">Datum, Unterschrift {company.name || 'Lieferant'}</p>
		</div>
	</article>
{/if}

<style>
	.sheet {
		background: white;
		color: black;
		font-size: 10pt;
		margin: 0 auto 2rem;
		max-width: 21cm;
		min-height: 29.7cm;
		padding: 1.8cm 2cm;
		box-shadow: 0 1px 3px rgb(0 0 0 / 0.15);
	}

	@media print {
		@page {
			size: A4;
			margin: 0;
		}
		.sheet {
			box-shadow: none;
			margin: 0;
			break-after: page;
		}
	}
</style>
