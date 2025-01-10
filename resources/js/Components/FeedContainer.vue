<template>
    <FeedPresenter/>
</template>

<script setup>
import FeedPresenter from './FeedPresenter.vue';
import { useNostrStore } from '@/stores/useNostrStore';
import { useSearchStore } from '@/stores/useSearchStore';
import { computed, provide, ref } from 'vue';

const nostrStore = useNostrStore();
const searchStore = useSearchStore();

const feedNotes = computed(() => searchStore.searchResults.length > 0 ? searchStore.searchResults : searchStore.trendingContent);
const isSearchActive = computed(() => searchStore.searchActive ? true : false);

const noteDate = (utcDate) => {
    const formattedDate = utcDate.split(' ')
    return formattedDate[0];
}

const setDisplayName = (authorContent) => {
    console.log('author content: ', authorContent);
    return authorContent?.name || authorContent?.display_name;
}

provide('feedNotes', feedNotes);
provide('isSearchActive', isSearchActive);
provide('noteData', noteDate);
provide('setDisplayName', setDisplayName);
</script>