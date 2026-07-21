<script lang="ts">
    import * as AlertDialog from "@/components/ui/alert-dialog/index.js";

    import Dropzone from "svelte-file-dropzone";
    import { toast } from "svelte-sonner";

    let {
        piece = {},
        updateImage = () => {}
    } = $props();

    let maskClasses = $state('');
    let confirmOverwriteOpen = $state(false);
    let overwriteConfirmed = $state(false);

    function confirmOverwrite() {
        overwriteConfirmed = true;
        confirmOverwriteOpen = false;
    }

    function droppedFile(e: any) {
        maskClasses = '';

        if (piece?.hash && !overwriteConfirmed) {
            confirmOverwriteOpen = true;
            return;
        }

        if (e) {
            const { acceptedFiles, fileRejections } = e.detail;
            if (acceptedFiles.length > 0) {
                const url = URL.createObjectURL(acceptedFiles[0]);
                updateImage(url);
            }
            if (fileRejections.length > 0) {
                const error = fileRejections[0].errors[0];
                if (error.code === 'file-invalid-type')
                    toast.error('Please only upload jpg, png, or webp files');
                else
                    toast.error('Encountered an error -- reload the page and try again');
            }
        }
        overwriteConfirmed = false;
    }

    function draggingOver() {
        maskClasses = 'dz-hovering'
    }
    function stopDragging() {
        maskClasses = '';
    }
</script>

<div class="image-mask {maskClasses}">
    <Dropzone 
        on:drop={droppedFile}
        on:dragenter={draggingOver}
        on:dragleave={stopDragging}
        name="file_{piece?.id ?? 'new'}"
        accept="image/jpg,image/jpeg,image/png,image/webp"
        multiple={false}
        containerClasses='dz-area'
    >
        <span>
            Click or Drop a file here
        </span>
    </Dropzone>
    <AlertDialog.Root bind:open={confirmOverwriteOpen}>
        <AlertDialog.Content class="sm:max-w-[425px]">
            <AlertDialog.Header>
                <AlertDialog.Title>
                    This will overwrite the existing file -- continue?
                </AlertDialog.Title>
            </AlertDialog.Header>
            <AlertDialog.Footer>
                <AlertDialog.Cancel onclick={() => confirmOverwriteOpen = false} variant='outline'>Cancel</AlertDialog.Cancel>
                <AlertDialog.Action onclick={confirmOverwrite}>
                    Overwrite
                </AlertDialog.Action>
            </AlertDialog.Footer>
        </AlertDialog.Content>
    </AlertDialog.Root>
</div>