import { defineStore } from "pinia";
import { ref } from 'vue';

export const useNostrStore = defineStore('nostr', () => {
    const npub = ref(null);
    const hexPub = ref(null);

    return { hexPub, npub };
})