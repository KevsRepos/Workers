<script lang="ts" generics="T extends string | number | null">
	import type { HTMLSelectAttributes } from 'svelte/elements';
	import Field from './Field.svelte';

	interface Option {
		value: T;
		label: string;
	}

	interface Props extends Omit<HTMLSelectAttributes, 'value'> {
		id: string;
		label: string;
		value?: T;
		options: Option[];
		error?: string | null;
		hint?: string;
		class?: string;
	}

	let { id, label, value = $bindable(), options, error = null, hint, class: className = '', ...rest }: Props = $props();
</script>

<Field {id} {label} {error} {hint} class={className}>
	<select {id} name={id} class="field-input" bind:value aria-invalid={error ? 'true' : undefined} {...rest}>
		{#each options as option (option.value)}
			<option value={option.value}>{option.label}</option>
		{/each}
	</select>
</Field>
