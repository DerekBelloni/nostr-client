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
    listenForMetadata();
    listenForUserNotes();
});

watch(metadataContent, async(newValue, oldValue) => {
    if (newValue && !oldValue) {
        console.log('in watcher: ', metadataContent);
        verifyNIP05();
    }
}, { once: true });

// put listeners in their own file and then initialize it here
const listenForMetadata = () => {
    echo.channel('user_metadata')
        .listen('.metadata_set', (event) => {
            console.log("event: ", event);
            toast.add({ severity: 'success', summary: 'Info', detail: 'Metadata Retrieved', life: 3000 });
            if (event.userPubKey === nostrStore.hexPub) {
                retrieveUserMetadata(nostrStore.hexPub);
            }
        })
}

const listenForUserNotes = () => {
    echo.channel('user_notes') 
        .listen('.user_notes_retrieved', (event) => {
            toast.add({ severity: 'info', summary: 'Info', detail: 'Notes Retrieved', life: 3000 });
            nostrStore.userNotes.push(event.usernotes);
        })
}

const listenForFollowsList = () => {
    //
}

const verifyNIP05 = () => {
    console.log("banana")
    router.post('/nip05-verification', {metadataContent: nostrStore.metadataContent, publicKeyHex: nostrStore.hexPub}, {
        preserveState: true,
        only: ['verified'],
        onSuccess: page => {
            console.log('verified props: ', page.props.verified)
            nostrStore.verified = page.props.verified;
            router.replace('/'); 
        },
        onError: errors => {
            console.error('Error fetching notes:', errors);
        }
    })
}

const retrieveUserMetadata = () => {
    router.post('/redis/user-metadata', {publicKeyHex: nostrStore.hexPub}, {
        method: 'post',
        preserveState: true,
        only: ['userMetadata'],
        onSuccess: page => {
            nostrStore.metadataContent = page.props.userMetadata;
            console.log('nostr metadata content: ', nostrStore.metadataContent)
            metadataContent.value = nostrStore.metadataContent;
            router.replace('/'); 
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