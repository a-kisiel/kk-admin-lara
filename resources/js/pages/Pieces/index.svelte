<script module lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Plus } from 'lucide-svelte';
    import PieceCard from '@/components/PieceCard.svelte';
    import * as Select from "@/components/ui/select/index.js";
    import * as Pagination from "@/components/ui/pagination/index.js";
    
    const props = $derived(page.props);

    const sortOptions = [
        {value: 'alphabetical', label: 'Alphabetical'},
        {value: 'recent', label: 'Recent'}
    ];

    function setSort(v: string) {
        const params = new URLSearchParams();
        params.set('sort', `${v}`);
        params.delete('page');
        window.history.replaceState(null, '', '?' + params.toString());
        window.location.reload();
    }

    function setCurrentPage(p: number) {
        const params = new URLSearchParams();
        params.set('page', `${p}`);
        window.history.replaceState(null, '', '?' + params.toString());
        window.location.reload();
    }

</script>

<AppHead title="Pieces" />

<div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
    <div
        class="relative min-h-screen flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
    >
        <div class="list-top-bar">
            <div class="filter-section">
                <Select.Root onValueChange={e => setSort(e)} bind:value={props.sort} type="single">
                    <Select.Trigger class="w-[180px]">{props.sort}</Select.Trigger>
                    <Select.Content>
                    {#each sortOptions as option}
                    <Select.Item value={option.value} >{option.label}</Select.Item>
                    {/each}
                    </Select.Content>
                </Select.Root>
            </div>
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
        <div class="list-pagination">
            <Pagination.Root count={props.total_pieces} perPage={20}>
            {#snippet children({ pages })}
            <Pagination.Content>
                <Pagination.Item>
                    <Pagination.Previous />
                </Pagination.Item>
                {#each pages as page (page.key)}
                    {#if page.type === "ellipsis"}
                    <Pagination.Item>
                        <Pagination.Ellipsis />
                    </Pagination.Item>
                    {:else}
                    <Pagination.Item>
                        <Pagination.Link
                            onclick={(e) => {e.preventDefault(); setCurrentPage(page.value);}}
                            {page}
                            isActive={+props.page === page.value}
                        >
                        {page.value}
                        </Pagination.Link>
                    </Pagination.Item>
                    {/if}
                {/each}
                <Pagination.Item>
                    <Pagination.Ellipsis />
                </Pagination.Item>
                <Pagination.Item>
                    <Pagination.Next />
                </Pagination.Item>
            </Pagination.Content>
            {/snippet}
            </Pagination.Root>
        </div>
    </div>
</div>
