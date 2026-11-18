<script lang="ts">
	import { Search, X } from '@lucide/svelte';
	import { goto } from '$app/navigation';
	import { page } from '$app/state';

	/**
	 * Search box that writes ?q= into the URL (debounced), so results are shareable and survive reloads.
	 */
	let {
		placeholder = 'Suchen…',
		param = 'q',
		label = 'Suche'
	}: { placeholder?: string; param?: string; label?: string } = $props();

	// svelte-ignore state_referenced_locally
	let value = $state(page.url.searchParams.get(param) ?? '');
	let timer: ReturnType<typeof setTimeout>;

	const apply = (next: string) => {
		const url = new URL(page.url);
		if (next.trim()) url.searchParams.set(param, next.trim());
		else url.searchParams.delete(param);
		url.searchParams.delete('page');
		goto(url.pathname + url.search, { keepFocus: true, replaceState: true, noScroll: true });
	};

	const oninput = () => {
		clearTimeout(timer);
		timer = setTimeout(() => apply(value), 300);
	};
</script>

<div class="relative w-full">
	<Search
		class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-stone-400"
		aria-hidden="true"
	/>
	<input type="search" class="field-input pr-9 pl-9" aria-label={label} {placeholder} bind:value {oninput} />
	{#if value}
		<button
			type="button"
			class="absolute top-1/2 right-2 -translate-y-1/2 rounded p-1 text-stone-400 hover:text-stone-700"
			aria-label="Suche leeren"
			onclick={() => {
				value = '';
				apply('');
			}}><X class="size-4" /></button
		>
	{/if}
</div>
