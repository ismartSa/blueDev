<template>
  <div class="course-details">
    <!-- Course Main Info -->
    <div class="course-header">
      <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-8">
          <div class="md:w-1/2">
            <h1 class="course-title">{{ course.title }}</h1>
            <p class="course-description">{{ course.description }}</p>
            <div class="course-meta">
              <div v-for="(item, index) in courseMetaItems" :key="index" class="meta-item">
                <i :class="item.icon"></i>
                <span>{{ item.value }}</span>
              </div>
            </div>
            <EnrollButton 
              v-if="!enrollmentStatus.enrolled" 
              :is-enrolling="isEnrolling" 
              @enroll="enrollInCourse"
              class="enrollment-section"
            />
          </div>
          <div class="md:w-1/2">
            <div class="course-image-container">
              <img :src="course.image" :alt="course.title" class="course-image">
              <div class="course-category">{{ course.category || 'كورس احترافي' }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Course Statistics -->
    <div class="container mx-auto px-4 py-12">
      <div class="stats-grid">
        <div v-for="(stat, index) in statisticsItems" :key="index" class="stat-card">
          <div class="stat-value">{{ stat.value }}</div>
          <div class="stat-label">{{ stat.label }}</div>
        </div>
      </div>
    </div>

    <!-- Secondary Enrollment Button -->
    <EnrollButton 
      v-if="!enrollmentStatus.enrolled" 
      :is-enrolling="isEnrolling" 
      @enroll="enrollInCourse"
      :text="{ enrolling: 'Enrolling...', default: 'Enroll in Course' }"
      class="enrollment-section secondary-enroll"
    />

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
import EnrollButton from '@/Components/EnrollButton.vue'
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import SwitchDarkMode from "@/Components/SwitchDarkMode.vue";
import { Head, Link } from "@inertiajs/vue3";
import SwitchLangNavbar from "@/Components/SwitchLangNavbar.vue";

export default {
  components: {
    Footer,
    EnrollButton,
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
  data() {
    return {
      isEnrolling: false
    };
  },

  computed: {
    // Dynamic course meta items for DRY principle
    courseMetaItems() {
      return [
        {
          icon: 'far fa-clock mr-2',
          value: this.statistics.durationFormatted
        },
        {
          icon: 'far fa-user mr-2',
          value: `${this.statistics.enrollmentsCount} طالب`
        },
        {
          icon: 'far fa-play-circle mr-2',
          value: `${this.statistics.lecturesCount} درس`
        }
      ]
    },

    // Dynamic statistics items for DRY principle
    statisticsItems() {
      return [
        {
          value: `${this.statistics.lecturesCount}+`,
          label: 'درس تعليمي'
        },
        {
          value: `${this.statistics.enrollmentsCount}+`,
          label: 'طالب مسجل'
        },
        {
          value: `${this.statistics.completionRate}%`,
          label: 'نسبة الإكمال'
        },
        {
          value: this.statistics.durationFormatted,
          label: 'مدة الدورة'
        }
      ]
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
      // Show loading state
      this.isEnrolling = true;
      
      this.$inertia.post(route('courses.enroll', this.course.id), {}, {
        onSuccess: () => {
          this.isEnrolling = false;
          // Update enrollment status locally for immediate UI feedback
          this.enrollmentStatus.enrolled = true;
        },
        onError: (errors) => {
          this.isEnrolling = false;
          console.error('Enrollment failed:', errors);
        }
      });
    }
  }
}
</script>

<style>
@import 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css';

/* Base layout */
.course-details {
  @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8;
}

/* Header section */
.course-header {
  @apply bg-gradient-to-r from-indigo-600 to-indigo-800 text-white p-8 rounded-lg shadow-lg;
}

.course-title {
  @apply text-4xl font-bold mb-4;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.course-description {
  @apply text-lg opacity-90 mb-6;
}

/* Course meta and statistics */
.course-meta {
  @apply flex items-center space-x-4 mb-6;
}

.meta-item {
  @apply flex items-center;
}

.stats-grid {
  @apply grid grid-cols-1 md:grid-cols-4 gap-8 text-center;
}

.stat-card {
  @apply p-6 rounded-lg border border-gray-200 bg-white shadow-md;
}

.stat-value {
  @apply text-4xl font-bold text-indigo-600 mb-2;
}

.stat-label {
  @apply text-gray-600;
}

/* Image container */
.course-image-container {
  @apply relative;
}

.course-image {
  @apply w-full h-auto rounded-xl shadow-2xl;
}

.course-category {
  @apply absolute -bottom-4 -left-4 bg-yellow-400 text-gray-900 px-4 py-2 rounded-lg font-bold shadow-lg;
}

/* Enrollment sections */
.enrollment-section {
  @apply mb-4;
}

.secondary-enroll {
  @apply text-center py-8;
}

/* Course content */
.course-section {
  @apply border border-gray-100 rounded-lg p-4 mb-4;
}

.lecture-item {
  @apply flex justify-between items-center p-2 rounded hover:bg-gray-50 transition-colors;
}

.lecture-item:hover {
  box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.1);
}

/* Responsive design */
@media (max-width: 640px) {
  .stats-grid {
    @apply grid-cols-1;
  }
  
  .course-meta {
    @apply flex-col space-x-0 space-y-2;
  }
}
</style>
