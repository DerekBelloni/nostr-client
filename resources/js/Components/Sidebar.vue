<template>
    <div class="flex flex-col space-y-10">
        <div class="mt-12 flex ml-16">
            <Logo></Logo>
        </div>
        <div>
            <ul class="ml-20 space-y-5">
                <div v-for="item in sidebarItems">
                    <div class="hover:border-b-2 hover:border-amber-500 border-b-2 border-transparent hover:inline-flex">
                        <li @click="setActiveView(item.text)" class="cursor-pointer font-semibold text-lg"><i :class="item.icon" class="mr-2"></i>{{item.text}}</li>
                    </div>
                </div>
            </ul>
        </div>
        <div class="ml-16" v-if="!npub || !nip05Verified">
            <div>
                <span class="text-xs ml-2">Welcome to nostr!</span>
            </div>
            <Button label="Get Started" @click="openAccountDialog" class="rounded-full px-4 py-1 text-white font-semibold"/>
        </div>
        <div class="truncate px-8 bg-gray-100 border border-gray-200 rounded-full mx-12 cursor-pointer" v-if="npub && !nip05Verified">
            <a @click="setActiveView('profile')">
                <span>{{nostrStore.npub}}</span>
            </a>
        </div>
        <div class="mx-16" v-else-if="npub && nip05Verified">
            <a @click="setActiveView('profile')" class="items-center space-x-2 cursor-pointer profile-container hover:bg-gray-200 hover:rounded-full hover:mr-16">
                <img :src="nostrStore.metadataContent.picture" class="profile-picture">
                <span class="font-semibold">{{nostrStore.metadataContent.name}}</span>
            </a>
        </div>
        <div class="mx-16">
            <Button @click="openNoteDialog" label="+ Note" class="rounded-full px-2 py-1 font-semibold"></Button>
        </div>
        <AccountDialog ref="accountDialog" @setActiveView="setActiveView"></AccountDialog>
        <NoteDialog ref="noteDialog"></NoteDialog>
    </div>
</template>

<script setup>
import {  computed, defineEmits, ref, watch, } from "vue";
import { useNostrStore } from '@/stores/useNostrStore';
import AccountDialog from '../Components/AccountDialog.vue';
import NoteDialog from '../Components/NoteDialog.vue';
import Logo from '../Components/Logo.vue';
import sidebarItems from "@/Data/SidebarData";

const activeView = ref(null);
const accountDialog = ref(null);
const noteDialog = ref(null);
const nostrStore = useNostrStore();

const props = defineProps(['mqVerified'])

const npub = computed(() => nostrStore.npub);
const nip05Verified = computed(() => nostrStore.verified);

const emit = defineEmits(['setActiveView', 'pubKeyRetrieved']);

const openAccountDialog = () => {
    accountDialog.value.open();
}

const openNoteDialog = () => {
    noteDialog.value.open();
}

function setActiveView(item) {
    activeView.value = item;
    nostrStore.followMetadataContent = null;
    nostrStore.userActiveProfile = true;
    emit('setActiveView', activeView.value);
}


watch(() => props.mqVerified, (newValue) => {
  if (newValue) {
      nostrStore.verified = true;
  }
});

</script>

<style scoped>
    .profile-container {
        display: flex;
        align-items: center;
    }
    .profile-picture {
        width: 50px;
        height: 50px;
        border: 2px solid #f59e0b;
        border-radius: 50%;
    }
</style>