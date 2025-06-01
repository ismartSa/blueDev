<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import { watchEffect } from "vue";

const props = defineProps({
    show: Boolean,
    title: String,
    courseId: String,
});

const emit = defineEmits(["close"]);

const form = useForm({
    title: "",
    order: "",
    description: "",
});

const create = () => {
    form.post(route("courses.sections.store", { course: props.courseId }), {
        preserveScroll: true,
        onSuccess: () => {
            emit("close");
            form.reset();
        },
        onError: () => null,
        onFinish: () => null,
    });
};

watchEffect(() => {
    if (props.show) {
        form.errors = {};
    }
});
</script>

<template>
    <section class="space-y-6">
        <Modal :show="props.show" @close="emit('close')">
            <form class="p-6" @submit.prevent="create">
                <h2 class="text-lg font-medium text-slate-900 dark:text-slate-100">
                    Add New Section
                </h2>
                <div class="my-6 space-y-4">
                    <div>
                        <InputLabel for="title" value="Title" />
                        <TextInput
                            id="title"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.title"
                            required
                            placeholder="Enter section title"
                            :error="form.errors.title"
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>
                    <div>
                        <InputLabel for="description" value="Description" />
                        <TextInput
                            id="description"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.description"
                            required
                            placeholder="Enter section description"
                            :error="form.errors.description"
                        />
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>
                    <div>
                        <InputLabel for="order" value="Order" />
                        <TextInput
                            id="order"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.order"
                            required
                            placeholder="Enter section order"
                            :error="form.errors.order"
                        />
                        <InputError class="mt-2" :message="form.errors.order" />
                    </div>
                </div>
                <div class="flex justify-end">
                    <SecondaryButton
                        :disabled="form.processing"
                        @click="emit('close')"
                    >
                        Close
                    </SecondaryButton>
                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="create"
                    >
                        {{ form.processing ? "Adding..." : "Add Section" }}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>
    </section>
</template>
