import { defineStore } from "pinia";
import { ref } from 'vue';

export const useNostrStore = defineStore('nostr', () => {
    const npub = ref(null);

    return { npub };
})