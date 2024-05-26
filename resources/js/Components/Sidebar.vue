<template>
    <div class="flex flex-col space-y-12">
        <div class="mt-12 flex ml-16">
            <img class="logo" src="/bitcoin-btc-logo-full.svg"/>
        </div>
        <div>
            <ul class="ml-16 space-y-10">
                <div v-for="item in sidebarItems">
                    <div class="hover:border-b-2 hover:border-amber-500 border-b-2 border-transparent hover:inline-flex">
                        <li class="cursor-pointer"><i :class="item.icon" class="mr-2"></i>{{item.text}}</li>
                    </div>
                </div>
            </ul>
        </div>
        <div class="ml-16">
            <div>
                <span class="text-xs ml-2">Welcome to nostr!</span>
            </div>
            <Button label="Get Started" @click="openAccountDialog" class="rounded-full px-4 py-1 text-white font-semibold"/>
        </div>
        <AccountDialog ref="accountDialog"></AccountDialog>
    </div>
</template>

<script setup>
import { ref } from "vue";
import Button from "primevue/button";
import AccountDialog from '../Components/AccountDialog.vue';
import sidebarItems from "@/Data/SidebarData";

const accountDialog = ref(null);

const openAccountDialog = () => {
    accountDialog.value.open();
}

</script>

<style scoped>
    .logo {
        width: 75%;
        height: auto;
    }
</style>