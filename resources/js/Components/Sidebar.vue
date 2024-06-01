<template>
    <div class="flex flex-col space-y-10">
        <div class="mt-12 flex ml-16">
            <Logo></Logo>
        </div>
        <div>
            <ul class="ml-16 space-y-10">
                <div v-for="item in sidebarItems">
                    <div class="hover:border-b-2 hover:border-amber-500 border-b-2 border-transparent hover:inline-flex">
                        <li @click="setActiveView(item.text)" class="cursor-pointer font-semibold text-lg"><i :class="item.icon" class="mr-2"></i>{{item.text}}</li>
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
        <AccountDialog ref="accountDialog" @setActiveView="setActiveView"></AccountDialog>
    </div>
</template>

<script setup>
import { ref, defineEmits } from "vue";
import AccountDialog from '../Components/AccountDialog.vue';
import Logo from '../Components/Logo.vue'
import sidebarItems from "@/Data/SidebarData";

const activeView = ref(null);
const accountDialog = ref(null);

const emit = defineEmits(['setActiveView'])

const openAccountDialog = () => {
    accountDialog.value.open();
}

function setActiveView(item) {
    activeView.value = item;
    emit('setActiveView', activeView.value);
}


</script>

<style scoped>
</style>