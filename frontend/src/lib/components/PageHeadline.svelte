<script lang="ts">
const { children } = $props();
</script>

<h1 class="mb-2 pt-2 pb-2 px-4 border-surface-200-800 text-xs w-fit bg-surface-100-900 font-bold">{@render children()}</h1>

<style>
h1 {
    padding-right: 24px;
    clip-path: polygon(
        0 0,
        100% 0,
        85% 100%,
        0 100%
    );
}
</style>