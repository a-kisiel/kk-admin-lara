<script lang="ts">
    import Carousel from 'svelte-carousel';
    import {
        Tooltip,
        TooltipContent,
        TooltipTrigger,
    } from "@/components/ui/tooltip";

    import { ChevronLeft, ChevronRight, SquarePen, CircleCheck, Circle, Image } from "lucide-svelte";

    let {
        piece = $bindable()
    } = $props();

    let carousel;

    let active = $state(piece.active);

    async function toggleActive() {
        const res = await fetch(`/api/pieces/${piece.id}/toggle-active`, {
            'method': 'POST'
        });
        active = !active;
    }
</script>

<div class="piece-card">
    <div class='card-images'>
        <Carousel
            bind:this={carousel}
            arrows={piece.children?.length > 0}
            dots={false}
        >
            <a
                href="/pieces/{piece.id}"
                class="carousel-item"
                style="{piece.hash ? `background-image: url(${piece.stub}compressed/${piece.hash}.webp);` : ''}"
                title={piece.title}
            ></a>
            {#each piece.children as child}
            <a
                href="/pieces/{piece.id}"
                class="carousel-item {child.hash ? '' : 'default-image'}"
                style="{child.hash ? `background-image: url(${piece.stub}compressed/${child.hash}.webp);` : ''}"
                title={child.title}
            >
                {#if !child.hash}
                <Image class="default-icon" style="width: 70px; height: 70px;" />
                {/if}
            </a>
            {/each}
            <button onclick={carousel.goToPrev} class="carousel-control next" slot="prev"><ChevronLeft /></button>
            <button onclick={carousel.goToNext} class="carousel-control previous" slot="next"><ChevronRight /></button>
        </Carousel>
    </div>
    <div class="card-footer">
        <a href="/pieces/{piece.id}" class="card-title">{piece.title}</a>
        <div class="card-tools">
            <a href="/pieces/{piece.id}/edit" style="margin-right: 4px;"><SquarePen /></a>
            <button onclick={toggleActive} class="active-toggle">
                {#if active}
                <Tooltip>
                    <TooltipTrigger><CircleCheck /></TooltipTrigger>
                    <TooltipContent>
                        Deactivate
                    </TooltipContent>
                </Tooltip>
                {:else}
                <Tooltip>
                    <TooltipTrigger><Circle /></TooltipTrigger>
                    <TooltipContent>
                        Activate
                    </TooltipContent>
                </Tooltip>
                {/if}
            </button>
        </div>
    </div>
</div>