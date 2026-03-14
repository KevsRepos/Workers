<script lang="ts">
import './layout.css';
import favicon from '$lib/assets/favicon.svg';
import { Menu } from '@lucide/svelte';
import { AppBar } from '@skeletonlabs/skeleton-svelte';
import { invalidateAll } from '$app/navigation';
import { auth } from '$lib/auth.svelte';
import PageHeadline from '$lib/components/PageHeadline.svelte';

let { data, children } = $props();

// Hydrate auth state from server data
$effect(() => {
    auth.hydrate(data.token);
});

let username = $state('');
let password = $state('');
let error = $state('');

async function handleLogin(e: Event) {
    e.preventDefault();
    error = '';
    
    const result = await auth.login(username, password);
    
    if (!result.success) {
        error = result.error || 'Login fehlgeschlagen';
        return;
    }

    await invalidateAll();
}
</script>

<svelte:head><link rel="icon" href={favicon} /></svelte:head>

<AppBar>
	<AppBar.Toolbar class="grid-cols-[auto_1fr_auto]">
		{#if data.loggedIn}
			<button><Menu /></button>
		{/if}
		
		<a href="/"><AppBar.Headline class="falcon-poster-bold text-3xl ">Workers</AppBar.Headline></a>
	</AppBar.Toolbar>
</AppBar>
{#if !data.loggedIn}
	<PageHeadline>Login</PageHeadline>

	<form class="flex flex-col gap-2 p-4 md:max-w-200 mx-auto" onsubmit={handleLogin}>
		<input class="input bg-surface-200-800" placeholder="E-Mail" bind:value={username} />
		<input class="input bg-surface-200-800" placeholder="Passwort" type="password" bind:value={password} />
		{#if error}<p class="text-red-500">{error}</p>{/if}
		<button class="btn preset-filled">Login</button>
	</form>
{:else}
    {@render children()}
{/if}
