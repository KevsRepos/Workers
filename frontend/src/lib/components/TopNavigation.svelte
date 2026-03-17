<script lang="ts">
import { Navigation } from '@skeletonlabs/skeleton-svelte';

let { children } = $props();
</script>

<Navigation class="mb-3 no-scrollbar [&_select]:border [&_select]:border-surface-200-800 [&_select]:rounded [&_select]:p-2">
    <Navigation.Menu class="overflow-x-auto">
        {@render children()}
    </Navigation.Menu>
</Navigation>