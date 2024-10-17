import echo from '../echo.js';

export const listenForMetadata = () => {
    echo.channel('user_metadata')
        .listen('.metadata_set', (event) => {
            if (event.userPubKey === nostrStore.hexPub) {
                retrieveUserMetadata(nostrStore.hexPub);
            }
        })
}

 export const listenForUserNotes = () => {
    echo.channel('user_notes') 
        .listen('.user_notes_set', (event) => {
            if (event.userPubKey === nostrStore.hexPub) {
                retrieveUserNotes(nostrStore.hexPub);
                console.log('banana', event)
            }
        })
}

export const listenForFollowsList = () => {
    echo.channel('follow_list')
        .listen('.follow_list_set', (event) => {
            if (event.userPubKey === nostrStore.hexPub) {
                retrieveFollowsMetadata();
            }
        })
}