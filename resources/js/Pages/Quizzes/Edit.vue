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
    courses: Array,
    sections: Array,
    quiz: Object,
});

const emit = defineEmits(["close"]);

const form = useForm({
    title: "",
    description: "",
    time_limit: "",
    passing_score: "",
    course_id: "",
    section_id: "",
});

const update = () => {
    form.put(route("quiz.update", props.quiz?.id), {
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
        form.title = props.quiz?.title;
        form.description = props.quiz?.description;
        form.time_limit = props.quiz?.time_limit;
        form.passing_score = props.quiz?.passing_score;
        form.course_id = props.quiz?.course_id;
        form.section_id = props.quiz?.section_id;
    }
});
</script>

<template>
    <section class="space-y-6">
        <Modal :show="props.show" @close="emit('close')">
            <form class="p-6" @submit.prevent="update">
                <h2
                    class="text-lg font-medium text-slate-900 dark:text-slate-100"
                >
                    {{ lang().label.edit }} {{ props.title }}
                </h2>
                <div class="my-6 space-y-4">
                    <div>
                        <InputLabel for="title" :value="lang().label.title" />
                        <TextInput
                            id="title"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.title"
                            required
                            :placeholder="lang().placeholder.title"
                            :error="form.errors.title"
                        />
                        <InputError class="mt-2" :message="form.errors.title" />
                    </div>
                </div>

                <div class="my-6 space-y-4">
                    <div>
                        <InputLabel for="description" :value="lang().label.description" />
                        <TextInput
                            id="description"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.description"
                            required
                            :placeholder="lang().placeholder.description"
                            :error="form.errors.description"
                        />
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>
                </div>
                <!-- Time Limit -->
                <div class="my-6 space-y-4">
                    <div>
                        <InputLabel for="time_limit" :value="lang().label.time_limit" />
                        <TextInput
                            id="time_limit"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.time_limit"
                            required
                            :placeholder="lang().placeholder.time_limit"
                            :error="form.errors.time_limit"
                        />
                        <InputError class="mt-2" :message="form.errors.time_limit" />
                    </div>
                </div>
                <!-- Passing Score -->
                <div class="my-6 space-y-4">
                    <div>
                        <InputLabel for="passing_score" :value="lang().label.passing_score" />
                        <TextInput
                            id="passing_score"
                            type="number"
                            class="mt-1 block w-full"
                            v-model="form.passing_score"
                            required
                            :placeholder="lang().placeholder.passing_score"
                            :error="form.errors.passing_score"
                        />
                        <InputError class="mt-2" :message="form.errors.passing_score" />
                    </div>
                </div>
                <!-- Course -->
                <div class="my-6 space-y-4">
                    <div>
                        <InputLabel for="course_id" :value="lang().label.course" />
                        <Select
                            id="course_id"
                            v-model="form.course_id"
                            required
                            :placeholder="lang().placeholder.course"
                            :error="form.errors.course_id"
                        >
                            <option v-for="course in props.courses" :key="course.id" :value="course.id">
                                {{ course.name }}
                            </option>
                        </Select>
                        <InputError class="mt-2" :message="form.errors.course_id" />
                    </div>
                </div>
                <!-- Section -->
                <div class="my-6 space-y-4">
                    <div>
                        <InputLabel for="section_id" :value="lang().label.section" />
                        <Select
                            id="section_id"
                            v-model="form.section_id"
                            required
                            :placeholder="lang().placeholder.section"
                            :error="form.errors.section_id"
                        >
                            <option v-for="section in props.sections" :key="section.id" :value="section.id">
                                {{ section.name }}
                            </option>
                        </Select>
                        <InputError class="mt-2" :message="form.errors.section_id" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <SecondaryButton
                        :disabled="form.processing"
                        @click="emit('close')"
                    >
                        {{ lang().button.close }}
                    </SecondaryButton>
                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="update"
                    >
                        {{
                            form.processing
                                ? lang().button.save + "..."
                                : lang().button.save
                        }}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>
    </section>
</template>
