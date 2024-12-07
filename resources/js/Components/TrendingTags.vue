<template>
    <div class="mt-12 m-4">
        <span class="font-semibold text-gray-700 text-md">Trending Hashtags</span>
        <div>
            <ul v-for="hashTag in props.trendingHashtags">
                <li class="ml-2">
                    <div class="flex flex-row items-center">
                        <div v-if="!startsWithHashtag(hashTag.hashtag)">
                            <span class="font-semibold text-gray-500 text-lg cursor-pointer hover:text-amber-500">#</span>
                        </div>
                        <span @click="retrieveHashTagNotes(hashTag.hashtag)" class="text-gray-500 font-semibold cursor-pointer hover:text-amber-500">{{hashTag.hashtag}}</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
    const props = defineProps(['trendingHashtags']);
    const emit = defineEmits(['hashTagNotesRetrieved']);

    const startsWithHashtag = (hashTag) => {
        if (hashTag.charAt(0) === '#') return true;
        else return false;
    }

    const retrieveHashTagNotes = (hashTag) => {
        console.log('hashTag: ', hashTag);
        return axios.get('/trending-hashtags')
            .then((response) => {
                console.log('response: ', response);
            })
    }
</script>