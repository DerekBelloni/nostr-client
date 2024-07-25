import { defineStore } from "pinia";
import { ref } from 'vue';

export const useNostrStore = defineStore('nostr', () => {
    const npub = ref(null);
    const hexPub = ref(null);
    const verified = ref(false);

    return { hexPub, npub, verified };
})