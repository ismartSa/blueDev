<template>
    <Head title="Course Details" />
    <AuthenticatedLayout>
        <template #header>
            <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        </template>

        <div class="py-6">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="space-y-3">
                    <div class="px-4 sm:px-0">
                        <div class="rounded-lg overflow-hidden w-full max-w-md mx-auto">
                            <img :src="course.image" alt="Course Image" class="w-full h-48 object-cover" />
                        </div>
                    </div>

                    <div class="relative bg-white dark:bg-slate-800 shadow sm:rounded-lg">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center p-4 gap-3">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ course.title }}</h2>

                            <div class="flex flex-wrap items-center gap-2">
                                <PrimaryButton
                                    @click="enroll"
                                    :disabled="form.processing || enrolled"
                                    :class="{ 'opacity-25': form.processing }"
                                    class="text-sm px-4 py-2"
                                >
                                    {{ enrolled ? 'Enrolled' : 'Enroll Now' }}
                                </PrimaryButton>

                                <PrimaryButton
                                    v-if="can(['create course'])"
                                    @click="data.createOpen = true"
                                    class="text-sm px-4 py-2"
                                >
                                    Add Section
                                </PrimaryButton>

                                <Link
                                    :href="route('course.create.lecture', { courseId: course.id })"
                                    class="inline-flex items-center"
                                >
                                    <PrimaryButton class="text-sm px-4 py-2">Add Lecture</PrimaryButton>
                                </Link>
                            </div>
                        </div>

                        <div class="p-4">
                            <p class="text-gray-600 dark:text-gray-300">{{ course.description }}</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Instructor</h3>
                                    <p class="text-gray-600 dark:text-gray-300">{{ course.name }}</p>
                                </div>

                                <div v-if="course.prerequisites && course.prerequisites.length">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Prerequisites</h3>
                                    <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300 space-y-1">
                                        <li v-for="(prerequisite, index) in course.prerequisites" :key="index" class="text-sm">
                                            {{ prerequisite }}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div v-if="course.learningOutcomes && course.learningOutcomes.length" class="mt-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Learning Outcomes</h3>
                                <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300 space-y-1">
                                    <li v-for="(outcome, index) in course.learningOutcomes" :key="index" class="text-sm">
                                        {{ outcome }}
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-6">
                                <div v-for="section in sortedSections" :key="section.id" class="mb-4">
                                    <Section
                                        :section="section"
                                        :lectures="filteredLectures(section.id)"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Add
            :show="data.createOpen"
            @close="data.createOpen = false"
            :title="title"
            :courseId="course.id"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { computed, reactive, ref } from "vue";
import { usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Section from '@/Pages/Course/Section.vue';
import Add from '@/Pages/Course/Add.vue';

const props = defineProps({
    title: String,
    course: Object,
    sections: Array,
    lectures: Array,
    breadcrumbs: Object,
    user: Object,
    enrolled: {
        type: Boolean,
        default: false
    }
});

const data = reactive({
    createOpen: false,
});

const form = useForm({
    course_id: props.course.id,
    user_id: props.user.id
});

const enroll = () => {
    form.post(route('courses.enroll', { courseId: props.course.id }), {
        preserveScroll: true,
        onSuccess: () => {
            // يمكنك إضافة رسالة نجاح هنا
            form.reset();
        },
        onError: (errors) => {
            // يمكنك إضافة رسالة خطأ هنا
            console.error('Enrollment failed:', errors);
        }
    });
};

const sortedSections = computed(() => {
    return [...props.sections].sort((a, b) => a.order - b.order);
});

const filteredLectures = (sectionId) => {
    return props.lectures.filter(lecture => lecture.section_id === sectionId);
};
</script>
