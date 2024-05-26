<template>
    <div class="flex h-screen">
        <Sidebar class="sidebar border border-r border-gray-200 shadow"></Sidebar>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Sidebar from '../Components/Sidebar.vue'


</script>

<style scoped>
    .sidebar {
        width: 20%;
    }

</style>