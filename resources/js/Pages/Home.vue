<template>
    <div class="flex h-screen">
        <Sidebar class="sidebar border border-r border-gray-200 shadow"></Sidebar>
        <Test></Test>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Test from '../Components/Test.vue'
import Sidebar from '../Components/Sidebar.vue'


</script>

<style scoped>
    .sidebar {
        width: 20%;
    }

</style>