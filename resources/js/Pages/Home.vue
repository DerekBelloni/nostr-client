<template>
    <div class="flex h-screen overflow-hidden">
        <Sidebar class="sidebar border border-r border-gray-200" @setActiveView="setActiveView" :mq-verified="mqVerified"></Sidebar>
        <div class="center-feature border-r">
            <Feed v-if="activeView == 'Home'" :trendingContent="trendingContent"></Feed>
            <Account v-if="activeView == 'account'"></Account>
            <Profile v-if="activeView == 'profile'"></Profile>
        </div>
        <div class="right-sidebar">
            <TrendingTags :trendingHashtags="trendingHashtags" @tagSelected="retrieveSearchResults"></TrendingTags>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onBeforeUnmount ,onMounted, ref, reactive, watch } from "vue";
import { router } from '@inertiajs/vue3'
import { useNostrStore } from '@/stores/useNostrStore';
import Account from '../Components/Account.vue'
import Feed from '../Components/Feed.vue'
import Profile from '../Components/Profile.vue'
import Sidebar from '../Components/Sidebar.vue'
import TrendingTags from '../Components/TrendingTags.vue'
import echo from '../echo.js';
import axios from 'axios';

const activeView = ref('');
const eventSource = ref(null);
const isSet = ref(false);
const mqVerified = ref(false);
const metadataContent = ref(null);
const nostrStore = useNostrStore();
const reactions = ref([]);
const trendingContent = ref([]);
const trendingHashtags = ref([]);


onBeforeUnmount(() => {
    if (eventSource.value) {
        eventSource.value.close();
    }
});

onMounted(() => {
    // retrieveTrendingContent();
    listenForFollowsList();
    listenForMetadata();
    listenForUserNotes();
    listenForFollowsMetadata();
    listenForSearchResults();
});

watch(metadataContent, async(newValue, oldValue) => {
    if (newValue && !oldValue) {
        verifyNIP05();
    }
}, { once: true });

// put listeners in their own file and then initialize it here
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
            console.log("event for user notes: ", event);
            if (event.userPubKey === nostrStore.hexPub) {
                retrieveUserNotes(nostrStore.hexPub);
            }
        });
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
    // echo.channel('search_results')
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

const retrieveSearchResults = (search) => {
    let publicKeyHex = nostrStore.hexPub ?? 'notLoggedIn';
    return axios.post('/rabbit-mq/search-results', {search: search, publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            console.log('search response: ', response);
        })
}

const retrieveFollowsMetadata = () => {
    return axios.post('/rabbit-mq/follows-metadata', {publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            console.log('response: ', response);
        })
}

const retrieveSetFollowsMetadata = () => {
    return axios.post('/redis/follows-metadata', {publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            nostrStore.addFollows(response.data);
        })
}

const retrieveUserNotes = () => {
    return axios.post('/redis/user-notes', {publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            // console.log('retrieve notes response: ', response);
            nostrStore.addNotes(response.data);
        })
}

const retrieveUserMetadata = () => {
    return axios.post('/redis/user-metadata', {publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            nostrStore.metadataContent = response.data.userMetadata;
            metadataContent.value = nostrStore.metadataContent;
        })
}

const retrieveTrendingContent = () => {
    return axios.get('/trending-events')
        .then((response) => {
            trendingContent.value = response.data.trending_content;
            trendingHashtags.value = response.data.trending_hashtags.hashtags;
            nostrStore.trendingHashtags = trendingHashtags.value;
        })
}

const setActiveView = (input) => {
    activeView.value = input;
}

</script>

<style scoped>
    .sidebar {
        width: 25%;
    }
    .center-feature {
        width: 45%;
    }
    .right-sidebar {
        width: 25%;
    }
</style>
