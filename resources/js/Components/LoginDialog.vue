<template>
    <Dialog v-model:visible="loginDialog" modal header="Login" :style="{ width: '30rem' }">
        <div class="block mb-4">
            <span>Enter your Nostr private key (starting with "nsec"):</span>
        </div>
        <InputText class="w-full rounded-full mb-4"></InputText>
        <div class="flex justify-end">
            <Button label="Login" class="rounded-full px-4 py-1 text-white font-semibold"></Button>
        </div>
    </Dialog>
</template>

<script setup>
    import { ref } from 'vue';
    import Dialog from 'primevue/dialog';

    const loginDialog = ref(false);

    const open = () => {
        loginDialog.value = true;
    }

    defineExpose({ open });
</script>