<template>
    <Modal :show="show" @close="close">
        <template #title>Create Quiz</template>
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
                        v-model="form.course_name"
                        label="Course Name"
                        :error="form.errors.course_name"
                        required
                    />
                    <TextInput
                        v-model="form.section_name"
                        label="Section Name (Optional)"
                        :error="form.errors.section_name"
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
            <PrimaryButton @click="submit">Create</PrimaryButton>
            <SecondaryButton @click="close">Cancel</SecondaryButton>
        </template>
    </Modal>
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    show: Boolean,
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

const close = () => {
    emit("close");
};

const submit = () => {
    form.post(route("quizzes.store"), {
        onSuccess: () => close(),
    });
};
</script>
