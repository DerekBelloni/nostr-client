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

const noteDialog = ref(false);
const note = ref(null);

const emit = defineEmits(['submitNote']);

const close = () => {
    noteDialog.value = false;
}

const open = () => {
    noteDialog.value = true;
}

const submitNote = () => {

}

defineExpose({ open });
</script>