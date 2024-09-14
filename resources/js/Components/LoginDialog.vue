<template>
    <Dialog v-model:visible="loginDialog" modal header="Login" :style="{ width: '30rem' }">
        <div class="block mb-4">
            <span>Enter your Nostr private key (starting with "nsec"):</span>
        </div>
        <InputText v-model="nsec" type="password" class="w-full rounded-full mb-4"></InputText>
        <div class="flex justify-end">
            <Button @click="retrieveNpub" label="Login" class="rounded-full px-4 py-1 text-white font-semibold"></Button>
        </div>
    </Dialog>
</template>

<script setup>
    // import { ref, defineEmits } from 'vue';
    import { ref } from 'vue';
    import { router } from '@inertiajs/vue3';
    import { useNostrStore } from '@/stores/useNostrStore';
    import Dialog from 'primevue/dialog';
    
    const loginDialog = ref(false);
    const hexPub = ref('');
    const metadataContent = ref([]);
    const npub = ref('');
    const nsec = ref('');
    const verified = ref(false);
    
    const nostrStore = useNostrStore();

    const emit = defineEmits(['pubKeyRetrieved']);

    const open = () => {
        loginDialog.value = true;
    }

    const retrieveNpub = () => {
        router.post('/npub', { nsec: nsec.value }, {
            preserveState: true,
            onSuccess: page => {
                nostrStore.metadataContent = page.props.metadataContent;
                nostrStore.npub = page.props.npub;
                nostrStore.hexPriv = page.props.hexPriv;
                nostrStore.hexPub = page.props.hexPub;
                nostrStore.verified = page.props.verified;
                router.replace('/'); 
                loginDialog.value = false; 
            },
            onError: errors => {
                console.error('Error: ', errors);
            }
        });
    }

    defineExpose({ open });
</script>