<template>
    <Modal :show="show" @close="close">
        <template #title>Edit Quiz</template>
        <template #content>
            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <TextInput
                        v-model="form.title"
                        label="Title"
                        :error="form.errors.title"
                        required
                    />
                    <TextInput
                        v-model="form.description"
                        label="Description"
                        :error="form.errors.description"
                        required
                    />
                    <TextInput
                        v-model="form.questions_count"
                        label="Questions Count"
                        type="number"
                        :error="form.errors.questions_count"
                        required
                    />
                    <TextInput
                        v-model="form.time_limit"
                        label="Time Limit (minutes)"
                        type="number"
                        :error="form.errors.time_limit"
                        required
                    />
                    <TextInput
                        v-model="form.passing_score"
                        label="Passing Score (%)"
                        type="number"
                        :error="form.errors.passing_score"
                        required
                    />
                </div>
            </form>
        </template>
        <template #footer>
            <PrimaryButton @click="submit">Save</PrimaryButton>
            <SecondaryButton @click="close">Cancel</SecondaryButton>
        </template>
    </Modal>
</template>

<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    show: Boolean,
    quiz: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(["close"]);

const form = useForm({
    title: "",
    description: "",
    course_name: "",
    section_name: "",
    questions_count: 0,
    time_limit: 0,
    passing_score: 0,
});

watch(() => props.quiz, (newQuiz) => {
    if (newQuiz) {
        form.title = newQuiz.title || "";
        form.description = newQuiz.description || "";
        form.course_name = newQuiz.course_name || "";
        form.section_name = newQuiz.section_name || "";
        form.questions_count = newQuiz.questions_count || 0;
        form.time_limit = newQuiz.time_limit || 0;
        form.passing_score = newQuiz.passing_score || 0;
    }
}, { immediate: true });

const close = () => {
    emit("close");
};

const submit = () => {
    if (props.quiz && props.quiz.id) {
        form.put(route("quizzes.update", props.quiz.id), {
            onSuccess: () => close(),
        });
    }
};
</script>
