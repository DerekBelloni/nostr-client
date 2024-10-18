import { defineStore } from "pinia";
import { ref } from 'vue';

export const useNostrStore = defineStore('nostr', () => {
    const hexPub = ref(null);
    const hexPriv = ref(null);
    const metadataContent = ref(null);
    const npub = ref(null);
    const trendingHashtags = ref([]);
    const userNotes = ref([]);
    const userFollows = ref([]);
    const verified = ref(false);

    const addNotes = (notes) => {
        const existingUserNotes = userNotes;

        notes.forEach((note) => {
            let parsedNote = JSON.parse(note);
            let existingNote = null;
            
            if (existingUserNotes.length <= 0) {
                userNotes.push(parsedNote[2]);
            }
        
            existingNote = existingUserNotes?.value.some((existing) => {
                return existing?.id == parsedNote[2]['id'];
            });
        
            if (!existingNote) userNotes.value.push(parsedNote[2]);
        })
    }

    return { addNotes, hexPub, hexPriv, metadataContent, npub, trendingHashtags, userFollows, userNotes, verified };
})