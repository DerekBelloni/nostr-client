import { defineStore } from "pinia";
import { ref } from 'vue';

export const useNostrStore = defineStore('nostr', () => {
    const hexPub = ref(null);
    const hexPriv = ref(null);
    const metadataContent = ref(null);
    const npub = ref(null);
    const userNotes = ref(null);
    const verified = ref(false);

    return { hexPub, hexPriv, metadataContent, npub, userNotes, verified };
})