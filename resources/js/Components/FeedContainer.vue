<template>
    <FeedPresenter/>
</template>

<script setup>
import FeedPresenter from './FeedPresenter.vue';
import { useNostrStore } from '@/stores/useNostrStore';
import { useSearchStore } from '@/stores/useSearchStore';
import { has } from 'lodash';
import { computed, provide, ref, watch } from 'vue';

const nostrStore = useNostrStore();
const searchStore = useSearchStore();

const feedNotes = computed(() => searchStore.searchResults.length > 0 ? searchStore.searchResults : searchStore.trendingContent);
const isSearchActive = computed(() => searchStore.searchActive ? true : false);

const retrievedEntities = searchStore.retrievedEntities;

const hasNip05 = (authorContent) => {
    let hasNip = false;
    if (authorContent?.nip05) {
        hasNip = true;
    }
    return hasNip;
}

const hasDisplayName = (authorContent) => {
    let hasDisplay = false;
    if (authorContent?.name || authorContent?.display_name || authorContent?.displayName) {
        console.log('in has display name?')
        hasDisplay = true;
    }
    return hasDisplay;
}

const noteDate = (utcDate) => {
    const formattedDate = utcDate.split(' ')
    return formattedDate[0];
}

const retrieveNpubMetadata = (npub) => {
    nostrStore.npubMetadataUUID = crypto.randomUUID();
    return axios.post('/rabbit-mq/npub-metadata', {bech32: npub, uuid: nostrStore.npubMetadataUUID})
        .then((response) => {
            console.log("cant control the past")
        })
} 

const setDisplayName = (authorContent) => {
    if (authorContent?.display_name) {
        return authorContent?.display_name
    } else if (authorContent?.displayName) {
        return authorContent?.displayName
    } else if (authorContent?.name) {
        return authorContent?.name;
    } 
    return authorContent?.name || authorContent?.display_name;
}

watch(retrievedEntities, (newData, oldData) => {
    console.log('new data', newData)
    console.log('old data ', oldData)
})

provide('retrievedEntities', retrievedEntities);
provide('feedNotes', feedNotes);
provide('hasDisplayName', hasDisplayName);
provide('hasNip05', hasNip05);
provide('isSearchActive', isSearchActive);
provide('noteDate', noteDate);
provide('retrieveNpubMetadata', retrieveNpubMetadata);
provide('setDisplayName', setDisplayName);
</script>