<template>
    <div v-for="follow in followsMetadata">
        <div class="flex flex-row cursor-pointer">
            <div class="profile-picture-container ml-4">
                <img v-if="follow.picture" class="profile-picture" :src="follow.picture" alt="" @error="handleImageError">
                <Avatar v-else size="large"></Avatar>
            </div>
            <div class="flex flex-col ml-2 mt-1">
                <div>
                    <span>{{setDisplayName(follow)}}</span>
                </div>
                <div class="-mt-1 block overflow-hidden truncate">
                    <span class="text-sm text-gray-600">{{follow.about}}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { computed, ref } from 'vue';
    import { useNostrStore } from '@/stores/useNostrStore';
    import Avatar from 'primevue/avatar';

    const nostrStore = useNostrStore();
    const followsMetadata = ref(nostrStore.userFollowsContent);
    
    const setDisplayName = (follow) => {
        return follow.display_name || follow.name;
    }

    const handleImageError = (e) => {
        console.log("image error: ", e.target.src);
        e.target.style.display = 'none';
    } 
</script>

<style scoped>
    .profile-picture-container {
        display: flex;
        width: 50px;
        height: 50px;
    }
    .profile-picture {
        width: 50px; 
        height: 50px;
        border-radius: 50%;
        object-fit: cover; 
        border: 2px solid white;
        aspect-ratio: 1/1;
    }
</style>