<script lang="ts">
import { invalidateAll } from "$app/navigation";
import { auth } from "$lib/auth.svelte";
import { BottleWine, ChevronRight, Cog, LogOut, NotebookPen, Settings, UserPen } from "@lucide/svelte";

let { open = $bindable<boolean>(), menuBtn } = $props();

const menuItems = [
    { icon: NotebookPen, label: 'Lieferscheine', href: '/delivery-note/list/1' },
    { icon: BottleWine, label: 'Artikel', href: '/products/list/all/1' },
    { icon: UserPen, label: 'Kunden', href: '/customers' },
];

let nav = $state<HTMLElement>();

const handleClick = (event: MouseEvent) => {
    if (nav && !nav.contains(event.target as Node) && menuBtn && !menuBtn.contains(event.target as Node)) {
        open = false;
    }
}
</script>

<svelte:document on:click={handleClick} />

<nav bind:this={nav} class="{open ? 'visible slide-in' : 'slide-out invisible'} pt-4 flex flex-col w-9/12 h-screen fixed top-0 bg-surface-100-900 z-50 shadow-md [&>a]:border-surface-950">
    {#each menuItems as item}
        <a onclick={() => open = false} class="px-2 py-4 flex justify-between items-center" href={item.href}>
            <div class="flex gap-2 items-center">
                <item.icon />
                {item.label}
            </div>
            <ChevronRight />
        </a>
    {/each}

    <a href="/settings" onclick={() => open = false} class="px-2 py-4  flex justify-between items-center border-t mt-8">
        <div class="flex gap-2 items-center">
            <Settings />
            Einstellungen
        </div>
    </a>
    <button onclick={async () => {await auth.logout(); invalidateAll(); open = false;}} class="px-2 py-4 flex gap-2 items-center">
        <LogOut />
        Ausloggen
    </button>
</nav>

<style>
.slide-in {
    animation: slideIn 0.3s forwards;
}
.slide-out {
    animation: slideOut 0.3s forwards;
}
@keyframes slideIn {
    from { left: -100%; }
    to { left: 0; }
}
@keyframes slideOut {
    from { left: 0; }
    to { left: -100%; }
}
</style>