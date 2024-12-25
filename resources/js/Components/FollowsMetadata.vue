<template>
    <div v-for="follow in followsMetadataList">
        <div @click="setActiveFollow(follow)" class="flex flex-row cursor-pointer w-full pt-2 hover:bg-gray-100">
            <div class="profile-picture-container ml-4 flex-shrink-0">
                <img v-if="follow.picture" class="profile-picture" :src="follow.picture" alt="" @error="handleImageError" @load="handleImageLoad">
                <Avatar v-else size="large"></Avatar>
            </div>
            <div class="flex flex-col ml-2 mt-1 flex-1 min-w-0">
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
    const followsMetadataList = ref(nostrStore.userFollowsContent);
   
    const retrieveFollowsNotes = () => {
        return axios.post('/rabbit-mq/follow-notes', {userPubkey: nostrStore.hexPub, followPubkey: nostrStore.followMetadataContent.pubkey})
            .then((response) => {
                console.log('response: ', response);
            })
    }   

    const setDisplayName = (follow) => {
        return follow.display_name || follow.name;
    }

    const setActiveFollow = (follow) => {
        nostrStore.followMetadataContent = follow;
        retrieveFollowsNotes();
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