<template>
    <FollowListPresenter />
</template>

<script setup>
import { useNostrStore } from '@/stores/useNostrStore';
import FollowListPresenter from './FollowListPresenter.vue';
import { computed, provide } from 'vue';

const store = useNostrStore();
const profileFollowsList = computed(() => store.activeProfile.follows);

const setActiveFollow = (follow) => {
    store.clearActiveProfile();
    // at somepoint during this process I do want to set the active tab to 'notes'
    store.setActiveProfileMetadata(follow);
    retrieveFollowsNotes();
}

const retrieveFollowsNotes = () => {
    return axios.post('/rabbit-mq/follow-notes', {userPubkey: store.hexPub, followPubkey: store.activeProfile.metadata.pubkey})
        .then((response) => {
            console.log('response banana: ', response);
        });
}   

const setDisplayName = (follow) => {
    return follow.display_name || follow.name;
}

provide('profileFollowsList', profileFollowsList);
provide('followState', {
    setActiveFollow,
    setDisplayName
});

</script>