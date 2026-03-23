<script lang="ts">
import PageHeadline from "$lib/components/PageHeadline.svelte";
import TopNavigation from "$lib/components/TopNavigation.svelte";
import { CirclePlus, Pen } from "@lucide/svelte";
import { Navigation } from "@skeletonlabs/skeleton-svelte";

let { data } = $props();
</script>

<TopNavigation>
    <Navigation.TriggerAnchor href="/products/unions/create">
        <CirclePlus />
        <Navigation.TriggerText>Anlegen</Navigation.TriggerText>
    </Navigation.TriggerAnchor>
</TopNavigation>

<PageHeadline>Einheiten</PageHeadline>

<main>
    <div class="px-4 pb-2 font-bold">{data.productUnions.length} Einheiten gesamt</div>

    <div class="flex flex-col">
        {#each data.productUnions as union}
            <div class="flex w-full not-last:border-b py-2">
                <a class="flex items-center py-2 px-6" href="/products/unions/{union.id}">
                    <Pen />
                </a>
                <div class="flex flex-col py-2">
                    <div class="font-bold">{union.name}</div>
                </div>
            </div>
        {/each}
    </div>
</main>
