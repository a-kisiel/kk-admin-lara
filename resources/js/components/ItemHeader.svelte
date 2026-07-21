<script lang="ts">
    import * as AlertDialog from "@/components/ui/alert-dialog/index.js";

    import { Trash2 } from 'lucide-svelte';

    let {item, type, label, mode} = $props();
</script>

<div class="page-header">
    <div class="page-title">
        {#if mode === 'add'}
            <h1>Add {label}</h1>
        {:else if mode === 'edit'}
            <h1>Edit {item.title}</h1>
        {:else}
            <h1>{item.title}</h1>
        {/if}
    </div>
    {#if mode !== 'add'}
    <div class="delete-item">
        <AlertDialog.Root>
            <AlertDialog.Trigger>
                <Trash2 />
            </AlertDialog.Trigger>
            <AlertDialog.Content class="sm:max-w-[425px]">
                <AlertDialog.Header>
                    <AlertDialog.Title>
                        Are you sure you want to delete this?
                    </AlertDialog.Title>
                </AlertDialog.Header>
                <AlertDialog.Footer>
                    <AlertDialog.Cancel variant='outline'>Cancel</AlertDialog.Cancel>
                    <form action="/{type}/{item.id}/delete">
                        <AlertDialog.Action variant='destructive'>Yes, kill this thing</AlertDialog.Action>
                    </form>
                </AlertDialog.Footer>
            </AlertDialog.Content>
        </AlertDialog.Root>
    </div>
    {/if}
</div>