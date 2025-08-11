<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Delete Quiz
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Are you sure you want to delete "{{ quiz.title }}"? This action cannot be undone.
            </p>

            <div class="mt-6 flex justify-end space-x-3">
                <SecondaryButton @click="close">
                    Cancel
                </SecondaryButton>

                <DangerButton
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
import { useForm } from '@inertiajs/vue3'
import Modal from '@/Components/Modal.vue'
import DangerButton from '@/Components/DangerButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    quiz: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close'])

const form = useForm({})

const close = () => {
    emit('close')
}

const submit = () => {
    if (!props.quiz?.id) {
        console.error('Quiz ID is missing')
        return
    }

    form.delete(route('quizzes.destroy', props.quiz.id), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => close()
    })
}
</script>
