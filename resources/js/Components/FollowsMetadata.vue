<template>
    <div v-for="follow in followsMetadata">
        <div class="flex flex-row ">
            <div class="profile-picture-container ml-4">
                <img class="profile-picture" :src="follow.picture" alt="">
            </div>
            <div class="flex flex-col">
                <div>
                    <!-- make into computed-->
                    <span>{{setDisplayName(follow)}}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { computed, ref } from 'vue';
    import { useNostrStore } from '@/stores/useNostrStore';

    const nostrStore = useNostrStore();
    const followsMetadata = ref(nostrStore.userFollowsContent);
    
    const setDisplayName = (follow) => {
        return follow.display_name ? follow.display_name : follow.name;
    }
</script>

<style scoped>
    .profile-picture-container {
        position: relative;
        display: flex;
    }
    .profile-picture {
        width: 50px; /* Set a fixed width */
        height: 50px; /* Set a fixed height to match the width */
        border-radius: 50%; /* Adjust the value as needed to position the image */
        object-fit: cover; /* Ensure the image covers the area */
        border: 2px solid white; /* Optional: Add a border to enhance the circular look */
    }
</style>