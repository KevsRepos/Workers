<script lang="ts">
let { children } = $props();
</script>

<ion-tab-bar class="justify-start border-b border-gray-100">
    {@render children()}
</ion-tab-bar>

<!-- <Navigation class="no-scrollbar border-surface-200-800 [&_select]:border [&_select]:border-surface-200-800 bg-surface-50-950 [&_select]:rounded [&_select]:p-2">
    <Navigation.Menu class="overflow-x-auto">
        {@render children()}
    </Navigation.Menu>
</Navigation> -->