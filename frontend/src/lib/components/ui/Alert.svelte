<script lang="ts">
	import type { Snippet } from 'svelte';
	import { CircleAlert, CircleCheck, Info, TriangleAlert } from '@lucide/svelte';

	type Tone = 'info' | 'success' | 'warning' | 'danger';

	let { tone = 'info', title, children }: { tone?: Tone; title?: string; children?: Snippet } = $props();

	const styles: Record<Tone, string> = {
		info: 'bg-sky-50 text-sky-900 border-sky-200',
		success: 'bg-emerald-50 text-emerald-900 border-emerald-200',
		warning: 'bg-amber-50 text-amber-900 border-amber-200',
		danger: 'bg-red-50 text-red-900 border-red-200'
	};
	const icons = { info: Info, success: CircleCheck, warning: TriangleAlert, danger: CircleAlert };
	const Icon = $derived(icons[tone]);
</script>

<div class="flex gap-3 rounded-lg border p-3 text-sm {styles[tone]}" role={tone === 'danger' ? 'alert' : 'status'}>
	<Icon class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
	<div class="flex flex-col gap-0.5">
		{#if title}<p class="font-semibold">{title}</p>{/if}
		{#if children}<div>{@render children()}</div>{/if}
	</div>
</div>
