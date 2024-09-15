<template>
    <Dialog v-model:visible="noteDialog" modal header="Say something" :style="{ width: '25rem' }">
        <div class="flex flex-col space-y-4">        
            <div class="card flex justify-center">
                <Textarea v-model="note" variant="filled" rows="5" cols="30" />
            </div>
            <div class="flex justify-end">
                <Button @click="submitNote" label="Submit"></Button>
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import Dialog from 'primevue/dialog';
import { ref, defineEmits } from 'vue';
import { router } from '@inertiajs/vue3';
import { useNostrStore } from '@/stores/useNostrStore';

const noteDialog = ref(false);
const note = ref(null);

const nostrStore = useNostrStore();

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
                console.log("success!");
            }
        })
    }
    // provide some type of warning, maybe a toast
    // about a note must have content
}

defineExpose({ open });
</script>