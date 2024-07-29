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
                        <span class="text-gray-700 font-semibold">{{note.author?.content.name}}</span>
                        <i class="pi pi-verified pl-1"></i>
                        <span class="text-amber-600 pl-1">{{note.author?.content.nip05}}</span>
                    </div>
                </div>
                <div class="grid grid-cols-12">
                    <div class="col-span-11 col-start-2">
                        <!-- <template v-if="!note.metadata_content.name && !note.metadata_content.nip05">
                            <span class="text-amber-600 font-semibold">{{note.npub}}</span>
                        </template> -->
                        <div class="text-wrap">
                            <!-- <span class="font-medium" v-html="note.event.content">{{note.event.content}}</span> -->
                            <span class="font-medium" v-html="note.event.content"></span>
                        </div>
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

</script>

<style scoped>
    :root {
        --main-bg-color: #f0f0f0;
        --main-text-color: #333333;
        --border-color: 55, 65, 81; /* Define the RGB values */
        --border-opacity: 1; /* Default opacity */
    }
    .center-feature {
        width: 50%;
    }

    .notes-container {
        height: 100%;
        overflow-y: auto;
        padding: 10px;
       
    }
</style>