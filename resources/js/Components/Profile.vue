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
        <span class="inline-block rounded-full bg-gray-200 px-4 py-1 font-medium">Followed</span>
    </div>
    <div class="mt-4">
        <UserNote></UserNote>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useNostrStore } from '@/stores/useNostrStore';
import UserNote from './UserNotes.vue';

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