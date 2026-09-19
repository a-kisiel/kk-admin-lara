<script module lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Plus } from 'lucide-svelte';
    import PieceCard from '@/components/PieceCard.svelte';
    import * as Select from "@/components/ui/select/index.js";
    import * as Pagination from "@/components/ui/pagination/index.js";
    
    const sortOptions = [
        {value: 'alphabetical', label: 'Alphabetical'},
        {value: 'latest', label: 'Latest First'},
        {value: 'first', label: 'Oldest First'}
    ];

    const binaryOptions = [
        {value: 'yes', label: 'Yes'},
        {value: 'no', label: 'No'}
    ];

    const props = $derived(page.props);

    const selectedMedium = $derived(props.media?.find((m: any) => m.id === +props.params.medium_id));
    const selectedSort = $derived(sortOptions.find(o => o.value === props.params.sort));
    const selectedActive = $derived(binaryOptions.find(o => o.value === props.params.is_active));

    function setParam(key: string, value: any) {
        const url = new URL(window.location.href);

        if (['sort', 'is_wallpaper', 'is_active'].includes(key))
            url.searchParams.delete('page');
        
        url.searchParams.set(key, value);
        window.history.pushState({}, '', url);
        
        window.location.reload();
    }

</script>

<AppHead title="Books" />

<div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
    <div
        class="relative min-h-screen flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"
    >
        <div class="list-top-bar">
            <div class="filter-section">
                <div class="filter-wrap">
                    <span>Medium</span>
                    <Select.Root
                        onValueChange={e => setParam('medium_id', e)}
                        bind:value={props.params.medium_id}
                        type="single"
                    >
                        <Select.Trigger class="w-[160px]">{selectedMedium?.title}</Select.Trigger>
                        <Select.Content>
                            <Select.Item value="">&nbsp;</Select.Item>
                            {#each props.media as option}
                            <Select.Item value={option.id} >{option.title}</Select.Item>
                            {/each}
                        </Select.Content>
                    </Select.Root>
                </div>
                <div class="filter-wrap">
                    <span>Sort</span>
                    <Select.Root
                        onValueChange={e => setParam('sort', e)}
                        bind:value={props.params.sort}
                        type="single"
                    >
                        <Select.Trigger class="w-[120px]">{selectedSort?.label}</Select.Trigger>
                        <Select.Content>
                        {#each sortOptions as option}
                            <Select.Item value={option.value} >{option.label}</Select.Item>
                        {/each}
                        </Select.Content>
                    </Select.Root>
                </div>
                <div class="filter-wrap">
                    <span>Active</span>
                    <Select.Root
                        onValueChange={e => setParam('is_active', e)}
                        bind:value={props.params.is_active}
                        type="single"
                    >
                        <Select.Trigger class="w-[60px]">{selectedActive?.label}</Select.Trigger>
                        <Select.Content>
                            <Select.Item value="">&nbsp;</Select.Item>
                        {#each binaryOptions as option}
                            <Select.Item value={option.value}>{option.label}</Select.Item>
                        {/each}
                        </Select.Content>
                    </Select.Root>
                </div>
            </div>
            <a href="/books/add" class="list-add">
                Add Book <Plus />
            </a>
        </div>
        <div class="list piece-list">
            {#each props.books as book}
            <PieceCard
                piece={book}
                type='book'
            />
            {:else}
            <div class="list-else">No books yet.</div>
            {/each}
        </div>
        {#if props.books?.length > 0}
        <div class="total-row">Total: {props.total_books}</div>
        {/if}
        <div class="list-pagination">
            <Pagination.Root count={props.total_books} perPage={20}>
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
                            onclick={(e) => {e.preventDefault(); setParam('page', page.value);}}
                            {page}
                            isActive={+props.params.page === page.value}
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
