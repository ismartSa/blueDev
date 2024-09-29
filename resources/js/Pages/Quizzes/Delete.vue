<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Are you sure you want to delete this quiz?
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Once this quiz is deleted, all of its resources and data will be permanently deleted.
            </p>
            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="close"> Cancel </SecondaryButton>
                <DangerButton
                    class="ml-3"
                    @click="submit"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Delete Quiz
                </DangerButton>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    show: Boolean,
    quiz: Object,
});

const emit = defineEmits(["close"]);

const form = useForm({});

const close = () => {
    emit("close");
};

const submit = () => {
    console.log('Submitting delete request for quiz:', props.quiz);
    if (props.quiz?.id) {
        const deleteUrl = route("quizzes.destroy", props.quiz.id);
        console.log('Delete URL:', deleteUrl);
        form.delete(deleteUrl, {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                console.log('Delete successful');
                close();
            },
            onError: (errors) => {
                console.error('Delete failed:', errors);
            }
        });
    } else {
        console.error('Quiz ID is missing');
    }
};
</script>
