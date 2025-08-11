<template>
    <section class="space-y-6">
        <Head title="Create New Lecture" />

        <AuthenticatedLayout>
            <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />

            <div class="p-4">
                <header>
                    <h2 class="text-xl font-bold">Create New Lecture</h2>
                </header>
                <form @submit.prevent="submitForm">
                    <div class="mb-4">
                        <label
                            for="title"
                            class="block text-gray-700 font-bold mb-2"
                            >Lecture Title:</label
                        >
                        <input
                            type="text"
                            id="title"
                            v-model="form.title"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        />
                    </div>

                    <div class="mb-4">
                        <label
                            for="section_id"
                            class="block text-gray-700 font-bold mb-2"
                            >Section:</label
                        >
                        <select
                            id="section_id"
                            v-model="form.section_id"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        >
                            <option
                                v-for="section in sections"
                                :key="section.id"
                                :value="section.id"
                            >
                                {{ section.title }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label
                            for="video_url"
                            class="block text-gray-700 font-bold mb-2"
                            >Video URL:</label
                        >
                        <input
                            type="text"
                            id="video_url"
                            v-model="form.video_url"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        />
                    </div>

                    <div class="mb-4">
                        <label
                            for="duration"
                            class="block text-gray-700 font-bold mb-2"
                            >Video Duration (in minutes):</label
                        >
                        <input
                            type="number"
                            id="duration"
                            v-model.number="form.duration"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        />
                    </div>

                    <button
                        type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus-shadow-outline"
                    >
                        Create Lecture
                    </button>
                </form>
            </div>



        </AuthenticatedLayout>
    </section>
</template>

<script setup>
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { ref } from "vue";

const props = defineProps({
    title: String,
    course: Object,
    sections: Array,
    breadcrumbs: Object,
});

const form = useForm({
    title: "",
    section_id: null,
    video_url: "",
    duration: 0,
});

const submitForm = () => {
    // Use Inertia.js to submit the form
    // usePage().post(`/courses/${props.course.id}/lecture`, form);
    form.post(route("course.create.lecture", { courseId: props.course.id }), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => null,
        onFinish: () => null,
    });
};
</script>
