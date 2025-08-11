<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-all duration-300 group">
    <!-- Course Image -->
    <div class="relative overflow-hidden">
      <img
        :src="courseImage"
        :alt="course.title"
        class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105"
        loading="lazy"
      />
      
      <!-- Price Badge -->
      <div class="absolute top-3 right-3">
        <span :class="priceClasses" class="px-3 py-1 rounded-full text-sm font-medium backdrop-blur-sm">
          {{ priceText }}
        </span>
      </div>
      
      <!-- Category Badge -->
      <div v-if="course.category" class="absolute top-3 left-3">
        <span class="bg-black/50 text-white px-3 py-1 rounded-full text-xs font-medium backdrop-blur-sm">
          {{ course.category.name }}
        </span>
      </div>
    </div>

    <!-- Course Content -->
    <div class="p-6">
      <!-- Course Title -->
      <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
        {{ course.title }}
      </h3>
      
      <!-- Course Description -->
      <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-2">
        {{ course.description }}
      </p>
      
      <!-- Course Stats -->
      <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 mb-4">
        <div class="flex items-center gap-1">
          <ClockIcon class="h-4 w-4" />
          <span>{{ course.duration || '2h 30m' }}</span>
        </div>
        <div class="flex items-center gap-1">
          <UsersIcon class="h-4 w-4" />
          <span>{{ course.enrollments_count || 0 }} {{ content.students }}</span>
        </div>
        <div class="flex items-center gap-1">
          <BookOpenIcon class="h-4 w-4" />
          <span>{{ course.lessons_count || 0 }} {{ content.lessons }}</span>
        </div>
      </div>
      
      <!-- Action Buttons -->
      <div class="space-y-2">
        <!-- Primary Action -->
        <button
          v-if="!course.user_enrolled"
          @click="handleEnroll"
          :disabled="enrolling"
          class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-medium py-2.5 px-4 rounded-lg transition-colors flex items-center justify-center gap-2"
        >
          <span v-if="enrolling" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
          {{ enrolling ? content.enrolling : content.enroll }}
        </button>
        
        <Link
          v-else
          :href="route('courses.learn', { courseId: course.id, courseSlug: course.slug })"
          class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 px-4 rounded-lg transition-colors flex items-center justify-center gap-2"
        >
          <PlayIcon class="h-4 w-4" />
          {{ content.continue }}
        </Link>
        
        <!-- Secondary Actions -->
        <div class="flex gap-2">
          <Link
            :href="route('courses.details', { id: course.id, courseSlug: course.slug })"
            class="flex-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium py-2 px-4 rounded-lg transition-colors text-center text-sm"
          >
            {{ content.details }}
          </Link>
          
          <button
            @click="toggleWishlist"
            :class="wishlistClasses"
            class="px-3 py-2 rounded-lg transition-colors"
          >
            <HeartIcon :class="course.is_wishlisted ? 'fill-current' : ''" class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
  ClockIcon,
  UsersIcon,
  BookOpenIcon,
  PlayIcon,
  HeartIcon
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  course: { type: Object, required: true }
})

// Emits
const emit = defineEmits(['enroll'])

// Page data
const page = usePage()

// Reactive state
const enrolling = ref(false)

// Check if user is authenticated
const isAuthenticated = computed(() => {
  return page.props.auth && page.props.auth.user
})

// Computed properties
const courseImage = computed(() => {
  if (props.course.image) return props.course.image
  return `https://picsum.photos/seed/${props.course.id}/400/300`
})

const priceText = computed(() => {
  if (props.course.price === 0 || !props.course.price) return content.value.free
  return `$${props.course.price}`
})

const priceClasses = computed(() => {
  const isFree = props.course.price === 0 || !props.course.price
  return isFree
    ? 'bg-green-500/90 text-white'
    : 'bg-blue-500/90 text-white'
})

const wishlistClasses = computed(() => {
  return props.course.is_wishlisted
    ? 'bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-800/50 text-red-600 dark:text-red-400'
    : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-400'
})

// Content
const content = computed(() => ({
  free: 'FREE',
  students: 'students',
  lessons: 'lessons',
  enroll: 'Enroll Now',
  enrolling: 'Enrolling...',
  continue: 'Continue Learning',
  details: 'View Details'
}))

// Methods
const handleEnroll = async () => {
  if (enrolling.value) return
  
  enrolling.value = true
  try {
    emit('enroll', props.course.id)
  } finally {
    enrolling.value = false
  }
}

const toggleWishlist = () => {
  // Check if user is authenticated
  if (!isAuthenticated.value) {
    // Redirect to login page for guest users
    router.visit(route('login'))
    return
  }
  
  router.post(route('courses.wishlist.toggle', props.course.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Update handled by parent component
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