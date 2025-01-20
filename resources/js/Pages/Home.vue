<template>
    <div class="flex h-screen overflow-hidden">
        <Sidebar class="sidebar border border-r border-gray-200" @setActiveView="setActiveView" :mq-verified="mqVerified"></Sidebar>
        <div class="center-feature border-r">
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
import { onBeforeUnmount, onMounted, ref, reactive, watch } from "vue";
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


onBeforeUnmount(() => {
    cleanup();
});

onMounted(() => {
    activeView.value = "Home";
    retrieveTrendingContent();
    listenForFollowsList();
    listenForMetadata();
    listenForUserNotes();
    listenForFollowsMetadata();
    listenForSearchResults();
    listenForAuthorMetadata();
});

watch(metadataContent, async(newValue, oldValue) => {
    if (newValue && !oldValue) {
        verifyNIP05();
    }
}, { once: true });

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

const listenForAuthorMetadata = () => {
    echo.channel('author_metadata')
        .listen('.author_metadata_set', (event) => {
            let searchKey = null;
            if (nostrStore.userActive && nostrStore.hexPub == event.user_pubkey) {
                searchKey = event.user_pubkey;
            } else if (event.uuid === nostrStore.searchUUID) {
                 searchKey = event.uuid;
             }

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
                searchKey = event.user_pubkey;
            } else if (event.uuid === nostrStore.searchUUID) {
                 searchKey = event.uuid;
             }
        });
}

const listenForFollowsMetadata = () => {
    echo.channel('follows_metadata')
        .listen('.follows_metadata_set', (event) => {
            retrieveSetFollowsMetadata();
        });
}

const verifyNIP05 = () => {
    return axios.post('/nip05-verification', {metadataContent: nostrStore.metadataContent, publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            nostrStore.verified = response.data.verified;
        })
}

const retrieveSearchCache = (searchKey) => {
    return axios.post('/redis/search-results', {redisSearchKey: searchKey})
        .then((response) => {
            searchStore.addSearchResults(response.data);
        })
}

const retrieveSearchResults = (search) => {
    let searchUUID = null;
    if (!nostrStore.hexPub) {
        searchUUID = crypto.randomUUID();
        nostrStore.searchUUID = searchUUID;
    }
   
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
            console.log('response data for notes: ', response.data);
            nostrStore.addUserNotes(response.data);
            nostrStore.setActiveProfileNotes(response.data);
        })
}

const retrieveUserMetadata = () => {
    return axios.post('/redis/user-metadata', {publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            nostrStore.metadataContent = response.data.userMetadata;
            nostrStore.setActiveProfileMetadata(response.data.userMetadata);
            metadataContent.value = nostrStore.metadataContent;
        })
}

const retrieveTrendingContent = () => {
    return axios.get('/trending-events')
        .then((response) => {
            trendingContent.value = response.data.trending_content;
            searchStore.trendingContent = response.data.trending_content;
            trendingHashtags.value = response.data.trending_hashtags.hashtags;
            nostrStore.trendingHashtags = trendingHashtags.value;
        })
}

const setActiveView = (input) => {
    activeView.value = input;
    if (activeView.value == 'Home') {
        searchStore.clearSearchResults();
    }
}

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
