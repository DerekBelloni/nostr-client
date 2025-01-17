import { defineStore } from "pinia";
import { ref } from 'vue';

export const useSearchStore = defineStore('search', () => {
    const searchActive = ref(false);
    const searchResults = ref([]);
    const trendingContent = ref([]);

    const resetStore = () => {
        searchActive.value = false,
        searchResults.value = [];
    }

    const addSearchResults = (results) => {
        console.log('search results in nostr store: ', results);
        if (searchResults.value.length > 0) searchResults.value = [];
        searchResults.value.push(...results);
        searchActive.value = true;
    }

    const clearSearchResults = () => {
        searchResults.value = [];
        searchActive.value = false;
    }

return { addSearchResults, clearSearchResults, resetStore, searchActive, searchResults, trendingContent }
});