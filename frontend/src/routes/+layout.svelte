<script lang="ts">
import './layout.css';
import favicon from '$lib/assets/favicon.svg';
import { Menu } from '@lucide/svelte';
import { AppBar } from '@skeletonlabs/skeleton-svelte';
import { invalidateAll } from '$app/navigation';
import { auth } from '$lib/auth.svelte';

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


{#if !data.loggedIn}
	<form class="flex flex-col gap-2 p-4" onsubmit={handleLogin}>
		<input placeholder="username" bind:value={username} />
		<input placeholder="password" bind:value={password} />
		{#if error}<p class="text-red-500">{error}</p>{/if}
		<button>Login</button>
	</form>
{:else}
	<AppBar>
		<AppBar.Toolbar class="grid-cols-[auto_1fr_auto]">
			<button><Menu /></button>
			
			<a href="/"><AppBar.Headline class="falcon-poster-bold text-3xl ">Workers</AppBar.Headline></a>
		</AppBar.Toolbar>
	</AppBar>

    {@render children()}
{/if}
