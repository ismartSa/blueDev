<script setup>
import { reactive, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    course: { type: Object, required: true }
});

const emit = defineEmits(['close', 'success']);

const form = useForm({
    title: '',
    description: '',
    domain: '',
    chapter: '',
    time_limit: 30,
    passing_score: 70,
    max_attempts: 3
});

const domainOptions = [
    { value: 'Mathematics', label: 'Mathematics' },
    { value: 'Science', label: 'Science' },
    { value: 'Programming', label: 'Programming' },
    { value: 'Language', label: 'Language' },
    { value: 'History', label: 'History' },
    { value: 'Other', label: 'Other' }
];

// Watch for show prop to reset form when modal closes
watch(() => props.show, (show) => {
    if (!show) {
        form.reset();
        form.clearErrors();
    }
});

const submit = () => {
    form.post(route('courses.quizzes.store', props.course.id), {
        onSuccess: () => {
            emit('success', 'Quiz created successfully!');
        },
        onError: (errors) => {
            console.error('Form errors:', errors);
        }
    });
};

const closeModal = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="2xl">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-6">
                Create New Quiz
            </h2>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Title -->
                <div>
                    <InputLabel for="title" value="Quiz Title" />
                    <TextInput
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autofocus
                    />
                    <InputError :message="form.errors.title" class="mt-2" />
                </div>

                <!-- Description -->
                <div>
                    <InputLabel for="description" value="Description" />
                    <TextArea
                        id="description"
                        v-model="form.description"
                        class="mt-1 block w-full"
                        rows="3"
                    />
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>

                <!-- Domain -->
                <div>
                    <InputLabel for="domain" value="Domain" />
                    <SelectInput
                        id="domain"
                        v-model="form.domain"
                        class="mt-1 block w-full"
                        :options="domainOptions"
                        placeholder="Select a domain..."
                        required
                    />
                    <InputError :message="form.errors.domain" class="mt-2" />
                </div>

                <!-- Chapter -->
                <div>
                    <InputLabel for="chapter" value="Chapter" />
                    <TextInput
                        id="chapter"
                        v-model="form.chapter"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="e.g., Chapter 1, Introduction, etc."
                        required
                    />
                    <InputError :message="form.errors.chapter" class="mt-2" />
                </div>

                <!-- Time Limit -->
                <div>
                    <InputLabel for="time_limit" value="Time Limit (minutes)" />
                    <TextInput
                        id="time_limit"
                        v-model="form.time_limit"
                        type="number"
                        class="mt-1 block w-full"
                        min="1"
                        max="180"
                    />
                    <InputError :message="form.errors.time_limit" class="mt-2" />
                </div>

                <!-- Passing Score -->
                <div>
                    <InputLabel for="passing_score" value="Passing Score (%)" />
                    <TextInput
                        id="passing_score"
                        v-model="form.passing_score"
                        type="number"
                        class="mt-1 block w-full"
                        min="0"
                        max="100"
                    />
                    <InputError :message="form.errors.passing_score" class="mt-2" />
                </div>

                <!-- Max Attempts -->
                <div>
                    <InputLabel for="max_attempts" value="Maximum Attempts" />
                    <TextInput
                        id="max_attempts"
                        v-model="form.max_attempts"
                        type="number"
                        class="mt-1 block w-full"
                        min="1"
                        max="10"
                    />
                    <InputError :message="form.errors.max_attempts" class="mt-2" />
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <SecondaryButton @click="closeModal" type="button">
                        Cancel
                    </SecondaryButton>

                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Quiz' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
