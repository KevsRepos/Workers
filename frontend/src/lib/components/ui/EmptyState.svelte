<script lang="ts">
	import type { Snippet } from 'svelte';
	import { Inbox } from '@lucide/svelte';

	let { title, description, action }: { title: string; description?: string; action?: Snippet } = $props();
</script>

<div class="flex flex-col items-center justify-center gap-2 px-6 py-12 text-center">
	<div class="rounded-full bg-stone-100 p-3 text-stone-500"><Inbox class="size-6" aria-hidden="true" /></div>
	<p class="font-medium text-stone-800">{title}</p>
	{#if description}<p class="max-w-sm text-sm text-stone-500">{description}</p>{/if}
	{#if action}<div class="mt-2">{@render action()}</div>{/if}
</div>
