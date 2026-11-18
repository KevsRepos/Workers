<script lang="ts">
	import type { Snippet } from 'svelte';
	import { page } from '$app/state';
	import { enhance } from '$app/forms';
	import {
		Boxes,
		ClipboardList,
		Clock,
		FileStack,
		House,
		LogOut,
		Menu,
		Package,
		PiggyBank,
		ScrollText,
		Settings,
		Tent,
		Users,
		UserCog,
		X
	} from '@lucide/svelte';
	import type { SessionUser } from '$lib/session';

	let { user, children }: { user: SessionUser; children: Snippet } = $props();

	let mobileOpen = $state(false);

	const main = [
		{ href: '/', label: 'Übersicht', icon: House, exact: true },
		{ href: '/delivery-notes', label: 'Lieferscheine', icon: ClipboardList },
		{ href: '/customers', label: 'Kunden', icon: Users },
		{ href: '/products', label: 'Artikel', icon: Package, exclude: ['/products/unions', '/products/deposits'] },
		{ href: '/order-presets', label: 'Bestellvorlagen', icon: FileStack },
		{ href: '/time-tracking', label: 'Zeiterfassung', icon: Clock },
		{ href: '/tent-planner', label: 'Zeltplaner', icon: Tent }
	];

	const admin = [
		{ href: '/products/unions', label: 'Artikelverbünde', icon: Boxes },
		{ href: '/products/deposits', label: 'Pfand', icon: PiggyBank },
		{ href: '/admin/accounts', label: 'Benutzer', icon: UserCog },
		{ href: '/admin/audit-log', label: 'Audit-Log', icon: ScrollText }
	];

	const isActive = (item: { href: string; exact?: boolean; exclude?: string[] }) => {
		const path = page.url.pathname;
		if (item.exact) return path === item.href;
		if (item.exclude?.some((e) => path.startsWith(e))) return false;
		return path === item.href || path.startsWith(item.href + '/');
	};

	$effect(() => {
		// close the mobile drawer after navigation
		void page.url.pathname;
		mobileOpen = false;
	});
</script>

{#snippet navigation()}
	<nav class="flex flex-1 flex-col gap-6 overflow-y-auto px-3 py-4" aria-label="Hauptnavigation">
		<ul class="flex flex-col gap-0.5">
			{#each main as item (item.href)}
				<li>
					<a
						href={item.href}
						class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors {isActive(item)
							? 'bg-brand-800 text-white'
							: 'text-brand-100 hover:bg-brand-800/60 hover:text-white'}"
						aria-current={isActive(item) ? 'page' : undefined}
					>
						<item.icon class="size-4.5" aria-hidden="true" />
						{item.label}
					</a>
				</li>
			{/each}
		</ul>
		{#if user.isAdmin}
			<div>
				<p class="px-3 pb-1 text-xs font-semibold tracking-wider text-brand-300 uppercase">Verwaltung</p>
				<ul class="flex flex-col gap-0.5">
					{#each admin as item (item.href)}
						<li>
							<a
								href={item.href}
								class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors {isActive(
									item
								)
									? 'bg-brand-800 text-white'
									: 'text-brand-100 hover:bg-brand-800/60 hover:text-white'}"
								aria-current={isActive(item) ? 'page' : undefined}
							>
								<item.icon class="size-4.5" aria-hidden="true" />
								{item.label}
							</a>
						</li>
					{/each}
				</ul>
			</div>
		{/if}
	</nav>
	<div class="border-t border-brand-800 p-3">
		<div class="mb-2 px-3">
			<p class="truncate text-sm font-medium text-white">{user.name}</p>
			<p class="truncate text-xs text-brand-300">{user.email}</p>
		</div>
		<a
			href="/settings"
			class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-brand-100 hover:bg-brand-800/60 hover:text-white"
		>
			<Settings class="size-4.5" aria-hidden="true" /> Einstellungen
		</a>
		<form method="POST" action="/logout" use:enhance>
			<button
				type="submit"
				class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm text-brand-100 hover:bg-brand-800/60 hover:text-white"
			>
				<LogOut class="size-4.5" aria-hidden="true" /> Abmelden
			</button>
		</form>
	</div>
{/snippet}

<a
	href="#main"
	class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded focus:bg-white focus:p-2"
	>Zum Inhalt springen</a
>

<div class="flex min-h-dvh">
	<aside class="no-print fixed inset-y-0 left-0 hidden w-64 flex-col bg-brand-950 lg:flex">
		<a href="/" class="flex h-16 items-center px-6 font-brand text-3xl text-white">Workers</a>
		{@render navigation()}
	</aside>

	{#if mobileOpen}
		<div class="no-print fixed inset-0 z-40 lg:hidden" role="dialog" aria-modal="true" aria-label="Navigation">
			<button
				class="absolute inset-0 bg-stone-950/50"
				aria-label="Navigation schließen"
				onclick={() => (mobileOpen = false)}
			></button>
			<aside class="relative flex h-full w-72 max-w-[85vw] flex-col bg-brand-950 pt-[env(safe-area-inset-top)]">
				<div class="flex h-16 items-center justify-between px-5">
					<span class="font-brand text-3xl text-white">Workers</span>
					<button
						class="rounded-lg p-2 text-brand-100 hover:bg-brand-800"
						aria-label="Navigation schließen"
						onclick={() => (mobileOpen = false)}
					>
						<X class="size-5" />
					</button>
				</div>
				{@render navigation()}
			</aside>
		</div>
	{/if}

	<div class="flex min-w-0 flex-1 flex-col lg:pl-64">
		<header
			class="no-print sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-stone-200 bg-white/90 px-4 pt-[env(safe-area-inset-top)] backdrop-blur lg:hidden"
		>
			<button
				class="-ml-2 rounded-lg p-2 text-stone-700 hover:bg-stone-100"
				aria-label="Navigation öffnen"
				aria-expanded={mobileOpen}
				onclick={() => (mobileOpen = true)}
			>
				<Menu class="size-5" />
			</button>
			<a href="/" class="font-brand text-2xl text-brand-900">Workers</a>
		</header>
		<main
			id="main"
			class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8 print:max-w-none print:p-0"
		>
			{@render children()}
		</main>
	</div>
</div>
