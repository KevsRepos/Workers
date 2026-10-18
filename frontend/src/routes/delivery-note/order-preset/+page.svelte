<script lang="ts">
import PageHeadline from "$lib/components/PageHeadline.svelte";
import TopNavigation from "$lib/components/TopNavigation.svelte";
import { Pen, StickyNotePlus } from "@lucide/svelte";
import { Navigation } from "@skeletonlabs/skeleton-svelte";

let { data } = $props();
</script>

<TopNavigation>
    <Navigation.TriggerAnchor href="/delivery-note/order-preset/create">
        <StickyNotePlus />
        <Navigation.TriggerText>Erstellen</Navigation.TriggerText>
    </Navigation.TriggerAnchor>
</TopNavigation>

<PageHeadline>Bestellvorlagen</PageHeadline>

<main>
    <div class="px-4 pb-2 font-bold">{data.orderPresets.length} Bestellvorlagen gesamt</div>

    {#if data.orderPresets.length === 0}
        <p class="px-4 py-2">Es gibt noch keine Bestellvorlagen.</p>
    {:else}
        <div class="flex flex-col">
            {#each data.orderPresets as orderPreset (orderPreset.id)}
                <div class="flex w-full not-last:border-b py-2">
                    <a
                        class="flex items-center py-2 px-6"
                        href="/delivery-note/order-preset/{orderPreset.id}"
                        aria-label="Bestellvorlage {orderPreset.name} bearbeiten"
                    >
                        <Pen />
                    </a>
                    <div class="flex flex-col py-2">
                        {orderPreset.name}
                    </div>
                </div>
            {/each}
        </div>
    {/if}
</main>