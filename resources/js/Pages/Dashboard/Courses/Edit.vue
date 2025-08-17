<script setup>
import { Head } from '@inertiajs/vue3'
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import CourseFormModal from '@/Components/Course/CourseFormModal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { PencilIcon, ArrowLeftIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
    course: { type: Object, required: true },
    categories: { type: Array, default: () => [] }
})

const data = reactive({
    editModalOpen: false
})

const openEditModal = () => {
    data.editModalOpen = true
}

const closeModal = () => {
    data.editModalOpen = false
}

const handleSuccess = (message) => {
    closeModal()
    // Redirect to courses list after successful update
    setTimeout(() => {
        router.get(route('dashboard.courses.index'))
    }, 1500)
}

const goBack = () => {
    router.get(route('dashboard.courses.index'))
}
</script>

<template>
    <Head :title="`Edit ${course.title}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Course</h2>
                <div class="flex gap-3">
                    <SecondaryButton @click="goBack" class="flex items-center gap-2">
                        <ArrowLeftIcon class="w-4 h-4" />
                        Back to Courses
                    </SecondaryButton>
                    <PrimaryButton @click="openEditModal" class="flex items-center gap-2">
                        <PencilIcon class="w-4 h-4" />
                        Edit Course
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Course Details</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">View and manage course information</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ course.title }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 capitalize">{{ course.status }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">${{ course.price || '0.00' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ course.duration || 'Not specified' }} hours</p>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ course.description || 'No description provided' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Course Modal -->
        <CourseFormModal
            :show="data.editModalOpen"
            mode="edit"
            :course="course"
            :categories="categories"
            title="Edit Course"
            max-width="3xl"
            @close="closeModal"
            @success="handleSuccess"
        />
    </AuthenticatedLayout>
</template>