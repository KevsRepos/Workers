<script lang="ts">
	import type { Snippet } from 'svelte';
	import { ArrowLeft } from '@lucide/svelte';

	interface Props {
		title: string;
		subtitle?: string;
		back?: string;
		actions?: Snippet;
		meta?: Snippet;
	}

	let { title, subtitle, back, actions, meta }: Props = $props();
</script>

<header class="no-print mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
	<div class="flex min-w-0 items-start gap-3">
		{#if back}
			<a
				href={back}
				class="mt-0.5 rounded-lg p-1.5 text-stone-500 hover:bg-stone-200/60 hover:text-stone-900"
				aria-label="Zurück"
			>
				<ArrowLeft class="size-5" />
			</a>
		{/if}
		<div class="min-w-0">
			<h1 class="truncate text-2xl sm:text-3xl">{title}</h1>
			{#if subtitle}<p class="mt-1 text-sm text-stone-500">{subtitle}</p>{/if}
			{#if meta}<div class="mt-2 flex flex-wrap items-center gap-2">{@render meta()}</div>{/if}
		</div>
	</div>
	{#if actions}
		<div class="flex flex-wrap gap-2">{@render actions()}</div>
	{/if}
</header>
