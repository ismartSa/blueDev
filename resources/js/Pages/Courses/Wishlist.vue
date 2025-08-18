<template>
    <Head title="My Wishlist" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h1 class="text-2xl font-bold">My Wishlist</h1>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ courses.length }} {{ courses.length === 1 ? 'course' : 'courses' }}
                            </span>
                        </div>

                        <div v-if="courses.length === 0" class="text-center py-12">
                            <div class="text-gray-500 dark:text-gray-400 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium mb-2">Your wishlist is empty</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">
                                Start adding courses to your wishlist to keep track of what you want to learn.
                            </p>
                            <Link :href="route('courses.index')" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition duration-150 ease-in-out">
                                Browse Courses
                            </Link>
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="course in courses" :key="course.id" class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                                <div class="relative">
                                    <img :src="course.image || '/placeholder-course.jpg'" :alt="course.title" class="w-full h-48 object-cover">
                                    <div class="absolute top-2 right-2">
                                        <button @click="removeFromWishlist(course.id)" class="p-2 bg-red-500 hover:bg-red-600 text-white rounded-full transition-colors duration-200">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div v-if="course.is_free" class="absolute top-2 left-2 bg-green-500 text-white px-2 py-1 rounded text-xs font-medium">
                                        Free
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">{{ course.category }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ course.level }}</span>
                                    </div>
                                    <h3 class="font-semibold text-lg mb-2 line-clamp-2">{{ course.title }}</h3>
                                    <p class="text-gray-600 dark:text-gray-300 text-sm mb-3 line-clamp-2">{{ course.description }}</p>
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">by {{ course.instructor }}</span>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ course.rating || 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <span v-if="!course.is_free" class="text-lg font-bold text-green-600 dark:text-green-400">${{ course.price }}</span>
                                            <span v-else class="text-lg font-bold text-green-600 dark:text-green-400">Free</span>
                                        </div>
                                        <Link :href="route('courses.show', [course.id, course.slug])" class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition duration-150 ease-in-out">
                                            View Course
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { router } from '@inertiajs/vue3'

defineProps({
    courses: {
        type: Array,
        default: () => []
    }
})

const removeFromWishlist = (courseId) => {
    router.post(route('courses.wishlist.toggle', courseId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Refresh the page to update the wishlist
            router.reload({ only: ['courses'] })
        }
    })
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>