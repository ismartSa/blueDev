<template>
  <div class="course-details">


    <!-- Course Main Info -->
    <div class="course-header bg-gradient-to-r from-indigo-600 to-indigo-800 text-white p-8 rounded-lg shadow-lg">
      <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-8">
          <div class="md:w-1/2">
            <h1 class="course-title text-4xl font-bold mb-4">{{ course.title }}</h1>
            <p class="course-description text-lg opacity-90 mb-6">{{ course.description }}</p>
            <div class="flex items-center space-x-4 mb-6">
              <div class="flex items-center">
                <i class="far fa-clock mr-2"></i>
                <span>{{ statistics.durationFormatted }}</span>
              </div>
              <div class="flex items-center">
                <i class="far fa-user mr-2"></i>
                <span>{{ statistics.enrollmentsCount }} طالب</span>
              </div>
              <div class="flex items-center">
                <i class="far fa-play-circle mr-2"></i>
                <span>{{ statistics.lecturesCount }} درس</span>
              </div>
            </div>
            <div v-if="!enrollmentStatus.enrolled" class="enrollment-section">
              <button @click="enrollInCourse" class="bg-white text-indigo-600 px-6 py-3 rounded-lg font-bold hover:bg-indigo-50 transition duration-300 shadow-lg">
                سجل الآن
              </button>
            </div>
          </div>
          <div class="md:w-1/2">
            <div class="relative">
              <img :src="course.image" :alt="course.title" class="w-full h-auto rounded-xl shadow-2xl">
              <div class="absolute -bottom-4 -left-4 bg-yellow-400 text-gray-900 px-4 py-2 rounded-lg font-bold shadow-lg">
                {{ course.category || 'كورس احترافي' }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Course Statistics -->
    <div class="container mx-auto px-4 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
        <div class="p-6 rounded-lg border border-gray-200 bg-white shadow-md">
          <div class="text-4xl font-bold text-indigo-600 mb-2">{{ statistics.lecturesCount }}+</div>
          <div class="text-gray-600">درس تعليمي</div>
        </div>
        <div class="p-6 rounded-lg border border-gray-200 bg-white shadow-md">
          <div class="text-4xl font-bold text-indigo-600 mb-2">{{ statistics.enrollmentsCount }}+</div>
          <div class="text-gray-600">طالب مسجل</div>
        </div>
        <div class="p-6 rounded-lg border border-gray-200 bg-white shadow-md">
          <div class="text-4xl font-bold text-indigo-600 mb-2">{{ statistics.completionRate }}%</div>
          <div class="text-gray-600">نسبة الإكمال</div>
        </div>
        <div class="p-6 rounded-lg border border-gray-200 bg-white shadow-md">
          <div class="text-4xl font-bold text-indigo-600 mb-2">{{ statistics.durationFormatted }}</div>
          <div class="text-gray-600">مدة الدورة</div>
        </div>
      </div>
    </div>

    <!-- Enrollment Status -->
    <div v-if="!enrollmentStatus.enrolled" class="enrollment-section">
      <button @click="enrollInCourse" class="enroll-button">
        Enroll in Course
      </button>
    </div>

    <!-- User Progress -->
    <div v-if="enrollmentStatus.enrolled && progress" class="progress-section">
      <h3>Your Progress</h3>
      <div class="progress-stats">
        <p>Completed Lectures: {{ progress.completedLectures }}</p>
        <p v-if="progress.certificateEligible" class="certificate-eligible">
          Eligible for Certificate! 🎉
        </p>
      </div>
    </div>

    <!-- Course Content -->
    <div class="course-content">
      <h2>Course Content</h2>
      <div v-for="section in course.sections" :key="section.id" class="course-section">
        <h3>{{ section.title }}</h3>
        <p>{{ section.description }}</p>
        <ul class="lectures-list">
          <li v-for="lecture in section.lectures" :key="lecture.id" class="lecture-item">
            <span class="lecture-title">{{ lecture.title }}</span>
            <span class="lecture-duration">{{ formatDuration(lecture.duration) }}</span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Footer Component -->
    <Footer />
  </div>
</template>

<script>
import Footer from '@/Pages/Index/Partials/Footer.vue'
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import SwitchDarkMode from "@/Components/SwitchDarkMode.vue";
import { Head, Link } from "@inertiajs/vue3";
import SwitchLangNavbar from "@/Components/SwitchLangNavbar.vue";

export default {
  components: {
    Footer,
    ApplicationLogo,
    SwitchDarkMode,
    SwitchLangNavbar
  },
  props: {
    course: {
      type: Object,
      required: true
    },
    statistics: {
      type: Object,
      required: true
    },
    enrollmentStatus: {
      type: Object,
      required: true
    },
    progress: {
      type: Object,
      default: null
    },
    meta: {
      type: Object,
      required: true
    }
  },

  methods: {
    formatDuration(minutes) {
      const hours = Math.floor(minutes / 60)
      const remainingMinutes = minutes % 60
      return hours > 0
        ? `${hours} hour${hours > 1 ? 's' : ''} ${remainingMinutes} minute${remainingMinutes !== 1 ? 's' : ''}`
        : `${remainingMinutes} minute${remainingMinutes !== 1 ? 's' : ''}`
    },

    enrollInCourse() {
      this.$inertia.post(route('course.enroll', this.course.id))
    }
  }
}
</script>

<style>
@import 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css';

.course-details {
  @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8;
}

.course-header {
  background-image: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
}

.course-title {
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.stat-item {
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.enroll-button {
  box-shadow: 0 4px 15px rgba(74, 222, 128, 0.2);
}

.course-section {
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.lecture-item:hover {
  box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.1);
}

@media (max-width: 640px) {
  .course-stats {
    grid-template-columns: repeat(1, 1fr);
  }
}
</style>
