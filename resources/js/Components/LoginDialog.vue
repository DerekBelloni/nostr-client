<template>
    <Dialog v-model:visible="loginDialog" modal header="Login" :style="{ width: '25rem' }">
        <span>test</span>
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