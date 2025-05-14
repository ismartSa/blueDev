<template>
    <Modal :show="show" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ $page.props.translations?.course?.label?.add_section || 'Add New Section' }}
            </h2>

            <form @submit.prevent="submit" class="mt-6">
                <div class="mb-4">
                    <InputLabel for="title" :value="$page.props.translations?.course?.label?.section_title || 'Section Title'" />
                    <TextInput
                        id="title"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.title"
                        required
                        autofocus
                    />
                    <InputError class="mt-2" :message="form.errors.title" />
                </div>

                <div class="mb-4">
                    <InputLabel for="description" :value="$page.props.translations?.course?.label?.section_description || 'Section Description'" />
                    <TextArea
                        id="description"
                        class="mt-1 block w-full"
                        v-model="form.description"
                        rows="4"
                    />
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <div class="flex items-center justify-end mt-6">
                    <SecondaryButton @click="closeModal" class="mr-3">
                        {{ $page.props.translations?.common?.cancel || 'Cancel' }}
                    </SecondaryButton>
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        {{ $page.props.translations?.common?.save || 'Save' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    courseId: {
        type: [Number, String],
        required: true
    }
});

const emit = defineEmits(['close']);

const form = useForm({
    title: '',
    description: '',
    course_id: props.courseId
});

const submit = () => {
    form.post(route('course.sections.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            form.reset();
        }
    });
};

const closeModal = () => {
    emit('close');
    form.reset();
};
</script>
