<template>
    <div class="flex h-screen overflow-hidden">
        <Sidebar class="sidebar border border-r border-gray-200" @setActiveView="setActiveView"></Sidebar>
        <div class="center-feature border-r">
            <Feed v-if="activeView == 'Home'" :notes="notes"></Feed>
            <Account v-if="activeView == 'account'"></Account>
        </div>
        <div class="right-sidebar">
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref, reactive } from "vue";
import { router } from '@inertiajs/vue3'
import Echo from 'laravel-echo';
import Sidebar from '../Components/Sidebar.vue'
import Account from '../Components/Account.vue'
import Feed from '../Components/Feed.vue'

const activeView = ref(null);
const notes = ref([]);
const isSet = ref(false);

onMounted(() => {
    retrieveNotes();
})

const retrieveNotes = () => {
    router.visit('/notes', {
        method: 'get',
        preserveState: true,
        only: ['notes'],
        onSuccess: page => {
            notes.value = page.props.notes;
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