<template>
    <Head title="Course Details" />
    <AuthenticatedLayout>
        <template #header>
            <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="space-y-4">
                    <div class="px-4 sm:px-0">
                        <div class="rounded-lg overflow-hidden w-fit">
                            <img :src="course.image" alt="Course Image" class="w-full h-64 object-cover" />
                        </div>
                    </div>

                    <div class="relative bg-white dark:bg-slate-800 shadow sm:rounded-lg">
                        <div class="flex justify-between items-center p-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ course.title }}</h2>

                            <div class="flex items-center space-x-2">
                                <PrimaryButton
                                    @click="enroll"
                                    :disabled="form.processing || enrolled"
                                    :class="{ 'opacity-25': form.processing }"
                                >
                                    {{ enrolled ? 'Enrolled' : 'Enroll Now' }}
                                </PrimaryButton>

                                <PrimaryButton
                                    v-if="can(['create course'])"
                                    @click="data.createOpen = true"
                                >
                                    Add Section
                                </PrimaryButton>

                                <Link
                                    :href="route('course.create.lecture', { courseId: course.id })"
                                    class="inline-flex items-center"
                                >
                                    <PrimaryButton>Add Lecture</PrimaryButton>
                                </Link>
                            </div>
                        </div>

                        <div class="p-6">
                            <p class="text-gray-600 dark:text-gray-300">{{ course.description }}</p>

                            <div class="mt-6">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Instructor</h3>
                                <p class="text-gray-600 dark:text-gray-300">{{ course.name }}</p>
                            </div>

                            <div class="mt-6">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Prerequisites</h3>
                                <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                                    <li v-for="(prerequisite, index) in course.prerequisites" :key="index">
                                        {{ prerequisite }}
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-6">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Learning Outcomes</h3>
                                <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300">
                                    <li v-for="(outcome, index) in course.learningOutcomes" :key="index">
                                        {{ outcome }}
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-8">
                                <div v-for="section in sortedSections" :key="section.id">
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
