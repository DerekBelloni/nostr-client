import { defineStore } from "pinia";
import { ref } from 'vue';

export const useSearchStore = defineStore('search', () => {
    const searchResults = ref([]);
    const trendingContent = ref([]);

    const addSearchResults = (results) => {
        if (searchResults.value.length > 0) searchResults.value = [];
        results.forEach(result => searchResults.value.push(result));
    }

return { addSearchResults, searchResults, trendingContent }
});