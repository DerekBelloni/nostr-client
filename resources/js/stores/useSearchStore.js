import { defineStore } from "pinia";
import { ref } from 'vue';

export const useSearchStore = defineStore('search', () => {
    const entityUUID = ref("");
    const parsedEntities = ref([]);
    const searchActive = ref(false);
    const searchKey = ref(null);
    const searchResults = ref([]);
    const trendingContent = ref([]);
    const newContent = ref([]);

    const resetStore = () => {
        searchActive.value = false,
        searchResults.value = [];
    }

    const addSearchResults = (results) => {
        if (searchResults.value.length > 0) searchResults.value = [];
        searchResults.value.push(...results);
        searchActive.value = true;
    }

    const addParsedEntites = (entities) => {
        if (parsedEntities.value.length > 0) parsedEntities.value = [];
        parsedEntities.value.push(...entities);
    }

    const clearSearchResults = () => {
        searchResults.value = [];
        searchActive.value = false;
    }

return { addParsedEntites, addSearchResults, clearSearchResults, entityUUID, newContent, parsedEntities, resetStore, searchActive, searchKey, searchResults, trendingContent }
});