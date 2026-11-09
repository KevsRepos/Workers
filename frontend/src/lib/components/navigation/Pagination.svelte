<script lang="ts">
import { goto } from "$app/navigation";
import { ChevronLeft, ChevronRight } from "@lucide/svelte";
import { Pagination } from "@skeletonlabs/skeleton-svelte";

let { data, href } = $props();

const changePage = (page: number) => {
    goto(`${href}/${page}`);
}
</script>

<Pagination
    count={data.total}
    defaultPageSize={data.limit}
    page={data.page}
    boundaryCount={1}
    siblingCount={1}
    type="link"
    onPageChange={(event) => changePage(event.page)}
>
    <Pagination.PrevTrigger>
        <ChevronLeft />
    </Pagination.PrevTrigger>
    <Pagination.Context>
        {#snippet children(pagination)}
            {#each pagination().pages as page, index (page)}
                {#if page.type === 'page'}
                    <Pagination.Item {...page} href="{href}/{page.value}">
                        {page.value}
                    </Pagination.Item>
                {/if}
            {/each}
        {/snippet}
    </Pagination.Context>
    <Pagination.NextTrigger>
        <ChevronRight />
    </Pagination.NextTrigger>
</Pagination>