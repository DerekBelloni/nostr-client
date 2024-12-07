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
                        <span @click="selectTag(hashTag.hashtag)" class="text-gray-500 font-semibold cursor-pointer hover:text-amber-500">{{hashTag.hashtag}}</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
    import { ref, defineEmits } from "vue";
    const selectedTag = ref(null);

    const props = defineProps(['trendingHashtags']);
    const emit = defineEmits(['tagSelected']);

    const startsWithHashtag = (hashTag) => {
        if (hashTag.charAt(0) === '#') return true;
        else return false;
    }

    const selectTag = (hashTag) => {
        selectedTag.value = `#${hashTag}`;
        emit('tagSelected', selectedTag.value);
    }
</script>