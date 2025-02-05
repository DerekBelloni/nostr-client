<template>
    <FeedSearch/>
    <div class="notes-container">
        <ul>
            <li v-for="note in feedNotes" :key="note.pubkey" class="flex flex-col border-b border-gray-700 py-2 px-2 my-1">
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
                                    <span class="text-gray-700 font-semibold text-white">{{note.author?.content.name}}</span>
                                    <template v-if="note.author?.content.nip05">
                                        <i class="pi pi-verified pl-1 text-amber-500"></i>
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
                                            <span class="text-gray-600 truncate italic ml-2 text-white">{{note.author?.content.pubkey || note.pubkey}}</span>
                                        </template>
                                    </template>
                                </template>
                            </div>
                            <span class="text-xs font-medium text-gray-600">{{noteDate(note.event.utc_timestamp)}}</span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-12">
                    <div v-for="block in note.blocks" class="col-span-11 col-start-2">
                        <div v-if="block.type === 'text'">
                            <span class="text-white">{{block.content}}</span>
                        </div>
                        <div v-if="block.type === 'newline'">
                            <div v-if="block.count > 1">
                                <br/>
                            </div>
                        </div>
                        <div v-if="block.type === 'video'">
                            <video class="rounded-video"  controls>
                                <source :src="block.url" type="video/mp4">
                            </video>
                        </div>
                        <div v-if="block.type === 'hashtags'">
                            <div class="flex flex-row"  v-for="content in block.content">
                                <span class="text-amber-500 hover:text-amber-600 cursor-pointer">{{content}}</span>
                            </div>
                        </div>
                        <div v-if="block.type === 'image'">
                            <div class="text-wrap image-container flex justify-center border border-gray-100">
                                <img class="rounded responsive-image" :src="block.url" alt="">
                            </div>
                        </div>
                        <div v-if="block.type === 'nostr'">
                            <div v-if="block.content.identifier === 'note'" class="border border-gray-200 rounded-lg">
                                <span>@{{block.content.bech32}}</span>
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

    .image-container {
        width: 100%;
        max-width: 100%;
        height: auto;
        margin: 1rem 0;
        position: relative;
        overflow: hidden;
    }
    
    .responsive-image {
        width: 100%;
        height: auto;
        max-height: 100vh;
        margin: 1rem auto;  /* Changed from 1rem 0 to auto for horizontal centering */
        object-fit: contain;
        border-radius: 0.5rem;
        display: block;
        display: flex;     /* Added flex display */
        justify-content: center; /* Center horizontally */
        align-items: center;  
    }
    
    @media (min-width: 48rem) {
        .image-container {
            max-width: 75%;
        }
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