<template>
    <!-- <div class="border-b border-gray-200"> -->
    <div class="shadow">
        <div class="flex ml-6 mt-12 mr-6 items-center pb-12">
            <div class="icon-border  mr-2">
                <i class="pi pi-user icon-color text-xl"></i>
            </div>
            <FloatLabel class="w-full">
                <label for="feed">Say something on nostr...</label>
                <InputText id="feed" variant="filled" class="rounded-full w-full"></InputText>
            </FloatLabel>
        </div>
    </div>
</template>

<script setup></script>

<style scoped>
.icon-border {
    width: 2.5rem; /* Set the width of the container */
    height: 2.5rem; /* Set the height of the container */
    border-radius: 50%; /* Make the container circular */
    border: 2px solid rgba(55, 65, 81, 0.8); /* Set the border properties */
    display: flex; /* Use flexbox for centering the icon */
    justify-content: center; /* Center the icon horizontally */
    align-items: center; 
}
.icon-color {
    color: rgba(55, 65, 81, 0.8);
}
</style>