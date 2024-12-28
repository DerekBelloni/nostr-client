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

const noteDate = (utcDate) => {
    const formattedDate = utcDate.split(' ')
    return formattedDate[0];
}

provide('feedNotes', feedNotes);
provide('noteData', noteDate);
</script>