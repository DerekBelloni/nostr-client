import { defineStore } from "pinia";
import { ref } from 'vue';

export const useNostrStore = defineStore('nostr', () => {
    const activeProfile = ref({
        follows: [],
        metadata: [],
        notes: []
    });

    const followMetadataContent = ref(null);
    const followNotes = ref([]);
    const hexPub = ref(null);
    const hexPriv = ref(null);
    const metadataContent = ref(null);
    const npub = ref(null);
    const searchUUID = ref(null);
    const trendingHashtags = ref([]);
    const userActiveProfile = ref(false);
    const userFollowsContent = ref([]);
    const userNotes = ref([]);
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

    // actions regarding setting the user as active profile, setting an active follows, etc should be handled through functionality in here
    const addFollowsNotes = (notes) => {
        const existingFollowNotes = followNotes;

        notes.forEach((note) => {
            let existingNote = null;
            const parsedNote = JSON.parse(note);
            if (followNotes.length <= 0) followNotes.push(parsedNote[2]);

            existingNote = existingFollowNotes?.value.some((existing) => {
                return existing?.content == parsedNote[2]['content'];
            });

            if (!existingNote) followNotes.value.push(parsedNote[2]);
        });
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
    const clearActiveProfile = () => {
        // iterate through the active profile object
        // set each array to empty
    }


    const setActiveProfileMetadata = (metadata) => {
        activeProfile.value.metadata = metadata;
    }

    const setActiveProfileNotes = (notes) => {
        const existingProfileNotes = activeProfile.value.notes;

        notes.forEach((note) => {
            let parsedNote = JSON.parse(note);
            let existingNote = null;

            if (existingProfileNotes.length <= 0) {
                activeProfile.value.notes.push(parsedNote);
            }

            existingNote = existingProfileNotes?.some((existingNote) => {
                return existingNote.content == parsedNote[2]["content"];
            });

            if (!existingNote) activeProfile.value.notes.push(parsedNote[2]);
        });
    }

    const setActiveProfileFollows = (follows) => {
        activeProfile.value.follows = follows;
    }
    
    return { addFollows, addFollowsNotes, addNotes, followMetadataContent, followNotes, hexPub, hexPriv, metadataContent, npub, searchUUID, setActiveProfileMetadata, trendingHashtags, userActiveProfile, userFollowsContent, userFollowsContent, userNotes, verified };
})