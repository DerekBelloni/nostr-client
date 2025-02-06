<template>
    <div class="border-b border-gray-700">
        <div class="flex ml-6 mt-12 mr-6 items-center pb-12">
            <div class="icon-border  mr-2">
                <i class="pi pi-user icon-color text-xl"></i>
            </div>
            <FloatLabel class="w-full">
                <label for="feed">Say something on nostr...</label>
                <InputText id="feed" variant="filled" class="rounded-full w-full bg-gray-300"></InputText>
            </FloatLabel>
        </div>
    </div>
</template>

<script setup></script>

<style scoped>
.icon-border {
    width: 2.5rem; 
    height: 2.5rem;
    border-radius: 50%; 
    border: 2px solid rgb(255, 255, 255); 
    display: flex; 
    justify-content: center; 
    align-items: center; 
}
.icon-color {
    color: rgba(251, 252, 255, 0.8);
}
</style>