<script lang="ts">
	import type { Snippet } from 'svelte';

	interface Props {
		id: string;
		label: string;
		error?: string | null;
		hint?: string;
		required?: boolean;
		class?: string;
		children: Snippet;
	}

	let { id, label, error = null, hint, required = false, class: className = '', children }: Props = $props();
</script>

<div class="flex flex-col gap-1.5 {className}">
	<label for={id} class="text-sm font-medium text-stone-700">
		{label}{#if required}<span class="text-red-600" aria-hidden="true"> *</span>{/if}
	</label>
	{@render children()}
	{#if error}
		<p id="{id}-error" class="text-sm text-red-600" role="alert">{error}</p>
	{:else if hint}
		<p id="{id}-hint" class="text-xs text-stone-500">{hint}</p>
	{/if}
</div>
