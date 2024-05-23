<template>
    <div class="flex h-screen">
        <div class="sidebar border border-r border-gray-200 shadow">
            <div class="flex flex-col space-y-8">
                <div class="mt-12 flex ml-16">
                    <h3 class="text-rose-500 font-semibold text-2xl">Logo</h3>
                </div>
                <div>
                    <ul class="ml-16 space-y-8">
                        <li>Menu</li>
                        <li>Explore</li>
                        <li>Messages</li>
                        <li>Relays</li>
                        <li>Bookmarks</li>
                        <li>Notifications</li>
                        <li>Downloads</li>
                        <li>Settings</li>
                    </ul>
                </div>
                <div class="ml-16">
                    <div>
                        <span class="text-xs ml-2">Welcome to nostr!</span>
                    </div>
                    <button class="rounded-full bg-rose-500 px-4 py-1 text-white font-semibold">Get Started</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

</script>

<style scoped>
    .sidebar {
        width: 20%;
    }

</style>