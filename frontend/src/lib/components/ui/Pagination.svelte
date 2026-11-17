<script lang="ts">
	import { ChevronLeft, ChevronRight } from '@lucide/svelte';
	import { page as currentPage } from '$app/state';

	let { page, pages, total }: { page: number; pages: number; total: number } = $props();

	const href = (target: number) => {
		const url = new URL(currentPage.url);
		url.searchParams.set('page', String(target));
		return url.pathname + url.search;
	};
</script>

{#if pages > 1}
	<nav class="flex items-center justify-between gap-2 px-1 py-3 text-sm text-stone-600" aria-label="Seitennavigation">
		<span>{total} Einträge · Seite {page} von {pages}</span>
		<div class="flex gap-1">
			<a
				href={page > 1 ? href(page - 1) : undefined}
				class="inline-flex size-9 items-center justify-center rounded-lg border border-stone-300 bg-white hover:bg-stone-50 aria-disabled:pointer-events-none aria-disabled:opacity-40"
				aria-disabled={page <= 1}
				aria-label="Vorherige Seite"><ChevronLeft class="size-4" /></a
			>
			<a
				href={page < pages ? href(page + 1) : undefined}
				class="inline-flex size-9 items-center justify-center rounded-lg border border-stone-300 bg-white hover:bg-stone-50 aria-disabled:pointer-events-none aria-disabled:opacity-40"
				aria-disabled={page >= pages}
				aria-label="Nächste Seite"><ChevronRight class="size-4" /></a
			>
		</div>
	</nav>
{:else if total > 0}
	<p class="px-1 py-3 text-sm text-stone-500">{total} {total === 1 ? 'Eintrag' : 'Einträge'}</p>
{/if}
