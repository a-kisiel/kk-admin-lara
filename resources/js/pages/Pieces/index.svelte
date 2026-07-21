<script module lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import List from '@/components/List.svelte';
    import { Plus } from 'lucide-svelte';
    import PieceCard from '@/components/PieceCard.svelte';
    
    const props = $derived(page.props);
</script>

<AppHead title="Pieces" />

<div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
    <div
        class="relative min-h-screen flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
    >
        <div class="list-top-bar">
            <a href="/pieces/add" class="list-add">
                Add Piece <Plus />
            </a>
        </div>
        <div class="list piece-list">
            {#each props.pieces as piece}
            <PieceCard
                piece={piece}
            />
            {:else}
            <div class="list-else">No pieces yet.</div>
            {/each}
        </div>
        
        <!-- <List list=props.pieces /> -->
    </div>
</div>
