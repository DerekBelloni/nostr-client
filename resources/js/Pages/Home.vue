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
import { onBeforeUnmount ,onMounted, ref, reactive } from "vue";
import { router } from '@inertiajs/vue3'
import { useNostrStore } from '@/stores/useNostrStore';
import { useToast } from 'primevue/usetoast';
import Account from '../Components/Account.vue'
import Feed from '../Components/Feed.vue'
import Profile from '../Components/Profile.vue'
import Sidebar from '../Components/Sidebar.vue'
import TrendingTags from '../Components/TrendingTags.vue'
import echo from '../echo.js';

const activeView = ref('');
const eventSource = ref(null);
const isSet = ref(false);
const mqVerified = ref(false);
const nostrStore = useNostrStore();
const reactions = ref([]);
const toast = useToast();
const trendingContent = ref([]);

onMounted(() => {
    // retrieveNotes();
    listenForMetadata();
    listenForUserNotes();
});

onBeforeUnmount(() => {
    if (eventSource.value) {
        eventSource.value.close();
    }
})

// Maybe create a listeners service that just gets initialized here
const listenForMetadata = () => {
    echo.channel('user_metadata')
        .listen('.metadata_set', (event) => {
            toast.add({ severity: 'success', summary: 'Info', detail: 'Metadata Retrieved', life: 3000 });
            nostrStore.metadataContent = event.metadata;
            verifyNIP05();
        })
}

const listenForUserNotes = () => {
    echo.channel('user_notes') 
        .listen('.user_notes_retrieved', (event) => {
            toast.add({ severity: 'info', summary: 'Info', detail: 'Notes Retrieved', life: 3000 });
            console.log('user notes event: ', event);
            nostrStore.userNotes.push(event.usernotes);
        })
}

const listenForFollowsList = () => {
    //
}

const verifyNIP05 = () => {
    router.post('/nip05-verification', {metadataContent: nostrStore.metadataContent, publicKeyHex: nostrStore.hexPub}, {
        preserveState: true,
        only: ['verified'],
        onSuccess: page => {
            nostrStore.verified = page.props.verified;
            router.replace('/'); 
        },
        onError: errors => {
            console.error('Error fetching notes:', errors);
        }
    })
}

const retrieveNotes = () => {
    router.visit('/trending-events', {
        method: 'get',
        preserveState: true,
        only: ['trendingContent'],
        onSuccess: page => {
            trendingContent.value = page.props.trendingContent;
            router.replace('/'); 
        },
        onError: errors => {
            console.error('Error fetching notes:', errors);
        }
    });
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