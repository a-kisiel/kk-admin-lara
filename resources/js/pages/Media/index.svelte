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
        <div style="display: flex;">
            <div style="margin-left: 10px;">
                <h2>Main Media</h2>
                <div class="list">
                    {#each props.media as medium}
                    <div class="item">
                        <a href="{`/media/${medium.id}`}">{medium.title}</a> ({medium.pieces_count})
                    </div>
                    {:else}
                    <div class="list-else">No media yet.</div>
                    {/each}
                </div>
            </div>
            <div>
                <h2>Support Media</h2>
                <div class="list">
                    {#each props.support_media as medium}
                    <div class="item">
                        <a href="{`/media/${medium.id}`}">{medium.title}</a> ({medium.support_pieces_count})
                    </div>
                    {:else}
                    <div class="list-else">No support media yet.</div>
                    {/each}
                </div>
            </div>
        </div>
    </div>
</div>
