<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/Breadcrumb.vue";

import { ref } from "vue";
import axios from "axios";

const props = defineProps({
    title: String,
    course: Object,
    sections: Array,
    lectures: Array,
    firstLecture: Object,
    breadcrumbs: Array,
    completionPercentage: Number,
});

// حالات الأكورديون لكل قسم
const openSections = ref({});

// تفعيل الأكورديون عند النقر
function toggleSection(sectionId) {
    openSections.value[sectionId] = !openSections.value[sectionId];
}

const currentLecture = ref({
    id: props.firstLecture.id,
    title: props.firstLecture.title,
    video_url: props.firstLecture.video_url,
    previous_lecture_id: props.firstLecture.previous_lecture_id,
    next_lecture_id: props.firstLecture.next_lecture_id,
    completed: props.firstLecture.completed
});

// تحديث المحاضرة الحالية عند اختيار محاضرة جديدة
function updateLecture(lecture) {
    currentLecture.value = {
        id: lecture.id,
        video_url: lecture.video_url,
        previous_lecture_id: lecture.previous_lecture_id,
        next_lecture_id: lecture.next_lecture_id,
        completed: lecture.completed
    };
}

function onVideoEnded() {
    // إرسال طلب لتحديث حالة المحاضرة
    axios.post('/lectures/mark-completed', { lecture_id: currentLecture.value.id })
        .then(response => {
            console.log(response.data.message);

            // الانتقال إلى المحاضرة التالية تلقائيًا
            if (currentLecture.value.next_lecture_id) {
                router.push(`/courses/${props.course.id}/player/${props.course.slug}/Watch/${currentLecture.value.next_lecture_id}`);
            } else {
                alert("Congratulations! You have completed all lectures.");
            }
        })
        .catch(error => {
            console.error(error);
        });
}

// التعامل مع الانتقال إلى المحاضرة السابقة
function goToPreviousLecture() {
    if (currentLecture.value.previous_lecture_id) {
        router.push(`/courses/${props.course.id}/player/${props.course.slug}/Watch/${currentLecture.value.previous_lecture_id}`);
    }
}


</script>

<template>
    <AuthenticatedLayout>
        <Breadcrumb :title="title" :breadcrumbs="breadcrumbs" />

            <!-- عرض نسبة الإنجاز باستخدام دائرة -->
            <div class="mb-4 flex items-center space-x-2">
    <span class="text-sm font-medium text-green-700 dark:text-green-500">
        Completion: {{ completionPercentage.toFixed(2) }}%
    </span>
    <div class="relative w-40 bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
        <div
            class="absolute top-0 left-0 bg-green-600 h-2.5 rounded-full dark:bg-green-500"
            :style="{ width: `${completionPercentage.toFixed(2)}%` }">
        </div>
    </div>
</div>

        <div class="space-y-4">
            <div class="flex justify-between space-x-4">

                <!-- قسم الفيديو -->
                <div class="w-2/3 bg-white dark:bg-slate-800 shadow sm:rounded-lg p-4">
                      <!-- إضافة عنوان المحاضرة فوق الفيديو -->
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
                        {{ currentLecture.title }}
                    </h3>
                    <div class="video-container mb-4">
                        <video controls class="w-full h-auto rounded-lg" @ended="onVideoEnded">
                            <source :src="currentLecture.video_url" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                       {{   currentLecture.id}}
                    </div>
                             <!-- أزرار التنقل بين الدروس -->
                            <div class="flex justify-between mt-4">
                        <Link
                            v-if="currentLecture.previous_lecture_id"
                            :href="`/courses/${course.id}/player/${course.slug}/watch/${currentLecture.previous_lecture_id}`"
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded">
                            Previous Lesson
                        </Link>
                        <Link
                            v-if="currentLecture.next_lecture_id"
                            :href="`/courses/${course.id}/player/${course.slug}/watch/${currentLecture.next_lecture_id}`"
                            class="bg-green-500 text-white px-4 py-2 rounded">
                            Next Lesson
                        </Link>
                     </div>
                    <!-- أزرار التنقل بين الدروس -->
                    <div class="flex justify-between mt-4">
                    <button
                        v-if="currentLecture.previous_lecture_id"
                        @click="goToPreviousLecture"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded">
                        Previous Lesson
                    </button>
                    <button
                        v-if="currentLecture.next_lecture_id"
                        @click="onVideoEnded"
                        class="bg-green-500 text-white px-4 py-2 rounded">
                        Next Lesson
                    </button>
                </div>
                </div>

    <!-- قسم قائمة الدروس باستخدام الأكورديون -->
  <div class="w-1/3 bg-gray-50 dark:bg-slate-700 shadow sm:rounded-lg p-4">
    <h2 class="text-xl font-bold mb-4">Course Content</h2>
    <div id="accordion-collapse" data-accordion="collapse">
      <!-- تكرار للأقسام -->
      <div
        v-for="section in sections.sort((a, b) => a.order - b.order)"
        :key="section.id"
        class="mt-4"
      >
        <!-- زر عنوان القسم -->
        <h2 :id="`accordion-collapse-heading-${section.id}`">
          <button
            type="button"
            @click="toggleSection(section.id)"
            class="flex items-center justify-between w-full p-3 font-medium text-left text-gray-500 border border-gray-200 dark:border-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 gap-3"
          >
            <span>{{ section.title }}</span>
            <svg
              data-accordion-icon
              class="w-3 h-3 transition-transform duration-200"
              :class="openSections[section.id] ? 'rotate-180' : 'rotate-0'"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 10 6"
            >
              <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5 5 1 1 5"
              />
            </svg>
          </button>
        </h2>
        <!-- عرض قائمة المحاضرات عند فتح القسم -->
        <div
          :id="`accordion-collapse-body-${section.id}`"
          v-show="openSections[section.id]"
          class="p-3 border border-gray-200 dark:border-gray-700"
        >
          <ul>
            <!-- تكرار المحاضرات داخل القسم -->
            <li
              v-for="lecture in lectures.filter(lec => lec.section_id === section.id)"
              :key="lecture.id"
              class="mb-2 ml-4"
            >
              <Link
                :href="`/courses/${course.id}/player/${course.slug}/watch/${lecture.id}`"
                class="text-left focus:outline-none flex items-center p-2 rounded hover:bg-blue-200 dark:hover:bg-blue-800"
                :class="lecture.completed ? 'bg-green-100 text-green-700' : ''"
              >
                <span class="flex-grow">{{ lecture.title }} </span>
                <span v-if="lecture.completed">✅</span> <!-- عرض العلامة إذا كانت المحاضرة مكتملة -->
              </Link>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
