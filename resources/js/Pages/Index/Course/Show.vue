<template>
    <div class="bg-gray-50">
      <Head :title="meta.title" :description="meta.description" />

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumbs -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
          <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
              <Link href="/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
                <i class="fas fa-home mr-2"></i>
                Home
              </Link>
            </li>
            <li>
              <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400"></i>
                <Link href="/courses" class="ml-1 text-sm font-medium text-gray-700 hover:text-indigo-600 md:ml-2">Courses</Link>
              </div>
            </li>
            <li aria-current="page">
              <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400"></i>
                <span class="ml-1 text-sm font-medium text-indigo-600 md:ml-2">{{ course.title }}</span>
              </div>
            </li>
          </ol>
        </nav>

        <!-- Course Header -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
          <div class="md:flex">
            <div class="md:w-2/3 p-8">
              <div class="flex items-center mb-2">
                <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ course.level }}</span>
                <span class="ml-2 text-sm text-gray-500">
                  <i class="fas fa-star text-yellow-400"></i> {{ course.rating }} ({{ course.reviews_count }} reviews)
                </span>
              </div>
              <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ course.title }}</h1>
              <p class="text-gray-600 mb-6">{{ course.description }}</p>

              <div class="flex items-center mb-4" v-if="course.instructors">
                <div class="flex -space-x-2">
                  <img v-for="instructor in course.instructors"
                       :key="instructor.id"
                       class="w-8 h-8 rounded-full border-2 border-white"
                       :src="instructor.avatar"
                       :alt="instructor.name">
                </div>
                <span class="ml-3 text-sm text-gray-500" v-if="course.instructors.length > 3">+{{ course.instructors.length - 3 }} instructors</span>
              </div>

              <div class="flex flex-wrap gap-4">
                <div class="flex items-center text-gray-600">
                  <i class="fas fa-clock mr-2 text-indigo-500"></i>
                  <span>{{ statistics.durationFormatted }}</span>
                </div>
                <div class="flex items-center text-gray-600">
                  <i class="fas fa-layer-group mr-2 text-indigo-500"></i>
                  <span>{{ course.sections_count }} modules</span>
                </div>
                <div v-if="course.certificate" class="flex items-center text-gray-600">
                  <i class="fas fa-certificate mr-2 text-indigo-500"></i>
                  <span>Certificate</span>
                </div>
              </div>
            </div>

            <!-- Enrollment Section -->
            <div class="md:w-1/3 bg-gray-50 p-8 border-l border-gray-200">
              <div class="sticky top-8">
                <div class="video-container mb-6 rounded-lg overflow-hidden">
                  <iframe :src="course.preview_video" frameborder="0" allowfullscreen></iframe>
                </div>

                <div class="bg-indigo-50 p-4 rounded-lg mb-6" v-if="enrollmentStatus?.enrolled">
                  <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-700">Course Progress</span>
                    <span class="text-sm font-medium text-indigo-600">{{ progress || 0 }}%</span>
                  </div>
                  <div class="progress-bar">
                    <div class="progress-value" :style="`width: ${progress || 0}%`"></div>
                  </div>
                  <div class="mt-2 text-xs text-gray-500" v-if="progress >= 80">
                    <i class="fas fa-certificate text-yellow-500 mr-1"></i>
                    Eligible for certificate
                  </div>
                </div>

                <div class="mb-6">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-2xl font-bold text-gray-900">${{ course.price }}</span>
                    <span v-if="course.original_price" class="text-sm text-gray-500 line-through">${{ course.original_price }}</span>
                  </div>
                  <button
                    @click="enrollCourse"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                    {{ enrollmentStatus.enrolled ? 'Continue Course' : 'Enroll Now' }}
                  </button>
                </div>

                <div class="border-t border-gray-200 pt-4">
                  <h3 class="text-sm font-medium text-gray-700 mb-3">This course includes:</h3>
                  <ul class="space-y-2">
                    <li v-for="feature in course.features" :key="feature" class="flex items-center text-sm text-gray-600">
                      <i class="fas fa-video mr-3 text-indigo-500"></i>
                      <span>{{ feature }}</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Course Tabs -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
          <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
              <button @click="activeTab = 'overview'"
                      :class="tabClasses('overview')">
                Overview
              </button>
              <button @click="activeTab = 'curriculum'"
                      :class="tabClasses('curriculum')">
                Curriculum
              </button>
              <button @click="activeTab = 'instructors'"
                      :class="tabClasses('instructors')">
                Instructors
              </button>
              <button @click="activeTab = 'reviews'"
                      :class="tabClasses('reviews')">
                Reviews
              </button>
            </nav>
          </div>

          <!-- Tab Contents -->
          <div class="p-8">
            <!-- Overview Tab -->
            <div v-show="activeTab === 'overview'" class="tab-content">
              <h2 class="text-2xl font-bold text-gray-900 mb-6">About This Course</h2>
              <p class="text-gray-600 mb-6">{{ course.long_description }}</p>

              <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">What You'll Learn</h3>
                <div class="grid md:grid-cols-2 gap-4">
                  <div v-for="(learning, index) in course.learnings" :key="index" class="flex items-start">
                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                    <span class="text-gray-600">{{ learning }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Curriculum Tab -->
            <div v-show="activeTab === 'curriculum'" class="tab-content">
              <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Course Curriculum</h2>
                <div class="text-sm text-gray-500">
                  {{ course.sections_count || 0 }} modules • {{ statistics?.lecturesCount || 0 }} lessons • {{ statistics?.durationFormatted || '0h 0m' }}
                </div>
              </div>

              <div class="space-y-4">
                <template v-if="course.sections && course.sections.length > 0">
                  <div v-for="section in course.sections" :key="section.id"
                       class="border border-gray-200 rounded-lg overflow-hidden">
                    <button @click="toggleSection(section.id)"
                            class="module-toggle w-full flex justify-between items-center p-4 bg-gray-50 hover:bg-gray-100">
                      <div class="flex items-center">
                        <i class="fas fa-folder-open text-indigo-500 mr-3"></i>
                        <span class="font-medium">{{ section.title }}</span>
                      </div>
                      <div class="flex items-center">
                        <span class="text-sm text-gray-500 mr-4">
                          {{ section.lectures_count || 0 }} lessons • {{ formatSectionDuration(section) }}
                        </span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"
                           :class="{ 'rotate-180': openSections.includes(section.id) }"></i>
                      </div>
                    </button>
                    <div v-show="openSections.includes(section.id)" class="module-content">
                      <div class="divide-y divide-gray-200">
                        <template v-if="section.lectures && section.lectures.length > 0">
                          <div v-for="lecture in section.lectures" :key="lecture.id"
                               class="module-item p-4 hover:bg-indigo-50 cursor-pointer flex items-center">
                            <i class="fas fa-play-circle text-indigo-500 mr-4"></i>
                            <div class="flex-grow">
                              <div class="flex justify-between">
                                <span>{{ lecture.title }}</span>
                                <span class="text-sm text-gray-500">{{ formatDuration(lecture.duration || 0) }}</span>
                              </div>
                              <div v-if="lecture.is_preview" class="text-sm text-gray-500 mt-1">Free preview</div>
                            </div>
                          </div>
                        </template>
                        <div v-else class="p-4 text-center text-gray-500">
                          لا توجد محاضرات متاحة في هذا القسم حتى الآن.
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
                <div v-else class="text-center py-8 bg-white rounded-lg border border-gray-200">
                  <i class="fas fa-book-open text-indigo-400 text-4xl mb-4"></i>
                  <p class="text-gray-500">لا توجد أقسام منهج دراسي متاحة لهذه الدورة حتى الآن.</p>
                </div>
              </div>
            </div>

            <!-- Instructors Tab -->
            <div v-show="activeTab === 'instructors'" class="tab-content">
              <h2 class="text-2xl font-bold text-gray-900 mb-6">Meet Your Instructors</h2>
              <div class="grid md:grid-cols-2 gap-8">
                <div v-for="instructor in course.instructors" :key="instructor.id" class="flex items-start">
                  <img class="w-16 h-16 rounded-full object-cover mr-4"
                       :src="instructor.avatar"
                       :alt="instructor.name">
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ instructor.name }}</h3>
                    <p class="text-sm text-indigo-600 mb-2">{{ instructor.position }}</p>
                    <p class="text-gray-600 text-sm">{{ instructor.bio }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Reviews Tab -->
            <div v-show="activeTab === 'reviews'" class="tab-content">
              <div class="flex justify-between items-center mb-6">
                <div>
                  <h2 class="text-2xl font-bold text-gray-900">Student Reviews</h2>
                  <div class="flex items-center mt-2">
                    <div class="flex items-center">
                      <i v-for="star in 5" :key="star"
                         class="fas fa-star"
                         :class="star <= course.rating ? 'text-yellow-400' : 'text-gray-300'"></i>
                    </div>
                    <span class="ml-2 text-sm text-gray-500">
                      {{ course.rating }} out of 5 ({{ course.reviews_count }} reviews)
                    </span>
                  </div>
                </div>
                <button v-if="enrollmentStatus.enrolled"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                  Write a Review
                </button>
              </div>

              <div class="space-y-6">
                <div v-for="review in course.reviews" :key="review.id"
                     class="border-b border-gray-200 pb-6">
                  <!-- Review content here -->
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  import { Head, Link } from '@inertiajs/vue3'


  export default {
    components: { Head, Link },

    props: {
      course: Object,
      statistics: Object,
      enrollmentStatus: Object,
      progress: Number,
      meta: Object
    },

    data() {
      return {
        activeTab: 'overview',
        openSections: []
      }
    },

    methods: {
      tabClasses(tabName) {
        return [
          'tab-button py-4 px-6 text-sm font-medium text-center border-b-2',
          this.activeTab === tabName
            ? 'border-indigo-500 text-indigo-600'
            : 'border-transparent hover:text-gray-700 hover:border-gray-300'
        ]
      },

      toggleSection(sectionId) {
        const index = this.openSections.indexOf(sectionId)
        if (index > -1) {
          this.openSections.splice(index, 1)
        } else {
          this.openSections.push(sectionId)
        }
      },

      formatDuration(minutes) {
        const hours = Math.floor(minutes / 60)
        const mins = minutes % 60
        return `${hours}h ${mins}m`
      },

      formatSectionDuration(section) {
        const totalMinutes = section.lectures.reduce((sum, lecture) => sum + lecture.duration, 0)
        return this.formatDuration(totalMinutes)
      },

      enrollCourse() {
        if (this.enrollmentStatus.enrolled) {
          // Navigate to first lecture
        } else {
          this.$inertia.post(route('enrollments.store', { course_id: this.course.id }))
        }
      }
    }
  }
  </script>

  <style>
  .progress-bar {
    height: 6px;
    background-color: #e0e0e0;
    border-radius: 3px;
  }
  .progress-value {
    height: 100%;
    border-radius: 3px;
    background-color: #4f46e5;
    transition: width 0.3s ease;
  }
  </style>


