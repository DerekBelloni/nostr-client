<template>
    <FeedSearch></FeedSearch>
    <div class="notes-container">
        <ul>
            <li v-for="note in props.trendingContent" :key="note.pubkey" class="flex flex-col border-b border-gray-300 py-2 px-2 my-2">
                <div class="grid grid-cols-12">
                    <div class="col-span-1">
                        <div v-if="note.author?.content.picture">
                            <img class="rounded-full h-10 w-10 border border-amber-500" :src="note.author?.content.picture" alt="">
                        </div>
                    </div>
                    <div class="col-span-11 col-start-2">
                        <div class="flex justify-between">
                            <div>
                                <span class="text-gray-700 font-semibold">{{note.author?.content.name}}</span>
                                <i class="pi pi-verified pl-1"></i>
                                <span class="text-amber-600 pl-1">{{note.author?.content.nip05}}</span>
                            </div>
                            <span class="text-xs font-medium text-gray-600">{{noteDate(note.event.utc_timestamp)}}</span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-12">
                    <div v-for="content in note.event.processed_content" class="col-span-11 col-start-2">
                        <div v-if="content.type === 'video'" class="rounded">
                            <div class="my-2">
                                <video class="rounded-video" width="600" height="405" controls>
                                    <source :src="content.content" type="video/mp4">
                                </video>
                            </div>
                        </div>
                        <div v-if="content.type === 'text' || content.type === 'link'" class="nostr-content">
                            <div class="text-wrap break-words">
                                <span class="font-medium" v-html="content.content"></span>
                            </div>
                        </div>
                        <div v-if="content.type === 'image'" class="my-4">
                            <div class="text-wrap">
                                <img class="rounded" :src="content.content" alt="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-12 mt-4 mb-2 mx-4">
                    <div class="col-start-2 col-span-11 flex space-x-12">
                        <i class="pi pi-comment text-emerald-500 cursor-pointer" style="font-size: 1.1rem"></i>
                        <i class="pi pi-heart text-rose-500 cursor-pointer" style="font-size: 1.1rem"></i>
                        <i class="pi pi-bolt text-amber-500 cursor-pointer" style="font-size: 1.1rem"></i>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <div>
        <span>test</span>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import InputText from 'primevue/inputtext';
import FeedSearch from './FeedSearch.vue';

const props = defineProps(['trendingContent']);
const notes = ref(props.notes);

const noteDate = (utcDate) => {
    const formattedDate = utcDate.split(' ')
    return formattedDate[0];
}

</script>

<style>
    :root {
        --main-bg-color: #f0f0f0;
        --main-text-color: #333333;
        --border-color: 55, 65, 81; /* Define the RGB values */
        --border-opacity: 1; /* Default opacity */
    }
    .center-feature {
        width: 50%;
    }

    .nostr-content a {
        @apply text-amber-500 underline hover:text-amber-700 text-wrap;
    }
    .notes-container {
        height: 100%;
        overflow-y: auto;
        padding: 10px;
       
    }
    .rounded-video {
        border-radius: 15px; /* Adjust the value to your preference */
        overflow: hidden; /* Ensures the rounded corners are applied */
        border: none !important; /* Removes any border */
        outline: none !important; /* Removes any outline */
        box-shadow: none !important; /* Removes any box shadow */
    }
</style>