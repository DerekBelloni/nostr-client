<template>
    <div class="flex h-screen">
        <Sidebar class="sidebar border border-r border-gray-200" @setActiveView="setActiveView"></Sidebar>
        <Feed v-if="activeView == 'Home'"></Feed>
        <Account v-if="activeView == 'account'"></Account>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from "vue";
import Sidebar from '../Components/Sidebar.vue'
import Account from '../Components/Account.vue'
import Feed from '../Components/Feed.vue'

const activeView = ref(null);

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
</style>