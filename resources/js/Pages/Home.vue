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
    window.Echo.channel('relay-notifications')
        .listen('RelayNotesReceived', (event) => {
            console.log('event: ', event);
            isSet.value = event.isSet;
            if (isSet.value) {
                retrieveNotes();
            }
        });
    // retrieveNotes();
})

const retrieveNotes = () => {
    console.log('in retrieve notes');
    router.visit('/notes', {
        method: 'get',
        preserveState: true, // Prevent full page reload
        only: ['notes'], // Ensure only 'notes' is updated
        onSuccess: page => {
            notes.value = page.props.notes;
        },
        onError: errors => {
            console.error('Error fetching notes:', errors);
        }
    });
}

const setActiveView = (input) => {
    console.log("input from set view, home: ", input);
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