<template>
    <span>banana</span>
</template>

<script setup>
import { inject } from 'vue';

const recommendedRelays = inject('recommendedRelays');
</script>