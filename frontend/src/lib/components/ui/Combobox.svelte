<script lang="ts" generics="T">
	import type { Snippet } from 'svelte';
	import { Search } from '@lucide/svelte';
	import Spinner from './Spinner.svelte';

	/**
	 * Accessible async search with keyboard navigation (ARIA combobox pattern).
	 */
	interface Props {
		id: string;
		label: string;
		placeholder?: string;
		search: (query: string) => Promise<T[]>;
		onselect: (item: T) => void;
		itemKey: (item: T) => string;
		item: Snippet<[T]>;
		empty?: Snippet<[string]>;
		minLength?: number;
		value?: string;
		input?: HTMLInputElement | null;
		error?: string | null;
		clearOnSelect?: boolean;
	}

	let {
		id,
		label,
		placeholder = 'Suchen…',
		search,
		onselect,
		itemKey,
		item,
		empty,
		minLength = 1,
		value = $bindable(''),
		input = $bindable(null),
		error = null,
		clearOnSelect = true
	}: Props = $props();

	let results = $state<T[]>([]);
	let open = $state(false);
	let loading = $state(false);
	let active = $state(-1);
	let timer: ReturnType<typeof setTimeout>;
	let requestCounter = 0;

	const run = (query: string) => {
		clearTimeout(timer);
		if (query.trim().length < minLength) {
			results = [];
			open = false;
			return;
		}
		timer = setTimeout(async () => {
			const current = ++requestCounter;
			loading = true;
			try {
				const found = await search(query.trim());
				if (current === requestCounter) {
					results = found;
					active = found.length > 0 ? 0 : -1;
					open = true;
				}
			} finally {
				if (current === requestCounter) loading = false;
			}
		}, 200);
	};

	const choose = (entry: T) => {
		onselect(entry);
		open = false;
		results = [];
		if (clearOnSelect) value = '';
	};

	const onkeydown = (event: KeyboardEvent) => {
		if (event.key === 'ArrowDown') {
			event.preventDefault();
			if (!open && results.length) open = true;
			active = Math.min(results.length - 1, active + 1);
		} else if (event.key === 'ArrowUp') {
			event.preventDefault();
			active = Math.max(0, active - 1);
		} else if (event.key === 'Enter') {
			if (open && active >= 0 && results[active]) {
				event.preventDefault();
				choose(results[active]!);
			}
		} else if (event.key === 'Escape') {
			open = false;
		}
	};
</script>

<div class="relative flex flex-col gap-1.5">
	<label for={id} class="text-sm font-medium text-stone-700">{label}</label>
	<div class="relative">
		<Search
			class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-stone-400"
			aria-hidden="true"
		/>
		<input
			bind:this={input}
			{id}
			type="text"
			role="combobox"
			autocomplete="off"
			class="field-input pr-9 pl-9"
			{placeholder}
			aria-expanded={open}
			aria-controls="{id}-listbox"
			aria-activedescendant={active >= 0 && open ? `${id}-option-${active}` : undefined}
			aria-invalid={error ? 'true' : undefined}
			bind:value
			oninput={() => run(value)}
			onfocus={() => results.length && (open = true)}
			onblur={() => setTimeout(() => (open = false), 150)}
			{onkeydown}
		/>
		{#if loading}<span class="absolute top-1/2 right-3 -translate-y-1/2 text-stone-400"><Spinner size={16} /></span
			>{/if}
	</div>
	{#if error}<p class="text-sm text-red-600" role="alert">{error}</p>{/if}
	{#if open}
		<ul
			id="{id}-listbox"
			role="listbox"
			class="absolute top-full z-20 mt-1 max-h-80 w-full overflow-auto rounded-lg border border-stone-200 bg-white py-1 shadow-lg"
		>
			{#each results as entry, index (itemKey(entry))}
				<li
					id="{id}-option-{index}"
					role="option"
					aria-selected={index === active}
					class="cursor-pointer px-3 py-2 text-sm {index === active ? 'bg-brand-50' : 'hover:bg-stone-50'}"
					onmousedown={(e) => {
						e.preventDefault();
						choose(entry);
					}}
					onmouseenter={() => (active = index)}
				>
					{@render item(entry)}
				</li>
			{:else}
				<li class="px-3 py-2 text-sm text-stone-500">
					{#if empty}{@render empty(value)}{:else}Keine Treffer.{/if}
				</li>
			{/each}
		</ul>
	{/if}
</div>
