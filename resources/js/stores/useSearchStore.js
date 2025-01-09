import { defineStore } from "pinia";
import { ref } from 'vue';

export const useSearchStore = defineStore('search', () => {
    const searchResults = ref([]);
    const trendingContent = ref([]);

    const addSearchResults = (results) => {
        if (searchResults.value.length > 0) searchResults.value = [];
        searchResults.value.push(...results);
    }

return { addSearchResults, searchResults, trendingContent }
});