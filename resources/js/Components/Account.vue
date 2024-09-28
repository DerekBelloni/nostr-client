<template>
    <div class="h-full">
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
                <div class="ml-8 mt-2">
                    <div class="mt-8">
                        <FloatLabel>
                            <label for="username">@ Enter username</label>
                            <InputText id="username" class="rounded-full account-inputs" size="small"></InputText>
                        </FloatLabel>
                    </div>
                    <div class="flex flex-row ml-4 mt-2">
                        <div>
                            <span>Username</span>
                            <span class="text-sm text-gray-400 pl-2">Pick a short user handle (e.g. "satoshi")</span>
                        </div>
                        <div class="ml-16 inline-flex">
                            <span class="text-red-500 text-3xl">*</span>
                            <span class="text-gray-400 font-semibold">required</span>
                        </div>
                    </div>
                    <div class="mt-8">
                        <FloatLabel>
                            <label for="username">Enter display name</label>
                            <InputText id="username" class="rounded-full account-inputs" size="small"></InputText>
                        </FloatLabel>
                    </div>
                    <div class="flex flex-row">
                        <div class="mt-2 ml-4">
                            <span>Display Name</span>
                            <span class="text-sm text-gray-400 pl-2">Pick a longer display name (e.g. "Satoshi Nakamoto")</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6 ml-8 flex flex-row space-x-4">
            <div class="flex justify-start">
                <Button label="Next" class="rounded-full px-4 py-1 text-white font-semibold"/>
            </div>
            <div>
                <Button label="Cancel" class="rounded-full px-4 py-1 text-white font-semibold"/>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineEmits } from "vue";

</script>

<style scoped>
    .marquee {
        width: 100%
    }
    .center-feature {
        width: 0%;
    }
    .account-inputs {
        width: 65%
    }
</style>