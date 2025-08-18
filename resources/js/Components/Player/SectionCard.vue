<template>
    <div class="border border-slate-200/60 rounded-xl shadow-sm">
        <div class="p-3 bg-gradient-to-r from-slate-50 to-blue-50/30 border-b border-slate-200/60">
            <h3 class="font-medium text-slate-900 text-sm sm:text-base flex items-center gap-2">
                <SectionIcon class="w-4 h-4 text-indigo-600" />
                {{ section.title }}
            </h3>
        </div>
        <div class="divide-y divide-slate-100">
            <LectureItem
                v-for="lecture in section.lectures"
                :key="lecture.id"
                :lecture="lecture"
                :is-current="currentLecture && currentLecture.id === lecture.id"
                :is-completed="isLectureCompleted(lecture.id)"
                @select="$emit('lecture-select', lecture)"
            />
        </div>
    </div>
</template>

<script setup>
import LectureItem from './LectureItem.vue'

// Section icon as inline SVG component
const SectionIcon = {
    template: `<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd"></path></svg>`
}

const props = defineProps({
    section: {
        type: Object,
        required: true
    },
    currentLecture: {
        type: Object,
        default: null
    },
    isLectureCompleted: {
        type: Function,
        required: true
    }
})

const emit = defineEmits(['lecture-select'])
</script>