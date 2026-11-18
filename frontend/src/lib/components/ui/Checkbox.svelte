<script lang="ts">
	import type { HTMLInputAttributes } from 'svelte/elements';

	interface Props extends Omit<HTMLInputAttributes, 'type' | 'checked'> {
		id: string;
		label: string;
		description?: string;
		checked?: boolean;
	}

	let { id, label, description, checked = $bindable(false), class: className = '', ...rest }: Props = $props();
</script>

<label for={id} class="flex cursor-pointer items-start gap-3 {className}">
	<input
		{id}
		name={id}
		type="checkbox"
		bind:checked
		class="mt-0.5 size-5 shrink-0 rounded border-stone-300 accent-brand-700"
		{...rest}
	/>
	<span class="flex flex-col">
		<span class="text-sm font-medium text-stone-800">{label}</span>
		{#if description}<span class="text-xs text-stone-500">{description}</span>{/if}
	</span>
</label>
