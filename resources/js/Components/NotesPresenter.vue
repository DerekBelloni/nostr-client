<template>
    <div>
        <div class="divide-y divide-gray-700 border-t border-gray-700 overflow-auto">
            <div v-for="note in profileNotes">
                <div class="grid grid-cols-12 space-y-4">
                    <div class="col-span-12 mt-2 ml-2">
                        <div class="flex flex-row items-center space-x-2">
                            <img class="rounded-full h-10 w-10" :src="profilePic" alt="">
                            <span class="text-lg font-medium">{{displayName}}</span>
                        </div>
                    </div>
                    <div class="col-span-12 ml-2" v-for="content in note.processed_content">
                        <div v-if="content.type === 'video'" class="rounded">
                            <div class="my-2">
                                <video class="rounded-video" width="600" height="405" controls>
                                    <source :src="content.content" type="video/mp4">
                                </video>
                            </div>
                        </div>
                        <div v-if="content.type === 'text' || content.type === 'link'" class="nostr-content">
                            <div class="text-wrap break-words">
                                <span class="font-medium text-gray-100" v-html="content.content"></span>
                            </div>
                        </div>
                        <div v-if="content.type === 'image'" class="my-4">
                            <div class="text-wrap">
                                <img class="rounded" :src="content.content" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="col-span-12 flex space-x-12 ml-2 pb-4">
                        <i class="pi pi-comment text-gray-200 hover:text-emerald-500 cursor-pointer" style="font-size: 1.1rem"></i>
                        <i class="pi pi-heart text-gray-200 hover:text-red-500 cursor-pointer" style="font-size: 1.1rem"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { inject } from 'vue';

const displayName = inject('displayName');
const profileNotes = inject('profileNotes');
const profilePic = inject('profilePic');
</script>