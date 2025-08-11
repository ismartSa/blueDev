<template>
    <Modal :show="show" @close="close">
        <template #title>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Create New Quiz
            </h2>
        </template>

        <template #content>
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Information -->
                <div>
                    <InputLabel for="title" value="Quiz Title" />
                    <TextInput
                        id="title"
                        v-model="form.title"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError :message="form.errors.title" class="mt-2" />
                </div>

                <div>
                    <InputLabel for="description" value="Description" />
                    <TextInput
                        id="description"
                        v-model="form.description"
                        type="textarea"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>

                <!-- Quiz Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="questions_count" value="Number of Questions" />
                        <TextInput
                            id="questions_count"
                            v-model="form.questions_count"
                            type="number"
                            min="1"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.questions_count" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="time_limit" value="Time Limit (minutes)" />
                        <TextInput
                            id="time_limit"
                            v-model="form.time_limit"
                            type="number"
                            min="1"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.time_limit" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="passing_score" value="Passing Score (%)" />
                        <TextInput
                            id="passing_score"
                            v-model="form.passing_score"
                            type="number"
                            min="0"
                            max="100"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.passing_score" class="mt-2" />
                    </div>
                </div>

                <!-- Additional Settings -->
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input
                            id="allow_retake"
                            v-model="form.allow_retake"
                            type="checkbox"
                            class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                        >
                        <InputLabel for="allow_retake" value="Allow Retake" class="ml-2" />
                    </div>

                    <div class="flex items-center">
                        <input
                            id="show_correct_answers"
                            v-model="form.show_correct_answers"
                            type="checkbox"
                            class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                        >
                        <InputLabel for="show_correct_answers" value="Show Correct Answers After Submission" class="ml-2" />
                    </div>

                    <div class="flex items-center">
                        <input
                            id="randomize_questions"
                            v-model="form.randomize_questions"
                            type="checkbox"
                            class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                        >
                        <InputLabel for="randomize_questions" value="Randomize Questions" class="ml-2" />
                    </div>
                </div>
            </form>
        </template>

        <template #footer>
            <div class="flex justify-end space-x-3">
                <SecondaryButton @click="close">
                    Cancel
                </SecondaryButton>
                <PrimaryButton
                    @click="submit"
                    :disabled="form.processing"
                    :class="{ 'opacity-25': form.processing }"
                >
                    Create Quiz
                </PrimaryButton>
            </div>
        </template>
    </Modal>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    course: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close'])

const form = useForm({
    title: '',
    description: '',
    course_id: props.course.id,
    section_id: null,
    questions_count: 0,
    time_limit: 30,
    passing_score: 70,
    allow_retake: true,
    show_correct_answers: true,
    randomize_questions: false
})

const close = () => {
    form.reset()
    emit('close')
}

const submit = () => {
    form.post(route('quizzes.store'), {
        preserveScroll: true,
        onSuccess: () => close()
    })
}
</script>
