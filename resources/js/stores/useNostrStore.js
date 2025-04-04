import { defineStore } from "pinia";
import { isProxy, ref, toRaw } from 'vue';

export const useNostrStore = defineStore('nostr', () => {
    const activeProfile = ref({
        follows: [],
        metadata: [],
        notes: []
    });
    const hexPub = ref(null);
    const hexPriv = ref(null);
    const metadataContent = ref(null);
    const npub = ref(null);
    const npubMetadataUUID = ref(null);
    const searchUUID = ref(null);
    const trendingHashtags = ref([]);
    const userFollowsContent = ref([]);
    const userActive = ref(false);
    const userNotes = ref([]);
    const verified = ref(false);

    const resetStore = () => {
        hexPub.value = null
        hexPriv.value = null
        metadataContent.value = null
        npub.value = null
        npubMetadataUUID.value = null
        searchUUID.value = null
        trendingHashtags.value = []
        userFollowsContent.value = []
        userActive.value = false
        userNotes.value = []
        verified.value = false
     }

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

    const addUserNotes = (notes) => {
        const existingUserNotes = userNotes;

        notes.forEach((note) => {
            let parsedNote = typeof note === 'string' ? JSON.parse(note) : note;
            let existingNote = null;
            
            if (existingUserNotes.length <= 0) {
                userNotes.push(parsedNote);
            }
        
            existingNote = existingUserNotes?.value.some((existing) => {
                return existing?.content == parsedNote['content'];
            });
        
            if (!existingNote) userNotes.value.push(parsedNote);
        })
    }
    
    const clearActiveProfile = () => {
        for (const key in activeProfile.value) {
            activeProfile.value[key] = [];
        }
    }

    const setActiveProfileMetadata = (metadata) => {
        const unwrappedMeta = isProxy(metadata) ? toRaw(metadata) : metadata;

        if (activeProfile.value.metadata.pubkey != metadata.pubkey) {
            userActive.value = false;
            clearActiveProfile();
        }

        if (metadata.pubkey === hexPub.value) {
            userActive.value = true;
            if (userNotes.value.length > 0) activeProfile.value.notes = userNotes.value;
            if (userFollowsContent.value.length > 0) activeProfile.value.follows = userFollowsContent.value;
        }

        activeProfile.value.metadata = unwrappedMeta;
    }

    const setActiveProfileNotes = (notes, follows = false) => {
        const existingProfileNotes = activeProfile.value.notes;

            notes.forEach((note) => {
                let parsedNote = typeof note === 'string' ? JSON.parse(note) : note;
                let existingNote = null;
                
                if (existingProfileNotes.length <= 0) {
                    activeProfile.value.notes.push(parsedNote);
                }
    
                existingNote = existingProfileNotes?.some((existingNote) => {
                    return existingNote?.content == parsedNote["content"];
                });
    
                if (!existingNote) activeProfile.value.notes.push(parsedNote);
            });
    }

    const setActiveProfileFollows = (follows) => {
        activeProfile.value.follows = follows;
    }
    
    return { activeProfile, addFollows, addUserNotes, clearActiveProfile, hexPub, hexPriv, metadataContent, npub, npubMetadataUUID, resetStore, searchUUID, setActiveProfileFollows, setActiveProfileMetadata, setActiveProfileNotes, trendingHashtags, userActive, userFollowsContent, userFollowsContent, userNotes, verified };
})