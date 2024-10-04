<template>
    <div class="flex h-screen overflow-hidden">
        <Sidebar class="sidebar border border-r border-gray-200" @setActiveView="setActiveView" :mq-verified="mqVerified"></Sidebar>
        <div class="center-feature border-r">
            <Feed v-if="activeView == 'Home'" :trendingContent="trendingContent"></Feed>
            <Account v-if="activeView == 'account'"></Account>
            <Profile v-if="activeView == 'profile'"></Profile>
        </div>
        <div class="right-sidebar">
            <TrendingTags></TrendingTags>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onBeforeUnmount ,onMounted, ref, reactive, watch } from "vue";
import { router } from '@inertiajs/vue3'
import { useNostrStore } from '@/stores/useNostrStore';
import { useToast } from 'primevue/usetoast';
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
const toast = useToast();
const trendingContent = ref([]);

onBeforeUnmount(() => {
    if (eventSource.value) {
        eventSource.value.close();
    }
});

onMounted(() => {
    retrieveNotes();
    listenForFollowsList();
    listenForMetadata();
    listenForUserNotes();
});

watch(metadataContent, async(newValue, oldValue) => {
    if (newValue && !oldValue) {
        verifyNIP05();
        console.log('banana')
    }
}, { once: true });

// put listeners in their own file and then initialize it here
const listenForMetadata = () => {
    echo.channel('user_metadata')
        .listen('.metadata_set', (event) => {
            if (event.userPubKey === nostrStore.hexPub) {
            console.log("bananan!")
                retrieveUserMetadata(nostrStore.hexPub);
            }
        })
}

const listenForUserNotes = () => {
    echo.channel('user_notes') 
        .listen('.user_notes_retrieved', (event) => {
            if (event.userPubKey === nostrStore.hexPub) {
                toast.add({ severity: 'info', summary: 'Info', detail: 'Notes Retrieved', life: 3000 });
                nostrStore.userNotes.push(event.usernotes);
            }
        })
}

const listenForFollowsList = () => {
    echo.channel('follow_list')
        .listen('.follow_list_set', (event) => {
            if (event.userPubKey === nostrStore.hexPub) {
                toast.add({ severity: 'contrast', summary: 'Info', detail: 'Follow List Retrieved', life: 3000 });
                retrieveFollowsMetadata();
            }
        })
        .error((error) => {console.error("Error in the follow list listener")});
}

// Convert to axios
const verifyNIP05 = () => {
    return axios.post('/nip05-verification', {metadataContent: nostrStore.metadataContent, publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            nostrStore.verified = response.data.verified;
        })
}

const retrieveFollowsMetadata = () => {
    return axios.post('/rabbit-mq/follows-metadata', {publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            console.log('response: ', response);
        })
}

const retrieveUserMetadata = () => {
    return axios.post('/redis/user-metadata', {publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            nostrStore.metadataContent = response.data.userMetadata;
            metadataContent.value = nostrStore.metadataContent;
        })
}

const retrieveNotes = () => {
    return axios.get('/trending-events')
        .then((response) => {
            trendingContent.value = response.data;
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