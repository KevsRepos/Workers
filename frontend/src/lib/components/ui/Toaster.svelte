<script lang="ts">
	import { CircleAlert, CircleCheck, Info, X } from '@lucide/svelte';
	import { fly } from 'svelte/transition';
	import { toast } from '$lib/stores/toast.svelte';

	const icons = { success: CircleCheck, error: CircleAlert, info: Info };
	const tones = {
		success: 'border-emerald-200 bg-white text-emerald-900',
		error: 'border-red-200 bg-white text-red-900',
		info: 'border-stone-200 bg-white text-stone-900'
	};
</script>

<div
	class="no-print pointer-events-none fixed inset-x-0 bottom-4 z-50 flex flex-col items-center gap-2 px-4 sm:items-end sm:pr-6"
	aria-live="polite"
>
	{#each toast.items as item (item.id)}
		{@const Icon = icons[item.tone]}
		<div
			transition:fly={{ y: 16, duration: 180 }}
			class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border p-3 shadow-lg {tones[
				item.tone
			]}"
			role={item.tone === 'error' ? 'alert' : 'status'}
		>
			<Icon class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
			<p class="flex-1 text-sm">{item.message}</p>
			<button
				type="button"
				class="rounded p-0.5 text-stone-400 hover:text-stone-700"
				aria-label="Meldung schließen"
				onclick={() => toast.dismiss(item.id)}
			>
				<X class="size-4" />
			</button>
		</div>
	{/each}
</div>
