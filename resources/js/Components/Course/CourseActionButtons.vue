<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    course: {
        type: Object,
        required: true
    },
    isEnrolled: {
        type: Boolean,
        default: false
    }
})

defineEmits(['enroll'])
</script>

<template>
    <div class="space-y-3">
        <button
            v-if="!isEnrolled"
            @click="$emit('enroll')"
            class="w-full bg-primary hover:bg-primary/90 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200"
        >
            {{ $page.props.translations?.course?.label?.enroll || 'Enroll Now' }}
        </button>
        <Link
            v-else
            :href="route('course.learn', { id: course.id, courseSlug: course.slug })"
            class="block w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center"
        >
            {{ $page.props.translations?.course?.label?.continue_learning || 'Continue Learning' }}
        </Link>
    </div>
</template>
