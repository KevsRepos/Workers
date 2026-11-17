<script lang="ts">
	import { query } from '$lib/api/client';
	import PageHeader from '$lib/components/layout/PageHeader.svelte';
	import { formatHours, monthName } from '$lib/utils/format';

	let { data } = $props();
	const accountQuery = $derived(query({ accountId: data.accountId }));
	const total = $derived(data.months.reduce((sum, m) => sum + m.totalHours, 0));
</script>

<svelte:head><title>Zeiterfassung {data.year} · Workers</title></svelte:head>

<PageHeader
	title="Zeiterfassung {data.year}"
	subtitle="Gesamt: {formatHours(Math.round(total * 100) / 100)}"
	back="/time-tracking{accountQuery}"
/>

<div class="mb-4 flex flex-wrap gap-2">
	{#each data.years as year (year)}
		<a
			href="/time-tracking/{year}{accountQuery}"
			class="rounded-full px-3 py-1 text-sm font-medium ring-1 ring-inset {year === data.year
				? 'bg-brand-700 text-white ring-brand-700'
				: 'bg-white ring-stone-300 hover:bg-stone-50'}">{year}</a
		>
	{/each}
</div>

<div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-4">
	{#each data.months as summary (summary.month)}
		<a href="/time-tracking/{data.year}/{summary.month}{accountQuery}" class="card p-4 hover:border-brand-300">
			<p class="font-medium">{monthName(summary.month)}</p>
			<p class="mt-1 text-2xl font-semibold">{formatHours(summary.totalHours)}</p>
			<p class="text-xs text-stone-500">{summary.entries} Einträge</p>
		</a>
	{/each}
</div>
