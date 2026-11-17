<script lang="ts">
	import type { Snippet } from 'svelte';
	import type { HTMLButtonAttributes } from 'svelte/elements';
	import Spinner from './Spinner.svelte';

	type Variant = 'primary' | 'secondary' | 'ghost' | 'danger';
	type Size = 'sm' | 'md' | 'lg';

	interface Props extends HTMLButtonAttributes {
		variant?: Variant;
		size?: Size;
		loading?: boolean;
		href?: string;
		children: Snippet;
	}

	let {
		variant = 'primary',
		size = 'md',
		loading = false,
		href,
		children,
		class: className = '',
		disabled,
		type = 'button',
		...rest
	}: Props = $props();

	const variants: Record<Variant, string> = {
		primary: 'bg-brand-700 text-white hover:bg-brand-800 active:bg-brand-900 shadow-xs',
		secondary: 'bg-white text-stone-800 border border-stone-300 hover:bg-stone-50 active:bg-stone-100 shadow-xs',
		ghost: 'text-stone-700 hover:bg-stone-100 active:bg-stone-200',
		danger: 'bg-red-600 text-white hover:bg-red-700 active:bg-red-800 shadow-xs'
	};
	const sizes: Record<Size, string> = {
		sm: 'h-8 px-3 text-sm gap-1.5',
		md: 'h-10 px-4 text-sm gap-2',
		lg: 'h-12 px-5 text-base gap-2'
	};

	const classes = $derived(
		`inline-flex items-center justify-center rounded-lg font-medium transition-colors disabled:pointer-events-none disabled:opacity-50 whitespace-nowrap ${variants[variant]} ${sizes[size]} ${className}`
	);
</script>

{#if href && !disabled}
	<a {href} class={classes}>{@render children()}</a>
{:else}
	<button {type} class={classes} disabled={disabled || loading} aria-busy={loading} {...rest}>
		{#if loading}<Spinner size={16} />{/if}
		{@render children()}
	</button>
{/if}
