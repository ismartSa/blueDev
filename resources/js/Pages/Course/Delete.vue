<script setup>
import { useForm } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: Boolean,
    course: Object
});

const emit = defineEmits(['close']);

const form = useForm({});

const deleteCourse = () => {
    form.delete(route('courses.destroy', props.course.id), {
        onSuccess: () => emit('close')
    });
};
</script>

<template>
    <Modal :show="show" @close="emit('close')">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">حذف الدورة</h2>
            <p class="mt-1 text-sm text-gray-600">هل أنت متأكد من رغبتك في حذف هذه الدورة؟</p>
            <div class="mt-6 flex justify-end">
                <DangerButton @click="deleteCourse">حذف</DangerButton>
            </div>
        </div>
    </Modal>
</template>
