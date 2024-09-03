<template>
    <div class="banner-container pt-12">
        <img class="banner" :src="userMetadata.banner" />
    </div>
    <div class="flex flex-row justify-between">
        <div class="profile-picture-container px-6">
            <img class="profile-picture" :src="userMetadata.picture"/>
        </div>
        <div class="mt-6 mx-10">
            <div class="rounded-full bg-amber-500 py-1 px-3">
                <span class="text-white">Edit</span>
            </div>
        </div>
    </div>
    <div class="px-6 username-container">
        <span class="text-3xl font-semibold text-gray-700">{{userMetadata.display_name}}</span>
        <i class="pi pi-check text-emerald-500 pl-4 text-lg"></i>
    </div>
    <div class="pl-4 pt-4 space-x-2">
        <span class="inline-block rounded-full bg-gray-200 px-4 py-1 font-medium">Notes</span>
        <span class="inline-block rounded-full bg-gray-200 px-4 py-1 font-medium">Reactions</span>
        <span class="inline-block rounded-full bg-gray-200 px-4 py-1 font-medium">Followers</span>
    </div>
    <ul>
        <li v-for="note in nostrStore.userNotes" :key="note.pubkey" class="flex flex-col border-b border-gray-300 py-2 px-2 my-2">
            <div class="grid grid-cols-12">
                <div class="col-span-1">
                    <div v-if="nostrStore?.metadataContent?.content?.picture">
                        <img class="rounded-full h-10 w-10 border border-amber-500" :src="nostrStore?.metadataContent?.content?.picture" alt="">
                    </div>
                </div>
                <div class="col-span-11 col-start-2">
                    <!-- <div class="flex justify-between">
                        <div>
                            <span class="text-gray-700 font-semibold">{{note.author?.content.name}}</span>
                            <i class="pi pi-verified pl-1"></i>
                            <span class="text-amber-600 pl-1">{{note.author?.content.nip05}}</span>
                        </div>
                        <span class="text-xs font-medium text-gray-600">{{noteDate(note.event.utc_timestamp)}}</span>
                    </div> -->
                </div>
            </div>
            <div class="grid grid-cols-12">
                <div v-for="content in note[2]" class="col-span-11 col-start-2">
                    <!-- <div v-if="content.type === 'video'" class="rounded">
                        <div class="my-2">
                            <video class="rounded-video" width="600" height="405" controls>
                                <source :src="content.content" type="video/mp4">
                            </video>
                        </div>
                    </div> -->
                    <div>
                        <div class="text-wrap">
                            <span class="font-medium" v-html="content.content"></span>
                        </div>
                    </div>
                    <!-- <div v-if="content.type === 'image'" class="my-4">
                        <div class="text-wrap">
                            <img class="rounded" :src="content.content" alt="">
                        </div>
                    </div> -->
                </div>
            </div>
            <!-- <div class="grid grid-cols-12 mt-4 mb-2 mx-4">
                <div class="col-start-2 col-span-11 flex space-x-12">
                    <i class="pi pi-comment text-emerald-500" style="font-size: 1.1rem"></i>
                    <i class="pi pi-heart text-rose-500" style="font-size: 1.1rem"></i>
                    <i class="pi pi-bolt text-amber-500" style="font-size: 1.1rem"></i>
                </div>
            </div> -->
        </li>
    </ul>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useNostrStore } from '@/stores/useNostrStore';

const nostrStore = useNostrStore();
const userMetadata = ref(nostrStore.metadataContent.content);

onMounted(() => {
    console.log("user metadata: ", userMetadata.value);
})
</script>

<style scoped>
    .banner-container {
        width: 100%;
        height: 325px;
        margin: 0 auto;
        overflow: hidden;
    }

    .banner {
        width: 100%; /* Ensure the image covers the full width */
        height: auto; /* Maintain aspect ratio */
        object-fit: cover; /* Cover the container */
        display: block; /* Remove any unwanted whitespace */
    }

    .profile-picture-container {
        position: relative;
        display: flex;
    }

    .profile-picture {
        width: 150px; /* Set a fixed width */
        height: 150px; /* Set a fixed height to match the width */
        border-radius: 50%;
        transform: translateY(-64px); /* Adjust the value as needed to position the image */
        object-fit: cover; /* Ensure the image covers the area */
        border: 2px solid white; /* Optional: Add a border to enhance the circular look */
    }

    .edit-button {
        margin-top: -32px;
    }

    .username-container {
        margin-top: -48px;
    }
</style>