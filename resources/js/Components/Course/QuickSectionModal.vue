<template>
    <Modal :show="show" @close="closeModal">
        <div class="p-4">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Quick Add Section
            </h2>
            <form @submit.prevent="submit" class="space-y-4">
                <TextInput
                    v-model="form.title"
                    placeholder="Section title..."
                    required
                    autofocus
                />
                <InputError :message="form.errors.title" />
                <div class="flex justify-end space-x-2">
                    <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">
                        {{ form.processing ? 'Adding...' : 'Add' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    courseId: { type: [Number, String], required: true }
});

const emit = defineEmits(['close', 'success']);

const form = useForm({
    title: '',
    description: '',
    course_id: props.courseId
});

const submit = () => {
    form.post(route('course.sections.store'), {
        onSuccess: () => {
            emit('success');
            closeModal();
        }
    });
};

const closeModal = () => {
    emit('close');
    form.reset();
};
</script>
