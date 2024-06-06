<template>
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
    <div class="notes-container">
        <ul v-for="note in props.notes" >
            <li class="flex flex-col border-b border-gray-300 py-2 px-2 my-2">
                <span>{{note.pubkey}}</span>
                <span class="font-medium">{{note.content}}</span>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import InputText from 'primevue/inputtext';

const props = defineProps(['notes']);
const notes = ref(props.notes);

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
    .notes-container {
        overflow-y: auto;
    }
</style>