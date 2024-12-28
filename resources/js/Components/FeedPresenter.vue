<template>

</template>

<script setup>

</script>

<style>
    :root {
        --main-bg-color: #f0f0f0;
        --main-text-color: #333333;
        --border-color: 55, 65, 81; 
        --border-opacity: 1; 
    }
    .center-feature {
        width: 50%;
    }

    .nostr-content a {
        @apply text-amber-500 underline hover:text-amber-700 text-wrap;
    }
    .notes-container {
        height: 100%;
        overflow-y: auto;
        padding: 10px;
       
    }
    .rounded-video {
        border-radius: 15px;
        overflow: hidden; 
        border: none !important;
        outline: none !important;
        box-shadow: none !important; 
    }
</style>