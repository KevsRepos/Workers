<script lang="ts">
	import type { Snippet } from 'svelte';

	type Tone = 'neutral' | 'brand' | 'success' | 'warning' | 'danger' | 'info';

	let {
		tone = 'neutral',
		children,
		class: className = ''
	}: { tone?: Tone; children: Snippet; class?: string } = $props();

	const tones: Record<Tone, string> = {
		neutral: 'bg-stone-100 text-stone-700 ring-stone-200',
		brand: 'bg-brand-50 text-brand-800 ring-brand-200',
		success: 'bg-emerald-50 text-emerald-800 ring-emerald-200',
		warning: 'bg-amber-50 text-amber-800 ring-amber-200',
		danger: 'bg-red-50 text-red-800 ring-red-200',
		info: 'bg-sky-50 text-sky-800 ring-sky-200'
	};
</script>

<span
	class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {tones[
		tone
	]} {className}"
>
	{@render children()}
</span>
