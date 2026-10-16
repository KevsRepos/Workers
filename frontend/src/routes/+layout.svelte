<script lang="ts">
import './layout.css';
import '@ionic/core';
import '@ionic/core/css/core.css';

// Basic CSS for apps built with Ionic
import '@ionic/core/css/normalize.css';
import '@ionic/core/css/structure.css';
import '@ionic/core/css/typography.css';

// Optional CSS utils that can be commented out
import '@ionic/core/css/padding.css';
import '@ionic/core/css/float-elements.css';
import '@ionic/core/css/text-alignment.css';
import '@ionic/core/css/text-transformation.css';
import '@ionic/core/css/flex-utils.css';
import '@ionic/core/css/display.css';

import { defineCustomElements } from '@ionic/core/loader';

import favicon from '$lib/assets/favicon.svg';
import { Ham, Menu } from '@lucide/svelte';
import { invalidateAll } from '$app/navigation';
import { auth } from '$lib/auth.svelte';
import PageHeadline from '$lib/components/PageHeadline.svelte';
import HamburgerMenu from '$lib/components/HamburgerMenu.svelte';
import { onMount } from 'svelte';

let { data, children } = $props();

onMount(() => {
	window.Ionic = {
		config: {
			mode: window.localStorage.getItem('ionicMode') || 'md'
		}
	}

    defineCustomElements(window);
});

// let style = $state(window.localStorage.getItem('ionicMode') || 'md');

const switchStyle = () => {
	console.log('passiert was?');

	let style = window.localStorage.getItem('ionicMode') || 'md';
	style = style === 'md' ? 'ios' : 'md';
	window.Ionic.config.mode = style;
	window.localStorage.setItem('ionicMode', style);

	
};

// Hydrate auth state from server data
$effect(() => {
    auth.hydrate(data.token);
});

let username = $state('');
let password = $state('');
let error = $state('');

let menuOpen = $state(false);

let menuBtn = $state<HTMLButtonElement>();

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

<ion-app>
	<!-- <HamburgerMenu bind:open={menuOpen} {menuBtn} /> -->

	<ion-header>
		<ion-toolbar>
			<ion-title>Workers</ion-title>
		</ion-toolbar>
	</ion-header>

	<ion-button onclick={switchStyle}>switch style</ion-button>


	<!-- <AppBar class="faboulus border-surface-200-800">
		<AppBar.Toolbar class="grid-cols-[auto_1fr_auto]">
			{#if data.loggedIn}
				<AppBar.Lead>
					<button bind:this={menuBtn} onclick={() => menuOpen = !menuOpen}><Menu /></button>
				</AppBar.Lead>
			{/if}
			
			<a href="/"><AppBar.Headline class="falcon-poster-bold text-3xl ">Workers</AppBar.Headline></a>
		</AppBar.Toolbar>
	</AppBar> -->

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
</ion-app>