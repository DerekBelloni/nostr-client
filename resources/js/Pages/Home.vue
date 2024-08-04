<template>
    <div class="flex h-screen overflow-hidden">
        <Sidebar class="sidebar border border-r border-gray-200" @setActiveView="setActiveView"></Sidebar>
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
import { onMounted, ref, reactive } from "vue";
import { router } from '@inertiajs/vue3'
import Account from '../Components/Account.vue'
import Feed from '../Components/Feed.vue'
import Profile from '../Components/Profile.vue'
import Sidebar from '../Components/Sidebar.vue'
import TrendingTags from '../Components/TrendingTags.vue'

const activeView = ref('');
const isSet = ref(false);
const trendingContent = ref([]);
const reactions = ref([]);

onMounted(() => {
    retrieveNotes();
});

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
        width: 50%;
    }
    .right-sidebar {
        width: 25%;
    }
</style>