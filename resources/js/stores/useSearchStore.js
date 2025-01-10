import { defineStore } from "pinia";
import { ref } from 'vue';

export const useSearchStore = defineStore('search', () => {
    const searchActive = ref(false);
    const searchResults = ref([]);
    const trendingContent = ref([]);

    const addSearchResults = (results) => {
        if (searchResults.value.length > 0) searchResults.value = [];
        searchResults.value.push(...results);
        searchActive.value = true;
        console.log('search active: ', searchActive.value);
    }

return { addSearchResults, searchActive, searchResults, trendingContent }
});