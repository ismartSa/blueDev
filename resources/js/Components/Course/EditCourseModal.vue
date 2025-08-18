<script setup>
import CourseFormModal from '@/Components/Course/CourseFormModal.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    course: { type: Object, required: true },
    categories: { type: Array, default: () => [] }
});

const emit = defineEmits(['close', 'success']);

const handleClose = () => {
    emit('close');
};

const handleSuccess = (message) => {
    emit('success', message);
};
</script>

<template>
    <CourseFormModal
        :show="show"
        mode="edit"
        :course="course"
        :categories="categories"
        title="Edit Course"
        max-width="3xl"
        @close="handleClose"
        @success="handleSuccess"
    />
</template>
