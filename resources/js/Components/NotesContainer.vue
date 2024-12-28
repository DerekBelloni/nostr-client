<template>
    <NotesPresenter/>
</template>

<script setup>
import { useNostrStore } from '@/stores/useNostrStore';
import { computed, provide } from 'vue';
import NotesPresenter from './NotesPresenter.vue';

const store = useNostrStore();

const displayName = computed(() => store.activeProfile.metadata.display_name || store.activeProfile.metadata.name);
const profileNotes = computed(() => store.activeProfile.notes);
const profilePic = computed(() => store.activeProfile.metadata.picture);

provide('displayName', displayName);
provide('profileNotes', profileNotes);
provide('profilePic', profilePic);
</script>