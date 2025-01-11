<template>
    <RelayPresenter />
</template>

<script setup>
import { provide } from 'vue';
import RelayPresenter from './RelayPresenter.vue';

const recommendedRelays = [
    'wss://relay.damus.io',
    'wss://nostr.wine',
    "wss://relay.primal.net",
    "wss://relay.nostr.band",
];

provide('recommendedRelays', recommendedRelays);
</script>