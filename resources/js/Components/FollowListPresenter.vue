<template>
    <div v-for="follow in profileFollowsList">
        <div @click="setActiveFollow(follow)" class="flex flex-row cursor-pointer w-full pt-2 hover:bg-gray-100">
            <div class="profile-picture-container ml-4 flex-shrink-0">
                <!-- <img v-if="follow.picture" class="profile-picture" :src="follow.picture" alt="" @error="handleImageError" @load="handleImageLoad"> -->
                <img v-if="follow.picture" class="profile-picture" :src="follow.picture" alt="">
                <!-- <Avatar v-else size="large"></Avatar> -->
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
import { inject } from 'vue';


const profileFollowsList = inject('profileFollowsList');
const {setDisplayName, setActiveFollow} = inject('followState');

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