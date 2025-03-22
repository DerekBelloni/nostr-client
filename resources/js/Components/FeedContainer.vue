<template>
    <FeedPresenter/>
</template>

<script setup>
import FeedPresenter from './FeedPresenter.vue';
import { useNostrStore } from '@/stores/useNostrStore';
import { useSearchStore } from '@/stores/useSearchStore';
import { has } from 'lodash';
import { computed, provide, ref } from 'vue';

const nostrStore = useNostrStore();
const searchStore = useSearchStore();

const feedNotes = computed(() => searchStore.searchResults.length > 0 ? searchStore.searchResults : searchStore.trendingContent);
const isSearchActive = computed(() => searchStore.searchActive ? true : false);

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
    console.log("npub: ", npub);
    // return axios.post('')
} 

const setDisplayName = (authorContent) => {
    console.log('author content: ', authorContent)
    if (authorContent?.display_name) {
        return authorContent?.display_name
    } else if (authorContent?.displayName) {
        return authorContent?.displayName
    } else if (authorContent?.name) {
        return authorContent?.name;
    } 
    return authorContent?.name || authorContent?.display_name;
}

provide('feedNotes', feedNotes);
provide('hasDisplayName', hasDisplayName);
provide('hasNip05', hasNip05);
provide('isSearchActive', isSearchActive);
provide('noteDate', noteDate);
provide('retrieveNpubMetadata', retrieveNpubMetadata);
provide('setDisplayName', setDisplayName);
</script>