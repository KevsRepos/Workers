<script lang="ts">
	let { size = 20, label = 'Lädt…' }: { size?: number; label?: string } = $props();
</script>

<svg class="animate-spin" width={size} height={size} viewBox="0 0 24 24" fill="none" role="status" aria-label={label}>
	<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="4" />
	<path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4" stroke-linecap="round" />
</svg>
