<template>
    <div class="flex h-screen overflow-hidden">
        <!-- <Sidebar class="sidebar border border-r border-gray-200" @setActiveView="setActiveView" @pubKeyRetrieved="pubKeyRetrieved"></Sidebar> -->
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
    retrieveNotes();
    setUpEcho();
});

onBeforeUnmount(() => {
    if (eventSource.value) {
        eventSource.value.close();
    }
})

const setUpEcho = () => {
    echo.channel('user_metadata')
        .listen('.metadata_set', (event) => {
            toast.add({ severity: 'success', summary: 'Info', detail: 'Metadata Retrieved', life: 3000 });
            nostrStore.metadataContent = event.metadata;
            mqVerified.value = true;
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

// // will become a composable
// function setupSSE() {
//     eventSource.value = new EventSource(`/sse?pubHexKey=${nostrStore.hexPub}`);

//     eventSource.value.onmessage = (event) => {
//         console.log("[Server side event]: ", event);
//     }

//     eventSource.value.onerror = (error) => {
//         console.error('[Server side event error]: ', error);
//         eventSource.value.close();
//     }

// }


</script>

<style scoped>
    .sidebar {
        width: 25%;
    }
    .center-feature {
        width: 50%;
    }
    .right-sidebar {
        width: 25%;
    }
</style>