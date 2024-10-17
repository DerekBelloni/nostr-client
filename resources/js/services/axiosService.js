const verifyNIP05 = () => {
    return axios.post('/nip05-verification', {metadataContent: nostrStore.metadataContent, publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            nostrStore.verified = response.data.verified;
        })
}

const retrieveFollowsMetadata = () => {
    return axios.post('/rabbit-mq/follows-metadata', {publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            console.log('response: ', response);
            
        })
}

const retrieveUserNotes = () => {
    return axios.post('/redis/user-notes', {publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            console.log('retrieve notes: response: ', response);
            addUserNotesToStore(response.data);
        })
}

const retrieveUserMetadata = () => {
    return axios.post('/redis/user-metadata', {publicKeyHex: nostrStore.hexPub})
        .then((response) => {
            nostrStore.metadataContent = response.data.userMetadata;
            metadataContent.value = nostrStore.metadataContent;
        })
}

const retrieveNotes = () => {
    return axios.get('/trending-events')
        .then((response) => {
            trendingContent.value = response.data;
        })
}