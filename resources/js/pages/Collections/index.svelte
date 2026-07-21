<script module lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Plus } from 'lucide-svelte';
    
    const props = $derived(page.props);
</script>

<AppHead title="Collections" />

<div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
    <div
        class="relative min-h-screen flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
    >
        <div class="list-top-bar">
            <a href="/collections/add" class="list-add">
                Add Collection <Plus />
            </a>
        </div>
        <div class="list collection-list">
            {#each props.collections as collection}
            <div class="item">
                <a href="{`/collections/${collection.id}`}">{collection.title}</a>
            </div>
            {:else}
            <div class="list-else">No collections yet.</div>
            {/each}
        </div>
        
        <!-- <List list=props.pieces /> -->
    </div>
</div>
