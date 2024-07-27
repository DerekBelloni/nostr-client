import { defineStore } from "pinia";
import { ref } from 'vue';

export const useNostrStore = defineStore('nostr', () => {
    const metadataContent = ref(null);
    const npub = ref(null);
    const hexPub = ref(null);
    const verified = ref(false);

    return { metadataContent, hexPub, npub, verified };
})