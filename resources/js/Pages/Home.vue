<template>
    <div class="flex h-screen overflow-hidden bg-black">
        <Sidebar class="sidebar border-r border-gray-700" @setActiveView="setActiveView" :mq-verified="mqVerified"></Sidebar>
        <div class="center-feature border-r border-gray-700">
            <FeedContainer v-if="activeView == 'Home'" :trendingContent="trendingContent"></FeedContainer>
            <Account v-if="activeView == 'account'"></Account>
            <ProfileContainer v-if="activeView == 'profile'"></ProfileContainer>
            <RelayContainer v-if="activeView == 'Relays'"></RelayContainer>
        </div>
        <div class="right-sidebar">
            <TrendingTags :trendingHashtags="trendingHashtags" @tagSelected="retrieveSearchResults"></TrendingTags>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref, reactive, watch, provide } from "vue";
import { router } from '@inertiajs/vue3'
import { useNostrStore } from '@/stores/useNostrStore';
import { useSearchStore } from '@/stores/useSearchStore';
import Account from '../Components/Account.vue'
import Feed from '../Components/Feed.vue'
import Sidebar from '../Components/Sidebar.vue'
import TrendingTags from '../Components/TrendingTags.vue'
import echo from '../echo.js';
import axios from 'axios';
import ProfileContainer from '@/Components/ProfileContainer.vue';
import FeedContainer from '@/Components/FeedContainer.vue';
import RelayContainer from '@/Components/RelayContainer.vue';
import { ContentService } from '@/services/content/ContentService';

const activeView = ref('');
const eventSource = ref(null);
const isSet = ref(false);
const mqVerified = ref(false);
const metadataContent = ref(null);
const reactions = ref([]);
const trendingContent = ref([]);
const trendingHashtags = ref([]);

const nostrStore = useNostrStore();
const searchStore = useSearchStore();
const contentService = new ContentService();


onBeforeUnmount(() => {
    cleanup();
});

onMounted(() => {
    activeView.value = "Home";
    // getRelayMetadata();
    retrieveTrendingContent();
    listenForFollowsList();
    listenForMetadata();
    listenForUserNotes();
    listenForFollowsMetadata();
    listenForSearchResults();
    listenForAuthorMetadata();
    listenForNostrEntity();
});

watch(metadataContent, async(newValue, oldValue) => {
    if (newValue && !oldValue) {
        verifyNIP05();
    }
}, { once: true });

const checkFollowsList = () => {
    return axios.post('/redis/follows-list', {publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            if (response.data === 1) {
                retrieveFollowsMetadata();
            }
        })
}

const cleanup = () => {
    echo.leaveChannel('user_metadata');
    echo.leaveChannel('user_notes');
    echo.leaveChannel('follow_list');
    echo.leaveChannel('follows_metadata');
    echo.leaveChannel('search_results');
    echo.leaveChannel('author_metadata');

    nostrStore.resetStore();
    searchStore.resetStore();
}

const getRelayMetadata = () => {
    return axios.get('/relay-metadata/')
        .then((response) => {
            console.log('relay metadata response: ', response);
        })
}

const listenForNostrEntity = () => {
    echo.channel('nostr_entity')
        .listen('.nostr_entity_set', (event) => {
            if (event.entity_key == searchStore.entityUUID) {
                return axios.post('/redis/nostr-entities', {entity_key: searchStore.entityUUID, event_id: event.event_id})
                    .then((response) => {
                        searchStore.addRetrievedEntities(response.data);
                    })
            }
        });
}

const listenForAuthorMetadata = () => {
    echo.channel('author_metadata')
        .listen('.author_metadata_set', (event) => {
            let searchKey = null;
            if (nostrStore.userActive && searchStore.searchKey == event.user_pubkey) {
                searchKey = event.user_pubkey;
            } else if (event.uuid === nostrStore.searchUUID) {
                 searchKey = event.uuid;
            }
             console.log('search key before function call: ', searchKey);
            retrieveSearchCache(searchKey);
        });
}

const listenForMetadata = () => {
    echo.channel('user_metadata')
        .listen('.metadata_set', (event) => {
            if (event.userPubKey === nostrStore.hexPub) {
                retrieveUserMetadata(nostrStore.hexPub);
            }
        });
}

const listenForUserNotes = () => {
    echo.channel('user_notes')
        .listen('.user_notes_set', (event) => {
            if (event.userPubKey === nostrStore.hexPub && nostrStore.userActive) {
                retrieveUserNotes(nostrStore.hexPub);
            } else if (event.receiving_users_pubkey === nostrStore.hexPub) {
                const followsPubkey = event.userPubKey;
                const receivingUserPubkey = event.receiving_users_pubkey;
                retrieveFollowsNotes(followsPubkey);
            }
        })
}

