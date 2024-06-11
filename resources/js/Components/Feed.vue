<template>
    <FeedSearch></FeedSearch>
    <div class="notes-container">
        <ul v-for="note in props.notes" >
            <li class="flex flex-col border-b border-gray-300 py-2 px-2 my-2">
                <span>{{note.pubkey}}</span>
                <div class="text-wrap truncate">
                    <span class="font-medium">{{note.content}}</span>
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import InputText from 'primevue/inputtext';
import FeedSearch from './FeedSearch.vue';

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

    .notes-container {
        height: 100%;
        overflow-y: auto;
        padding: 10px;
        overflow-x: hidden;
    }
</style>