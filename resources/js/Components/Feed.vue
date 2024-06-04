<template>
    <div class="center-feature border-r border-gray-200">
        <div class="border-b border-gray-200">
            <div class="flex ml-6 mt-12 mr-6 items-center pb-12">
                <div class="icon-border  mr-2">
                    <i class="pi pi-user icon-color text-xl"></i>
                </div>
                <FloatLabel class="w-full">
                    <label for="feed">Say something on nostr...</label>
                    <InputText id="feed" variant="filled" class="rounded-full w-full"></InputText>
                </FloatLabel>
            </div>
        </div>
        <div>
            <ul v-for="note in notes">
                <li>{{note.content}}</li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive } from 'vue';
import InputText from 'primevue/inputtext';
import Echo from 'laravel-echo';

let notes = reactive([]);


onMounted(() => {
    window.Echo.channel('relay-notifications')
        .listen('RelayNotesReceived', (event) => {
            notes.push(event.notes);
        });
})

</script>

<style scoped>
    :root {
        --main-bg-color: #f0f0f0;
        --main-text-color: #333333;
        --border-color: 55, 65, 81; /* Define the RGB values */
        --border-opacity: 1; /* Default opacity */
    }
    .center-feature {
        width: 50%;
    }
    .icon-border {
        width: 2.5rem; /* Set the width of the container */
        height: 2.5rem; /* Set the height of the container */
        border-radius: 50%; /* Make the container circular */
        border: 2px solid rgba(55, 65, 81, 0.8); /* Set the border properties */
        display: flex; /* Use flexbox for centering the icon */
        justify-content: center; /* Center the icon horizontally */
        align-items: center; 
    }
    .icon-color {
        color: rgba(55, 65, 81, 0.8);
    }
</style>