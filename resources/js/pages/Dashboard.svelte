<script module lang="ts">
    import { page } from '@inertiajs/svelte';

    let autoUpdate = $state(true);

    let generatingJSON = $state(false);
    let pushingChanges = $state(false);
    let revertingChanges = $state(false);
    let confirmRebuild = $state(false);
    let rebuildingDB = $state(false);
    let pruningS3 = $state(false);
    let generatingThumbnails = $state(false);

    let db_file = $state((new DataTransfer()).files);

    const user = $derived(page.props.auth.user);

    async function pushChanges() {
        pushingChanges = true;
        const res = await fetch('/api/push-changes');
        pushingChanges = false;
    }

    async function revertChanges() {
        revertingChanges = true;
        const res = await fetch('/api/revert-changes');
        revertingChanges = false;
    }

    async function generateJSON() {
        generatingJSON = true;
        const res = await fetch('/api/generate-json');
        generatingJSON = false;
    }

    async function rebuildDB() {
        const file = db_file[0];
        
        if (!file || file.type !== 'application/json') {
            toast.error("Invalid file: make sure it's a valid JSON file.");
            return;
        }

        if (!confirmRebuild) {
            confirmRebuild = true;
            return;
        }

        rebuildingDB = true;

        const formData = new FormData();
        formData.append('db_file', file);

        const res = await fetch('/api/rebuild-db', {
            method: 'POST',
            body: formData
        });

        if (res.status === 200) {
            toast.success(await res.text());
        } else {
            toast.error('Encountered a problem -- try again');
        }

        confirmRebuild = false;
        rebuildingDB = false;
    }

    async function pruneS3() {

    }

    async function generateThumbnails() {
        generatingThumbnails = true;
        const res = await fetch('/api/generate-thumbnails');
        if (res.status === 200) {
            toast.success('suces')
        } else {
            toast.error('Encountered an error :/');
        }
        generatingThumbnails = false;
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
    } from "@/components/ui/item";
    import Input from '@/components/ui/input/input.svelte';
    import { Switch } from '@/components/ui/switch';
    import Separator from '@/components/ui/separator/separator.svelte';
    import Spinner from '@/components/ui/spinner/Spinner.svelte';
    import * as AlertDialog from "@/components/ui/alert-dialog/index.js";
    import { toast } from 'svelte-sonner';
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
        <!-- <Item variant="outline">
            <ItemContent>
                <ItemTitle>Automatically Update</ItemTitle>
                <ItemDescription>
                    Leave this off if you'll be making lots of adjustments in a small time period.
                </ItemDescription>
            </ItemContent>
            <ItemActions>
                <Switch
                    id="active"
                    bind:checked={autoUpdate}
                />
            </ItemActions>
        </Item> -->
        <Item variant="outline">
            <ItemContent>
                <ItemTitle>Push Changes</ItemTitle>
                <ItemDescription>
                    Set the most recent file in pending/ to active
                </ItemDescription>
            </ItemContent>
            <ItemActions>
                <Button onclick={pushChanges} variant="outline" size="sm">
                Push Changes
                {#if pushingChanges}
                <Spinner />
                {/if}
                </Button>
            </ItemActions>
        </Item>
        <Item variant="outline">
            <ItemContent>
                <ItemTitle>Revert Changes</ItemTitle>
                <ItemDescription>
                    Set the last file in backups/ to active
                </ItemDescription>
            </ItemContent>
            <ItemActions>
                <Button onclick={revertChanges} variant="outline" size="sm">
                Revert Changes
                {#if revertingChanges}
                <Spinner />
                {/if}
                </Button>
            </ItemActions>
        </Item>
        <Item variant="outline">
            <ItemContent>
                <ItemTitle>Generate JSON</ItemTitle>
                <ItemDescription>
                    Generate a new data file to pending/
                </ItemDescription>
            </ItemContent>
            <ItemActions>
                <Button onclick={generateJSON} variant="outline" size="sm">
                Generate
                {#if generatingJSON}
                <Spinner />
                {/if}
                </Button>
            </ItemActions>
        </Item>
        <Item variant="outline">
            <ItemContent>
                <ItemTitle>Rebuild DB</ItemTitle>
                <ItemDescription>
                    Upload an existing data file to overwrite the DB on this server.
                </ItemDescription>
            </ItemContent>
            <ItemActions>
                <Input
                    onchange={rebuildDB}
                    type="file"
                    bind:files={db_file}
                />
            </ItemActions>
        </Item>
        <Item variant="outline">
            <ItemContent>
                <ItemTitle>Remove Unused S3 Images</ItemTitle>
            </ItemContent>
            <ItemActions>
                <Button onclick={pruneS3} variant="outline" size="sm">
                Remove
                {#if pruningS3}
                <Spinner />
                {/if}
                </Button>
            </ItemActions>
        </Item>
        <Item variant="outline">
            <ItemContent>
                <ItemTitle>Generate Thumbnails</ItemTitle>
            </ItemContent>
            <ItemActions>
                <Button onclick={generateThumbnails} variant="outline" size="sm">
                Generate
                {#if generatingThumbnails}
                <Spinner />
                {/if}
                </Button>
            </ItemActions>
        </Item>
    </ItemGroup>
    <AlertDialog.Root bind:open={confirmRebuild}>
        <AlertDialog.Content class="sm:max-w-[425px]">
            <AlertDialog.Header>
                <AlertDialog.Title>
                    Are you sure you want to rebuild the DB?
                    <br/>&nbsp;<br/>
                    All Pieces, Media, Collections, etc. will be set to the uploaded file
                </AlertDialog.Title>
            </AlertDialog.Header>
            <AlertDialog.Footer>
                <AlertDialog.Cancel variant='ghost'>Cancel</AlertDialog.Cancel>
                    <Button onclick={rebuildDB} variant="destructive" disabled={rebuildingDB}>
                        Confirm
                        {#if rebuildingDB}
                        <Spinner />
                        {/if}
                    </Button>
            </AlertDialog.Footer>
        </AlertDialog.Content>
    </AlertDialog.Root>
</div>

