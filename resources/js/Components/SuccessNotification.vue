<script setup>
const props = defineProps({
    show: { type: Boolean, default: false },
    message: { type: String, default: '' },
});
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 transform translate-x-full"
        enter-to-class="opacity-100 transform translate-x-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 transform translate-x-0"
        leave-to-class="opacity-0 transform translate-x-full"
    >
        <div
            v-if="show"
            class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 max-w-sm"
        >
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm font-medium">{{ message }}</span>
            </div>
        </div>
    </Transition>
</template>
