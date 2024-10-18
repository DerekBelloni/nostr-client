<template>
    <div class="mt-12 m-4">
        <span class="font-semibold text-gray-700 text-md">Trending Hashtags</span>
        <div>
            <ul v-for="hashTag in props.trendingHashtags">
                <li class="ml-2">
                    <div class="flex flex-row space-x-1 items-center">
                        <span class="font-semibold text-gray-500 text-lg">#</span>
                        <span class="text-gray-500">{{hashTag.hashtag}}</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
    const props = defineProps(['trendingHashtags']);

</script>