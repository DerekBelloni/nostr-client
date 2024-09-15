<template>
    <Dialog v-model:visible="accountDialog" modal header="Get Started" :style="{ width: '25rem' }">
        <div class="flex flex-col space-y-4">
            <span>New to Nostr? Create your account to get started!</span>
            <div>
                <Button @click="setActiveView()" label="Create Account" class="rounded-full px-4 py-1 text-white font-semibold"/>
            </div>
            <div class="inline-flex">
                <span class="text-sm">Already have an account?</span>
                <a @click="openLoginDialog" class="text-sm pl-2 text-amber-500 hover:text-amber-600 cursor-pointer">Login now</a>
            </div>
        </div>
    </Dialog>
    <LoginDialog ref="loginDialog" @pubKeyRetrieved="pubKeyRetrieved"></LoginDialog>
</template>

<script setup>
import { ref, defineEmits } from "vue";
import Dialog from 'primevue/dialog';
import LoginDialog from '../Components/LoginDialog.vue';

const accountDialog = ref(false);
const loginDialog = ref(null);
const activeView = ref(null);

const emit = defineEmits(['setActiveView', 'pubKeyRetrieved']);

function setActiveView() {
    activeView.value = 'account';
    emit('setActiveView', activeView.value);
    close();
}

const close = () => {
    accountDialog.value = false;
}

const open = () => {
    accountDialog.value = true;
};

const openLoginDialog = () => {
    loginDialog.value.open();
    accountDialog.value = false;
}

const pubKeyRetrieved = () => {
    emit('pubKeyRetrieved');
}


defineExpose({ open });
</script>