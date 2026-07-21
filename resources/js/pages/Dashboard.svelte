<script module lang="ts">
    import { page } from '@inertiajs/svelte';

    let loadingBackup = $state(false);
    let regeneratingBackup = $state(false);
    const user = $derived(page.props.auth.user);

    async function downloadBackup() {
        loadingBackup = true;
        const res = await fetch('/api/get-backup');
        loadingBackup = false;
    }

    async function regenerateBackup() {
        regeneratingBackup = true;
        const res = await fetch('/api/regenerate-backup');
        regeneratingBackup = false;
    }
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Button from '@/components/ui/button/button.svelte';
    import {
        Item,
        ItemActions,
        ItemContent,
        ItemDescription,
        ItemGroup,
        ItemTitle,
    } from "@/components/ui/item"
    import Separator from '@/components/ui/separator/separator.svelte';
    import Spinner from '@/components/ui/spinner/Spinner.svelte';
</script>

<AppHead title="Dashboard" />

<div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
    <div>Hey {user.name}</div>
    <Separator />
    <div>
        <h2>Admin Actions</h2>
    </div>
    <!-- <Separator /> -->
    <ItemGroup className="max-w-sm">
        <Item variant="outline">
            <ItemContent>
                <ItemTitle>Get DB Backup</ItemTitle>
                <ItemDescription>
                    Get the current backup file.
                </ItemDescription>
            </ItemContent>
            <ItemActions>
                    <Button onclick={downloadBackup} variant="outline" size="sm">
                    Download
                    {#if loadingBackup}
                    <Spinner />
                    {/if}
                    </Button>
            </ItemActions>
        </Item>
        <Item variant="outline">
            <ItemContent>
                <ItemTitle>Manual DB Backup</ItemTitle>
                <ItemDescription>
                    Immediately regenerate the backup file on S3.
                    <br/>
                    The system automatically performs this action at the end of every week (last backup: ).
                </ItemDescription>
            </ItemContent>
            <ItemActions>
                <Button onclick={regenerateBackup} variant="outline" size="sm">
                Regenerate
                {#if regeneratingBackup}
                <Spinner />
                {/if}
                </Button>
            </ItemActions>
        </Item>
    </ItemGroup>
</div>

