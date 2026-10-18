<script lang="ts">
import { invalidateAll } from "$app/navigation";
import { swipeGesture } from "$lib/attachments/swipeGesture";
import { auth } from "$lib/auth.svelte";
import {
    BottleWine,
    ChevronRight,
    Clock,
    LogOut,
    NotebookPen,
    Settings,
    UserPen
} from "@lucide/svelte";

let { open = $bindable<boolean>(), menuBtn } = $props();

const menuItems = [
    { icon: NotebookPen, label: "Lieferscheine", href: "/delivery-note/list/1" },
    { icon: BottleWine, label: "Artikel", href: "/products/list/all/1" },
    { icon: UserPen, label: "Kunden", href: "/customers" },
    { icon: Clock, label: "Zeiterfassung", href: "/time-tracking" }
];

let nav = $state<HTMLElement | null>(null);

const handleClick = (event: MouseEvent) => {
    const target = event.target;

    if (
        nav &&
        target instanceof Node &&
        !nav.contains(target) &&
        menuBtn &&
        !menuBtn.contains(target)
    ) {
        open = false;
    }
};
</script>

<svelte:document onclick={handleClick} />

<nav
    bind:this={nav}
    {@attach swipeGesture({
        axis: "x",
        edge: "left",
        open,
        handle: () => nav,
        canOpen: true,
        canClose: true,
        onOpen: () => open = true,
        onClose: () => open = false
    })}
    aria-hidden={!open}
    inert={!open}
    class="pt-4 flex flex-col lg:w-3/12 md:w-4/12 w-9/12 h-screen fixed top-0 left-0 bg-surface-50-950 z-50 shadow-md [&>a]:border-surface-950"
    class:pointer-events-none={!open}
    class:initially-closed={!open}
    style:pointer-events={!open ? 'none' : 'auto'}
>
    {#each menuItems as item}
        <a
            href={item.href}
            onclick={() => open = false}
            class="px-2 py-4 flex justify-between items-center hover:bg-surface-100-900"
        >
            <div class="flex gap-2 items-center">
                <item.icon />
                {item.label}
            </div>
            <ChevronRight />
        </a>
    {/each}

    <a
        href="/settings/account"
        onclick={() => open = false}
        class="px-2 py-4 flex justify-between items-center border-t mt-8 hover:bg-surface-100-900"
    >
        <div class="flex gap-2 items-center">
            <Settings />
            Einstellungen
        </div>
    </a>

    <button
        onclick={async () => {
            await auth.logout();
            invalidateAll();
            open = false;
        }}
        class="px-2 py-4 flex gap-2 items-center hover:bg-surface-100-900"
    >
        <LogOut />
        Ausloggen
    </button>
</nav>

<style>
nav.initially-closed {
    translate: -100% 0;
}
</style>