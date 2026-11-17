<script lang="ts">
	import { Car, ChevronRight, MapPin, Phone, Truck } from '@lucide/svelte';
	import type { DeliveryNoteSummary } from '$lib/api/types';
	import Badge from '$lib/components/ui/Badge.svelte';
	import { statusInfo } from '$lib/domain/deliveryNote';
	import { formatWeekdayDate } from '$lib/utils/format';

	let { note }: { note: DeliveryNoteSummary } = $props();
	const status = $derived(statusInfo(note.status));
</script>

<a href="/delivery-notes/{note.id}" class="group flex items-center gap-4 px-4 py-3 transition-colors hover:bg-stone-50">
	<div
		class="hidden size-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700 sm:flex"
		aria-hidden="true"
	>
		{#if note.delivery}<Truck class="size-5" />{:else}<Car class="size-5" />{/if}
	</div>
	<div class="min-w-0 flex-1">
		<div class="flex flex-wrap items-center gap-x-2 gap-y-1">
			<span class="truncate font-medium text-stone-900">{note.customerName}</span>
			<Badge tone={status.tone}>{status.label}</Badge>
			{#if note.assignment}<Badge>{note.assignment}</Badge>{/if}
			{#if note.callBefore}<Badge tone="warning"><Phone class="size-3" aria-hidden="true" />Vorher anrufen</Badge>{/if}
		</div>
		<div class="mt-1 flex flex-wrap gap-x-4 gap-y-0.5 text-sm text-stone-500">
			<span>{note.number}</span>
			<span>{note.delivery ? 'Lieferung' : 'Abholung'} {formatWeekdayDate(note.deliveryDate)}</span>
			{#if note.pickupDate}<span>zurück {formatWeekdayDate(note.pickupDate)}</span>{/if}
			{#if note.shippingAddress && note.delivery}
				<span class="flex items-center gap-1"><MapPin class="size-3.5" aria-hidden="true" />{note.shippingAddress}</span
				>
			{/if}
		</div>
		{#if note.shortDescription}<p class="mt-1 line-clamp-1 text-sm text-stone-600">{note.shortDescription}</p>{/if}
	</div>
	<ChevronRight class="size-5 shrink-0 text-stone-300 group-hover:text-stone-500" aria-hidden="true" />
</a>
