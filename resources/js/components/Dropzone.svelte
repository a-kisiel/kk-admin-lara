<script lang="ts">
    import * as AlertDialog from "@/components/ui/alert-dialog/index.js";

    import Dropzone from "svelte-file-dropzone";
    import Spinner from "./ui/spinner/Spinner.svelte";
    import { toast } from "svelte-sonner";
    import { settled, tick } from "svelte";

    let {
        index,
        piece = {},
        updateImage = () => {}
    } = $props();

    let maskClasses = $state('');
    let confirmOverwriteOpen = $state(false);

    let pendingFile = $state(null);

    let compressed = $state(new DataTransfer());
    let uncompressed = $state(new DataTransfer());

    let cFiles = $derived(compressed.files);

    let converting = $state(false);

    async function convert(file: File, format: String) {
        return new Promise<File>((resolve, reject) => {
            try {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = () => {
                    const img = new Image();
                    img.src = reader.result?.toString() ?? '';
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');

                        canvas.width = img.width;
                        canvas.height = img.height;
                        ctx?.drawImage(img, 0, 0);

                        const url = canvas.toDataURL(`image/${format}`);

                        const bin = atob(url.split(',')[1]);
                        const arr = [];
                        for (var i=0; i<bin.length; i++) {
                        arr.push(bin.charCodeAt(i));
                        }
                        URL.revokeObjectURL(url);

                        const result = new Blob([new Uint8Array(arr)], {type: `image/${format}`});
                        const filename = format === 'webp' ? 'compressed.webp' : 'uncompressed.jpg';

                        toast.success(`Successfully created a ${format} version`);

                        resolve(new File([result], filename));
                    };
                };
            } catch (e) {
                console.error(e);
            }
        });
    }

    async function droppedFile(e: any) {
        if (e) {
            const { acceptedFiles, fileRejections } = e.detail;
            pendingFile = acceptedFiles[0];
        }

        const type = pendingFile.type.toLowerCase();

        if (!['image/png', 'image/jpg', 'image/jpeg', 'image/webp'].includes(type)) {
            toast.info('File should be a png, jpg, or webp');
            pendingFile = null;
            confirmOverwriteOpen = false;
            return;
        }

        // Get confirmation to overwrite existing
        if ((piece?.hash) && !confirmOverwriteOpen) {
            confirmOverwriteOpen = true;
            return;
        }

        if (!pendingFile)
            return;

        compressed = new DataTransfer();
        uncompressed = new DataTransfer();

        converting = true;

        uncompressed.items.add(await convert(pendingFile, 'jpg'));
        compressed.items.add(await convert(pendingFile, 'webp'));

        const newComp = new DataTransfer();
        Array.from(compressed.files).forEach(f => newComp.items.add(f));
        
        const newUncomp = new DataTransfer();
        Array.from(uncompressed.files).forEach(f => newUncomp.items.add(f));

        compressed = newComp;
        uncompressed = newUncomp;

        const imageURL = uncompressed.files[0] ?
            URL.createObjectURL(uncompressed.files[0]) :
            '';

        updateImage({
            uncompressed,
            compressed,
            url: imageURL
        });

        converting = false;
        maskClasses = '';

        pendingFile = null;
    }

    function draggingOver() {
        maskClasses = 'dz-hovering'
    }
    function stopDragging() {
        maskClasses = '';
    }
</script>

<div class="image-mask {maskClasses}">
    {#each cFiles as file}
    - {file.name}(c)<br/>
    {/each}
    <Dropzone 
        on:drop={droppedFile}
        on:dragenter={draggingOver}
        on:dragleave={stopDragging}
        containerClasses='dz-area'
        disableDefaultStyles={true}
    >
        <span style="display: flex;">
            {#if converting}
            Converting&nbsp;&nbsp;<Spinner />
            {:else}
            Click or Drop a file here
            {/if}
            <!-- <input hidden
                name="{index}_compressed"
                type="file"
                files={compressed.files}
            > -->
            <input hidden
                name="{index}_compressed"
                type="file"
                bind:files={cFiles}
            >
            <input hidden
                name="{index}_uncompressed"
                type="file"
                files={uncompressed.files}
            >
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
                <AlertDialog.Cancel onclick={() => pendingFile = null} variant='outline'>Cancel</AlertDialog.Cancel>
                <AlertDialog.Action onclick={() => droppedFile(null)}>
                    Overwrite
                    {#if converting}
                    &nbsp; <Spinner />
                    {/if}
                </AlertDialog.Action>
            </AlertDialog.Footer>
        </AlertDialog.Content>
    </AlertDialog.Root>
</div>