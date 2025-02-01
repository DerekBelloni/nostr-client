<template>
    <FeedSearch/>
    <div class="notes-container">
        <ul>
            <li v-for="note in feedNotes" :key="note.pubkey" class="flex flex-col border-b border-gray-300 py-2 px-2 my-1">
                <div class="grid grid-cols-12">
                    <div class="col-span-1">
                        <div v-if="note.author?.content.picture">
                            <img class="rounded-full h-14 w-14 border border-amber-500" :src="note.author?.content.picture" alt="">
                        </div>
                        <div v-else>
                            <img src="/images/avatar.jpg" class="rounded-full h-14 w-14 border border-amber-500" alt="">
                        </div>
                    </div>
                    <div class="col-span-11 col-start-2">
                        <div class="flex justify-between">
                            <div class="mt-2">
                                <template v-if="!isSearchActive">              
                                    <span class="text-gray-700 font-semibold">{{note.author?.content.name}}</span>
                                    <template v-if="note.author?.content.nip05">
                                        <i class="pi pi-verified pl-1"></i>
                                        <span class="text-amber-600 pl-1">{{note.author?.content.nip05}}</span>
                                    </template>
                                </template>
                                <template v-else-if="isSearchActive">
                                    <template v-if="hasDisplayName(note.author?.content)">
                                        <span class="text-gray-700 font-semibold">{{setDisplayName(note.author?.content)}}</span>
                                        <template class="flex flex-row" v-if="hasNip05(note.author?.content)">
                                            <i class="pi pi-verified pl-1"></i>
                                            <span class="text-amber-600 pl-1">{{note.author?.content.nip05}}</span>
                                        </template>
                                        <template v-else class="block">
                                            <span class="text-gray-600 truncate italic ml-2">{{note.author?.content.pubkey || note.pubkey}}</span>
                                        </template>
                                    </template>
                                </template>
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
</template>

<script setup>
import { inject } from 'vue';
import FeedSearch from './FeedSearch.vue';

const hasDisplayName = inject('hasDisplayName');
const hasNip05 = inject('hasNip05');
const feedNotes = inject('feedNotes');
const isSearchActive = inject('isSearchActive');
const noteDate = inject('noteDate');
const setDisplayName = inject('setDisplayName');
</script>

<style>
    :root {
        --main-bg-color: #f0f0f0;
        --main-text-color: #333333;
        --border-color: 55, 65, 81; 
        --border-opacity: 1; 
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
    .feed-image {
        
    }
    .rounded-video {
        border-radius: 15px;
        overflow: hidden; 
        border: none !important;
        outline: none !important;
        box-shadow: none !important; 
        height: 50% !important;
    }
</style>