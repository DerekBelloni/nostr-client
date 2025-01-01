import { defineStore } from "pinia";
import { ref } from 'vue';

export const useSearchStore = defineStore('search', () => {
    const searchResults = ref([]);
    const trendingContent = ref([]);

    const processResult = (searchedCache) => {
        return axios.post('/searched-events', {redisSearchCache: searchedCache})
            .then((response) => {
                return response.data;
            })
    }

    const addAuthorMetadata = (results) => {
        console.log('results');
    }

    const addSearchResults = (results) => {
        if (searchResults.value.length > 0) searchResults.value = [];
        // results.forEach(result => searchResults.value.push(result));
        const processedResults = processResult(results);
        console.log('processed results inside the search store: ', processedResults);
    }

return { addSearchResults, searchResults, trendingContent }
});