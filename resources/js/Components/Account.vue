<template>
    <div class="border-r border-gray-200 h-full center-feature">
        <div class="marquee">
            <div class="flex flex-col">
                <div class="py-8 border-b border-gray-200">
                    <div class="ml-8">
                        <span class="text-2xl text-gray-600 font-semibold">Create Account</span>
                    </div>
                </div>
                <div class="mt-8 ml-8">
                    <span>Start with the basics. Only the username is required.</span>
                </div>
                <div class="ml-8 mt-8">
                    <div class="flex flex-row">
                        <div>
                            <span>Username</span>
                            <span class="text-sm text-gray-400 pl-2">Pick a short user handle (e.g. "satoshi")</span>
                        </div>
                        <div class="ml-16 inline-flex">
                            <span class="text-red-500 text-3xl">*</span>
                            <span class="text-gray-400 font-semibold">required</span>
                        </div>
                    </div>
                    <FloatLabel>
                        <label for="username">@ Enter username</label>
                        <InputText id="username" class="rounded-full account-inputs" size="small"></InputText>
                    </FloatLabel>
                    <div class="flex flex-row">
                        <div class="mt-8">
                            <span>Display Name</span>
                            <span class="text-sm text-gray-400 pl-2">Pick a longer display name (e.g. "Satoshi Nakamoto")</span>
                        </div>
                    </div>
                    <FloatLabel>
                        <label for="username">Enter display name</label>
                        <InputText id="username" class="rounded-full account-inputs" size="small"></InputText>
                    </FloatLabel>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineEmits } from "vue";

// const activeView = ref(null);

// const emit = defineEmits(['setActiveView']);

// function setActiveView() {
//     activeView.value = 'account';
//     emit('setActiveView', activeView.value);
// }

</script>

<style scoped>
    .marquee {
        width: 100%
    }
    .center-feature {
        width: 55%;
    }
    .account-inputs {
        width: 65%
    }
</style>