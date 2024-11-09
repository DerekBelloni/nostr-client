<template>
    <div v-for="follow in followsMetadata">
        <div class="flex flex-row ">
            <div class="profile-picture-container ml-4">
                <img class="profile-picture" :src="follow.picture" alt="">
            </div>
            <div class="flex flex-col ml-2 mt-1">
                <div>
                    <span>{{setDisplayName(follow)}}</span>
                </div>
                <div class="-mt-1">
                    <span class="text-sm text-gray-600">{{follow.about}}</span>
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
        return follow.display_name || follow.name;
    }
</script>

<style scoped>
    .profile-picture-container {
        position: relative;
        display: flex;
    }
    .profile-picture {
        width: 50px; 
        height: 50px;
        border-radius: 50%;
        object-fit: cover; 
        border: 2px solid white;
    }
</style>