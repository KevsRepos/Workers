<script lang="ts">
	import { ClipboardList, Clock, Plus, UserPlus } from '@lucide/svelte';
	import DeliveryNoteCard from '$lib/components/DeliveryNoteCard.svelte';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import Button from '$lib/components/ui/Button.svelte';
	import EmptyState from '$lib/components/ui/EmptyState.svelte';
	import { DeliveryNoteStatus } from '$lib/api/types';
	import { formatHours, formatWeekdayDate } from '$lib/utils/format';

	let { data } = $props();

	const greeting = $derived.by(() => {
		const hour = Number(
			new Intl.DateTimeFormat('de-DE', { hour: 'numeric', hour12: false, timeZone: 'Europe/Berlin' }).format(new Date())
		);
		return hour < 11 ? 'Guten Morgen' : hour < 18 ? 'Hallo' : 'Guten Abend';
	});
	const firstName = $derived(data.user?.name.split(' ')[0] ?? '');
</script>

<svelte:head><title>Übersicht · Workers</title></svelte:head>

<PageHeader title="{greeting}, {firstName}" subtitle={formatWeekdayDate(data.today)}>
	{#snippet actions()}
		<Button href="/customers/new" variant="secondary"><UserPlus class="size-4" />Kunde</Button>
		<Button href="/delivery-notes/new"><Plus class="size-4" />Lieferschein</Button>
	{/snippet}
</PageHeader>

<div class="grid gap-4 sm:grid-cols-3">
	<a href="/delivery-notes?status=1" class="card flex items-center gap-4 p-4 hover:border-brand-300">
		<div class="rounded-lg bg-sky-50 p-2.5 text-sky-700"><ClipboardList class="size-5" aria-hidden="true" /></div>
		<div>
			<p class="text-2xl font-semibold">{data.counts[DeliveryNoteStatus.Open] ?? 0}</p>
			<p class="text-sm text-stone-500">offene Lieferscheine</p>
		</div>
	</a>
	<a href="/delivery-notes?status=2" class="card flex items-center gap-4 p-4 hover:border-brand-300">
		<div class="rounded-lg bg-brand-50 p-2.5 text-brand-700"><ClipboardList class="size-5" aria-hidden="true" /></div>
		<div>
			<p class="text-2xl font-semibold">{data.counts[DeliveryNoteStatus.Delivered] ?? 0}</p>
			<p class="text-sm text-stone-500">ausgeliefert, Rückgabe offen</p>
		</div>
	</a>
	<a href="/time-tracking" class="card flex items-center gap-4 p-4 hover:border-brand-300">
		<div class="rounded-lg bg-amber-50 p-2.5 text-amber-700"><Clock class="size-5" aria-hidden="true" /></div>
		<div>
			{#if data.todayEntry}
				<p class="text-2xl font-semibold">{formatHours(data.todayEntry.hours)}</p>
				<p class="text-sm text-stone-500">heute erfasst · {formatHours(data.monthHours)} im Monat</p>
			{:else}
				<p class="text-base font-semibold text-amber-800">Heute noch nichts erfasst</p>
				<p class="text-sm text-stone-500">{formatHours(data.monthHours)} im Monat</p>
			{/if}
		</div>
	</a>
</div>

<section class="mt-8">
	<div class="mb-3 flex items-center justify-between">
		<h2 class="text-lg">Die nächsten 7 Tage</h2>
		<a href="/delivery-notes?status=1" class="text-sm font-medium text-brand-700 hover:underline">Alle offenen</a>
	</div>
	<div class="card divide-y divide-stone-100 overflow-hidden">
		{#each data.upcoming.items as note (note.id)}
			<DeliveryNoteCard {note} />
		{:else}
			<EmptyState title="Keine anstehenden Lieferungen" description="In den nächsten 7 Tagen ist nichts geplant." />
		{/each}
	</div>
</section>