const listenForFollowsList = () => {
    echo.channel('follow_list')
        .listen('.follow_list_set', (event) => {
            if (event.userPubKey === nostrStore.hexPub) {
                retrieveFollowsMetadata();
            }
        });
}

const listenForSearchResults = () => {
    echo.channel('search_results')
        .listen('.search_results_set', (event) => {
            let searchKey = null;
            if (nostrStore.userActive && nostrStore.hexPub == event.user_pubkey) {
                searchStore.searchKey = event.user_pubkey;
            } else if (event.uuid === nostrStore.searchUUID) {
                searchStore.searchKey = event.uuid;
             }
        });
}

const listenForFollowsMetadata = () => {
    echo.channel('follows_metadata')
        .listen('.follows_metadata_set', (event) => {
            retrieveSetFollowsMetadata();
        });
}

const retrieveSearchCache = (searchKey) => {
    return axios.post('/redis/search-results', {redisSearchKey: searchKey})
        .then((response) => {
            searchStore.addSearchResults(response.data);
        })
}

const retrieveSearchResults = (search) => {
    let searchUUID = null;

    searchUUID = crypto.randomUUID();
    nostrStore.searchUUID = searchUUID;

    return axios.post('/rabbit-mq/search-results', {search: search, publicKeyHex: nostrStore.hexPub, searchUUID: searchUUID});
}

const retrieveFollowsMetadata = () => {
    return axios.post('/rabbit-mq/follows-metadata', {publicKeyHex: nostrStore.hexPub});
}

const retrieveFollowsNotes = (followsPubkey) => {
    return axios.post('/redis/follows-notes', {publicKeyHex: followsPubkey})
        .then((response) => {
            nostrStore.setActiveProfileNotes(response.data, true);
        });
}

const retrieveSetFollowsMetadata = () => {
    return axios.post('/redis/follows-metadata', {publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            nostrStore.addFollows(response.data);
            nostrStore.setActiveProfileFollows(response.data);
        })
}

const retrieveUserNotes = () => {
    return axios.post('/redis/user-notes', {publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            nostrStore.addUserNotes(response.data);
            nostrStore.setActiveProfileNotes(response.data);
        })
}

const retrieveUserMetadata = () => {
    return axios.post('/redis/user-metadata', {publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            nostrStore.metadataContent = response.data.userMetadata;
            nostrStore.setActiveProfileMetadata(response.data.userMetadata);
            setUserMetadata();
        })
}

const parseBechContent = (trendingContent) => {
    return axios.post('/bech/parse-notes', {trendingContent: trendingContent})
        .then((response) => {
            searchStore.addParsedEntites(response.data);
        })
        .finally((response) => {
            retrieveEmbeddedEntities()
        })
}

const retrieveTrendingContent = () => {
    let parsedEntities = null;
    
    return axios.get('/trending-events')
        .then((response) => {
            trendingContent.value = response.data.trending_content;
            parseBechContent(trendingContent.value);
            processContent(trendingContent.value);
            trendingHashtags.value = response.data.trending_hashtags.hashtags;
            nostrStore.trendingHashtags = trendingHashtags.value;
        })
}

const retrieveEmbeddedEntities = () => {
    const entityUUID = crypto.randomUUID();
    searchStore.entityUUID = entityUUID;

   return axios.post('/bech/retrieve-entities', {entityUUID: entityUUID, entities: searchStore.parsedEntities})
       .then((response) => {
           console.log('banana')
       })
}

const processContent = async (trendingContent) => {
    // trendingContent.forEach((c, index) => {
    //     c['blocks'] = await contentService.processContent(c);
    //     searchStore.trendingContent.push({...c});
    // });
    for (const [index, c] of trendingContent.entries()) {
        c['blocks'] = await contentService.processContent(c);
        searchStore.trendingContent.push({ ...c });
    }
}

const setUserMetadata = () => {
    metadataContent.value = nostrStore.metadataContent;

}

const clearSearchCache = (searchKey) => {
    return axios.post('/redis/clear-search-cache', {searchKey: searchKey})
        .then((response) => {
            console.log('response from clearing cache: ', response);
        })
}

const setActiveView = (input) => {
    activeView.value = input;
    if (activeView.value == 'Home') {
        const searchKey = searchStore.searchKey;
        searchStore.clearSearchResults();
        clearSearchCache(searchKey);
    }
}

const verifyNIP05 = () => {
    return axios.post('/nip05-verification', {metadataContent: nostrStore.metadataContent, publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            nostrStore.verified = response.data.verified;
        })
}

provide('setUserMetadata', setUserMetadata);
provide('retrieveUserNotes', retrieveUserNotes);
provide('checkFollowsList', checkFollowsList);

</script>

<style scoped>
    .sidebar {
        width: 20%;
    }
    .center-feature {
        width: 55%;
    }
    .right-sidebar {
        width: 20%;
    }
</style>
