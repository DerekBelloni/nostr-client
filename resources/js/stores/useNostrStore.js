import { defineStore } from "pinia";
import { ref } from 'vue';

export const useNostrStore = defineStore('nostr', () => {
    const hexPub = ref(null);
    const hexPriv = ref(null);
    const metadataContent = ref(null);
    const npub = ref(null);
    const searchUUID = ref(null);
    const trendingHashtags = ref([]);
    const userNotes = ref([]);
    const userFollowsContent = ref([]);
    const verified = ref(false);

    const addFollows = (follows) => {
        const existingFollows = userFollowsContent;

        follows.forEach((follow) => {
            if (existingFollows.length <= 0) {
                userFollowsContent.value.push(follow);
            }
    
            let existingFollow = existingFollows?.value.some((existing) => {
                 return existing?.pubkey == follow.pubkey;
            })

            if (!existingFollow) userFollowsContent.value.push(follow);
        })
    }

    const addNotes = (notes) => {
        const existingUserNotes = userNotes;

        notes.forEach((note) => {
            let parsedNote = JSON.parse(note);
            let existingNote = null;
            
            if (existingUserNotes.length <= 0) {
                userNotes.push(parsedNote[2]);
            }
        
            existingNote = existingUserNotes?.value.some((existing) => {
                return existing?.content == parsedNote[2]['content'];
            });
        
            if (!existingNote) userNotes.value.push(parsedNote[2]);
        })
    }

    return { addFollows, addNotes, hexPub, hexPriv, metadataContent, npub, searchUUID, trendingHashtags, userFollowsContent, userFollowsContent, userNotes, verified };
})