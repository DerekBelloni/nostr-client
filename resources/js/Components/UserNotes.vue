<template>
    <div v-if="!notesLoading">
        <div class="divide-y divide-gray-300 border-t border-gray-300 overflow-auto">
            <div v-for="note in activeNotes">
                <div class="grid grid-cols-12 space-y-4">
                    <div class="col-span-12 mt-2 ml-2">
                        <div class="flex flex-row items-center space-x-2">
                            <img class="rounded-full h-10 w-10" :src="profilePic" alt="">
                            <span class="text-lg font-medium">{{displayName}}</span>
                        </div>
                    </div>
                    <div class="col-span-12 ml-2">
                        <span>{{note.content}}</span>
                    </div>
                    <div class="col-span-12 flex space-x-12 ml-2 pb-4">
                        <i class="pi pi-comment text-gray-500 hover:text-emerald-500 cursor-pointer" style="font-size: 1.1rem"></i>
                        <i class="pi pi-heart text-gray-500 hover:text-red-500 cursor-pointer" style="font-size: 1.1rem"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="flex justify-center ml-8">
        <ProgressSpinner></ProgressSpinner>
    </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, watch, toRef } from 'vue';
import { useNostrStore } from '@/stores/useNostrStore';

const props = defineProps(['followNotesLoading']);
let notesLoading = toRef(props, 'followNotesLoading');

const nostrStore = useNostrStore();
const profilePic = nostrStore.metadataContent.picture;
const displayName = nostrStore.metadataContent.displayName;

const activeNotes = computed(() => {
    if (nostrStore.userActiveProfile) {
        return nostrStore.userNotes;
    } else if (!nostrStore.userActiveProfile && nostrStore.followNotes) {
        return nostrStore.followNotes;
    }
})

const handleNotesLoading = () => {
    if (!nostrStore.userNotes && !nostrStore.followNotes) notesLoading.value = true;
    else notesLoading.value = false;
    console.log('notes loading', notesLoading.value)
}

watch(notesLoading, (newValue) => {
    console.log('new value in user notes: ', newValue);
    handleNotesLoading();

}, {immediate: true});

</script>

<style scoped>
</style>