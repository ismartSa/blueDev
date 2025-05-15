<script setup>
import { watchEffect } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import InputError from '@/Components/InputError.vue'
import TextInput from '@/Components/TextInput.vue'
import Select from '@/Components/Select.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    quiz: {
        type: Object,
        required: true
    },
    courses: {
        type: Array,
        required: true
    },
    sections: {
        type: Array,
        required: true
    }
})

const emit = defineEmits(['close'])

const form = useForm({
    title: '',
    description: '',
    time_limit: '',
    passing_score: '',
    course_id: '',
    section_id: '',
    allow_retake: true,
    show_correct_answers: true,
    randomize_questions: false
})

const update = () => {
    form.put(route('quizzes.update', props.quiz.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close')
            form.reset()
        }
    })
}

watchEffect(() => {
    if (props.show && props.quiz) {
        form.errors = {}
        form.title = props.quiz.title
        form.description = props.quiz.description
        form.time_limit = props.quiz.time_limit
        form.passing_score = props.quiz.passing_score
        form.course_id = props.quiz.course_id
        form.section_id = props.quiz.section_id
        form.allow_retake = props.quiz.allow_retake
        form.show_correct_answers = props.quiz.show_correct_answers
        form.randomize_questions = props.quiz.randomize_questions
    }
})
</script>

<template>
    <Modal :show="show" @close="emit('close')">
        <form class="p-6" @submit.prevent="update">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Edit Quiz
            </h2>

            <div class="mt-6 space-y-6">
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

                <!-- Course and Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="course_id" value="Course" />
                        <Select
                            id="course_id"
                            v-model="form.course_id"
                            class="mt-1 block w-full"
                            required
                        >
                            <option value="">Select a course</option>
                            <option v-for="course in courses" :key="course.id" :value="course.id">
                                {{ course.title }}
                            </option>
                        </Select>
                        <InputError :message="form.errors.course_id" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="section_id" value="Section" />
                        <Select
                            id="section_id"
                            v-model="form.section_id"
                            class="mt-1 block w-full"
                        >
                            <option value="">Select a section (optional)</option>
                            <option v-for="section in sections" :key="section.id" :value="section.id">
                                {{ section.title }}
                            </option>
                        </Select>
                        <InputError :message="form.errors.section_id" class="mt-2" />
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
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <SecondaryButton
                    @click="emit('close')"
                    :disabled="form.processing"
                >
                    Cancel
                </SecondaryButton>

                <PrimaryButton
                    @click="update"
                    :disabled="form.processing"
                    :class="{ 'opacity-25': form.processing }"
                >
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
