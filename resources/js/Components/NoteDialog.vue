<template>
    <Dialog v-model:visible="noteDialog" modal header="Compose a note" :style="{ width: '40rem' }" >
        <div class="flex flex-col space-y-4">        
            <div class="card flex justify-center">
                <Textarea v-model="note" variant="filled" rows="5" cols="50" />
            </div>
            <div class="flex justify-end">
                <Button @click="submitNote" label="Submit" raised rounded size="small" class="font-bold"></Button>
            </div>
        </div>
    </Dialog>
    <Toast position="top-right"/>
</template>

<script setup>
import Dialog from 'primevue/dialog';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useNostrStore } from '@/stores/useNostrStore';
import { useToast } from 'primevue/usetoast';

const noteDialog = ref(false);
const note = ref(null);

const nostrStore = useNostrStore();
const toast = useToast();

const emit = defineEmits(['submitNote']);

const close = () => {
    noteDialog.value = false;
}

const open = () => {
    noteDialog.value = true;
}

const submitNote = () => {
    if (!!note.value && note.value != '') {
        const params = {
            noteContent: note.value,
            pubHexKey: nostrStore.hexPub,
            hexPriv: nostrStore.hexPriv
        }
        const url = '/note/create'
        router.visit(url, {
            method: 'post',
            data: params,
            preserveState: true,
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Note published! 🚀', detail: 'Successfully shared with the Nostr network', life: 2500 });
                close();
            }
        })
    }
}

defineExpose({ open });
</script>