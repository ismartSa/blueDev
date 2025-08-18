<script setup>
import { watchEffect, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import FormField from '@/Components/Form/FormField.vue'
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

const courseOptions = computed(() => [
    { value: '', label: 'Select a course' },
    ...props.courses.map(course => ({ value: course.id, label: course.title }))
])

const sectionOptions = computed(() => [
    { value: '', label: 'Select a section (optional)' },
    ...props.sections.map(section => ({ value: section.id, label: section.title }))
])

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
                <FormField
                    id="title"
                    v-model="form.title"
                    type="text"
                    label="Quiz Title"
                    :error="form.errors.title"
                    required
                />

                <FormField
                    id="description"
                    v-model="form.description"
                    type="textarea"
                    label="Description"
                    :error="form.errors.description"
                    required
                />

                <!-- Quiz Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormField
                        id="time_limit"
                        v-model="form.time_limit"
                        type="number"
                        label="Time Limit (minutes)"
                        :error="form.errors.time_limit"
                        min="1"
                        required
                    />

                    <FormField
                        id="passing_score"
                        v-model="form.passing_score"
                        type="number"
                        label="Passing Score (%)"
                        :error="form.errors.passing_score"
                        min="0"
                        max="100"
                        required
                    />
                </div>

                <!-- Course and Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FormField
                        id="course_id"
                        v-model="form.course_id"
                        type="select"
                        label="Course"
                        :options="courseOptions"
                        :error="form.errors.course_id"
                        required
                    />

                    <FormField
                        id="section_id"
                        v-model="form.section_id"
                        type="select"
                        label="Section"
                        :options="sectionOptions"
                        :error="form.errors.section_id"
                    />
                </div>

                <!-- Additional Settings -->
                <div class="space-y-4">
                    <FormField
                        id="allow_retake"
                        v-model="form.allow_retake"
                        type="checkbox"
                        label="Allow Retake"
                    />

                    <FormField
                        id="show_correct_answers"
                        v-model="form.show_correct_answers"
                        type="checkbox"
                        label="Show Correct Answers After Submission"
                    />

                    <FormField
                        id="randomize_questions"
                        v-model="form.randomize_questions"
                        type="checkbox"
                        label="Randomize Questions"
                    />
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
