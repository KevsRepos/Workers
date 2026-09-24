<script lang="ts">
import { Navigation } from '@skeletonlabs/skeleton-svelte';

let { children } = $props();
</script>

<Navigation class="no-scrollbar border-b border-surface-200-800 [&_select]:border [&_select]:border-surface-200-800 bg-surface-50-950 [&_select]:rounded [&_select]:p-2">
    <Navigation.Menu class="overflow-x-auto">
        {@render children()}
    </Navigation.Menu>
</Navigation>