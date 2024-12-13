<template>
    <div class="overflow-y-auto h-full">
        <div class="banner-container pt-12">
            <img class="banner" :src="activeMetadata.banner" />
        </div>
        <div class="flex flex-row justify-between">
            <div class="profile-picture-container px-6">
                <img class="profile-picture" :src="activeMetadata.picture"/>
            </div>
            <div class="mt-6 mx-10">
                <div class="rounded-full bg-amber-500 py-1 px-3">
                    <span class="text-white">Edit</span>
                </div>
            </div>
        </div>
        <div class="px-6 username-container">
            <span class="text-3xl font-semibold text-gray-700">{{activeMetadata.display_name}}</span>
            <i class="pi pi-check text-emerald-500 pl-4 text-lg"></i>
        </div>
        <div class="pl-4 pt-4 space-x-2">
            <span class="inline-block rounded-full bg-gray-200 px-4 py-1 font-medium cursor-pointer hover:bg-gray-300" @click="selectTab('notes')">Notes</span>
            <span class="inline-block rounded-full bg-gray-200 px-4 py-1 font-medium cursor-pointer hover:bg-gray-300" @click="selectTab('reactions')">Reactions</span>
            <span class="inline-block rounded-full bg-gray-200 px-4 py-1 font-medium cursor-pointer hover:bg-gray-300" @click="selectTab('followers')">Followers</span>
            <span class="inline-block rounded-full bg-gray-200 px-4 py-1 font-medium cursor-pointer hover:bg-gray-300" @click="selectTab('followed')">Followed</span>
        </div>
        <div class="mt-4 overflow-y-auto">
            <div v-if="activeTab == 'notes'">
                <UserNote :followNotesLoading="followNotesLoading"></UserNote>
            </div>
            <div v-if="activeTab == 'followed'">
                <FollowsMetadata></FollowsMetadata>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { useNostrStore } from '@/stores/useNostrStore';
import UserNote from './UserNotes.vue';
import FollowsMetadata from './FollowsMetadata.vue';

let followNotesLoading = ref(false);
const nostrStore = useNostrStore();
const userMetadata = nostrStore.metadataContent;
const activeTab = ref(null);

onMounted(() => {
    activeTab.value = "notes";
    nostrStore.userActiveProfile = true;
});

onUnmounted(() => {
    activeTab.value = null;
});

const selectTab = (tabType) => {
    switch(tabType) {
        case "notes":
            activeTab.value = "notes";
            break;
        case "followed":
            activeTab.value = "followed";
            break;
        default:
            activeTab.value = null;
            break;
    }
}

const activeMetadata = computed(() => {
    return nostrStore.followMetadataContent || nostrStore.metadataContent;
});

watch(() => nostrStore.followMetadataContent, (newValue) => {
    if (newValue) {
        activeTab.value = "notes";
        followNotesLoading.value = true;
        nostrStore.userActiveProfile = false;
    };
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
        width: 100%; 
        height: auto; 
        object-fit: cover; 
        display: block; 
    }

    .profile-picture-container {
        position: relative;
        display: flex;
    }

    .profile-picture {
        width: 150px;
        height: 150px; 
        border-radius: 50%;
        transform: translateY(-64px); 
        object-fit: cover; 
        border: 2px solid white; 
    }

    .edit-button {
        margin-top: -32px;
    }

    .username-container {
        margin-top: -48px;
    }
</style>
