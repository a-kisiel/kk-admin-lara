<script module lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Plus } from 'lucide-svelte';
    
    const props = $derived(page.props);
</script>

<AppHead title="Media" />

<div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
    <div
        class="relative min-h-screen flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
    >
        <div class="list-top-bar">
            <a href="/media/add" class="list-add">
                Add Medium <Plus />
            </a>
        </div>
        <div class="list">
            {#each props.media as medium}
            <div class="item">
                <a href="{`/media/${medium.id}`}">{medium.title}</a>
            </div>
            {:else}
            <div class="list-else">No media yet.</div>
            {/each}
        </div>
        
        <!-- <List list=props.pieces /> -->
    </div>
</div>
