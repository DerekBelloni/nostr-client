<template>
    <ProfilePresenter/>
</template>

<script setup>
import { useNostrStore } from '@/stores/useNostrStore';
import ProfilePresenter from './ProfilePresenter.vue';
import { computed, provide, ref } from 'vue';

const activeTab = ref('notes');
const store = useNostrStore();

const profileMetadata = computed(() => store.activeProfile.metadata);
const userActive = computed(() => store.userActive);

const switchTab = (tab) => {
    activeTab.value = tab;
}

provide('profileMetadata', profileMetadata);
provide('profileState', {
    activeTab,
    switchTab
});
provide('userActive', userActive);
</script>