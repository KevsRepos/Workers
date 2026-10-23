<script lang="ts">
import './layout.css';
import favicon from '$lib/assets/favicon.svg';
import { ArrowLeft, Menu } from '@lucide/svelte';
import { AppBar } from '@skeletonlabs/skeleton-svelte';
import { invalidateAll } from '$app/navigation';
import { auth } from '$lib/auth.svelte';
import PageHeadline from '$lib/components/PageHeadline.svelte';
import HamburgerMenu from '$lib/components/HamburgerMenu.svelte';
import { page } from '$app/state';

let { data, children } = $props();

// Hydrate auth state from server data
$effect(() => {
    auth.hydrate(data.token);
});

let username = $state('');
let password = $state('');
let error = $state('');

let menuOpen = $state(false);

let menuBtn = $state<HTMLButtonElement>();
let previousScrollY = 0;

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

const slideHeaderIntoView = () => {
	const currentScrollY = window.scrollY;
	const header = document.querySelector('.app-header') as HTMLElement;
	if (!header) return;

	if (currentScrollY <= 0) {
		header.style.transform = 'translateY(0)';
		previousScrollY = currentScrollY;
		return;
	}

	const scrollDelta = currentScrollY - previousScrollY;
	if (Math.abs(scrollDelta) >= 4) {
		header.style.transform = scrollDelta > 0 ? 'translateY(-100%)' : 'translateY(0)';
		previousScrollY = currentScrollY;
	}
};
</script>

<svelte:head><link rel="icon" href={favicon} /></svelte:head>

<svelte:document onscroll={slideHeaderIntoView} />

{#if page.url.pathname === '/'}
	<HamburgerMenu bind:open={menuOpen} {menuBtn} />
{/if}

<AppBar class="app-header bg-white py-0 ps-0 sticky top-0 z-40">
	<AppBar.Toolbar class="flex gap-0 w-fit">
		{#if data.loggedIn}
			{#if page.url.pathname === '/'}
				<button class="py-6 px-4" bind:this={menuBtn} onclick={() => menuOpen = true}><Menu /></button>
			{:else}
				<button class="py-6 px-4" onclick={() => window.history.back()}>
					<ArrowLeft />
				</button>
			{/if}
		{/if}
		
		<a href="/"><AppBar.Headline class="falcon-poster-bold text-3xl">Workers</AppBar.Headline></a>
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

<footer class="bottom-0 w-full bg-gray-200 text-center p-2"></footer>