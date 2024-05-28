<template>
    <div class="flex h-screen">
        <Sidebar class="sidebar border border-r border-gray-200 "></Sidebar>
        <Account></Account>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Sidebar from '../Components/Sidebar.vue'
import Account from '../Components/Account.vue'

</script>

<style scoped>
    .sidebar {
        width: 22%;
    }
    .center-feature {
        width: 50%;
    }
</style>