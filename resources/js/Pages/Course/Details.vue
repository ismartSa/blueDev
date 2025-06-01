<template>
  <div v-memo="[course.id]">
    <Head :title="course.title" />
    <AuthenticatedLayout>
      <template #header>
        <Breadcrumb :title="course.title" :breadcrumbs="breadcrumbs" />
      </template>

      <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 sm:p-10">
              <div class="flex flex-col md:flex-row gap-6">
                <CourseInfoCard
                  :course="course"
                  :isEnrolled="isEnrolled"
                  @enroll="enrollCourse"
                />

                <CourseDetailsContent :course="course" />
              </div>

              <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                  التعليقات والتقييمات
                </h3>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AuthenticatedLayout>
  </div>
</template>

<script setup>
import CourseInfoCard from '@/Components/Course/CourseInfoCard.vue'
import CourseDetailsContent from '@/Components/Course/CourseDetailsContent.vue'
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";
import { router } from '@inertiajs/vue3';

const props = defineProps({
    course: {
        type: Object,
        required: true
    },
    isEnrolled: {
        type: Boolean,
        default: false
    },
    breadcrumbs: {
        type: Array,
        default: () => []
    }
});

const enrollCourse = () => {
    router.post(route('course.enroll', { id: props.course.id }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // يمكنك إضافة رسالة نجاح هنا
        },
        onError: (errors) => {
            // يمكنك إضافة رسالة خطأ هنا
        }
    });
};
</script>

<style scoped>
.dark .prose {
    color: #e2e8f0;
}

.dark .prose a {
    color: #60a5fa;
}

.dark .prose strong {
    color: #f3f4f6;
}

.dark .prose h1,
.dark .prose h2,
.dark .prose h3,
.dark .prose h4 {
    color: #f3f4f6;
}
</style>
