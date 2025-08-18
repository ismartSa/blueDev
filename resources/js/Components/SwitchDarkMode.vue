<script setup>
import { ref, computed, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const translations = computed(() => page.props.translations?.messages || {})
const lang = () => translations.value

const darkMode = ref(
    localStorage.getItem('dark-mode') === 'true' ||
    (!('dark-mode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
)

watch(darkMode, (value) => {
    localStorage.setItem('dark-mode', value)
    document.documentElement.classList.toggle('dark', value)
}, { immediate: true })

const toggleDarkMode = () => {
    darkMode.value = !darkMode.value
}
</script>

<template>
    <button
        @click="toggleDarkMode"
        class="hover:text-slate-400 hover:bg-slate-900 focus:bg-slate-900 focus:text-slate-400 inline-flex items-center justify-center p-2 rounded-md lg:hover:text-slate-500 dark:hover:text-slate-400 lg:hover:bg-slate-100 dark:hover:bg-slate-900 focus:outline-none lg:focus:bg-slate-100 dark:focus:bg-slate-900 lg:focus:text-slate-500 dark:focus:text-slate-400 transition duration-150 ease-in-out"
    >
        <svg
            v-show="!darkMode"
            class="w-5 h-5"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
            />
        </svg>
        <svg
            v-show="darkMode"
            class="w-5 h-5"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
            />
        </svg>
    </button>
</template>
