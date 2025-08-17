<template>
  <div :class="cardClasses">
    <!-- Course Image -->
    <div class="relative overflow-hidden">
      <img
        :src="courseImage"
        :alt="course.title"
        :class="imageClasses"
        loading="lazy"
      />
      
      <!-- Dynamic Badges -->
      <div class="absolute top-3 right-3">
        <span :class="badgeClasses.price">
          {{ priceText }}
        </span>
      </div>
      
      <div v-if="course.category" class="absolute top-3 left-3">
        <span :class="badgeClasses.category">
          {{ course.category.name }}
        </span>
      </div>
    </div>

    <!-- Course Content -->
    <div class="p-6">
      <!-- Course Title -->
      <h3 :class="titleClasses">
        {{ course.title }}
      </h3>
      
      <!-- Course Description -->
      <p :class="descriptionClasses">
        {{ course.description }}
      </p>
      
      <!-- Course Stats -->
      <div :class="statsContainerClasses">
        <div v-for="stat in courseStats" :key="stat.key" :class="statItemClasses">
          <component :is="stat.icon" class="h-4 w-4" />
          <span>{{ stat.value }}</span>
        </div>
      </div>
      
      <!-- Quiz Section -->
      <div v-if="course.quizzes && course.quizzes.length > 0" class="mb-4">
        <button 
          @click="$emit('toggle-quiz', course.id)"
          class="flex items-center justify-between w-full text-left p-3 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg hover:from-blue-100 hover:to-purple-100 transition-colors"
        >
          <div class="flex items-center space-x-2">
            <BookOpenIcon class="w-5 h-5 text-blue-600" />
            <span class="text-sm font-medium text-gray-700">Quiz Playlist</span>
            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
              {{ completedQuizCount }}/{{ course.quizzes.length }}
            </span>
          </div>
          <svg 
            class="w-4 h-4 text-gray-500 transition-transform"
            :class="{ 'rotate-180': expanded }"
            fill="currentColor" 
            viewBox="0 0 20 20"
          >
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>

      <!-- Action Buttons -->
      <div class="space-y-2">
        <!-- Primary Action -->
        <component
          :is="primaryAction.component"
          v-bind="primaryAction.props"
          :class="primaryAction.classes"
          @click="primaryAction.handler"
        >
          <component v-if="primaryAction.icon" :is="primaryAction.icon" class="h-4 w-4" />
          <span v-if="enrolling" :class="spinnerClasses"></span>
          {{ primaryAction.text }}
        </component>
        
        <!-- Secondary Actions -->
        <div class="flex gap-2">
          <Link
            :href="route('courses.details', { id: course.id, courseSlug: course.slug })"
            :class="secondaryButtonClasses"
          >
            {{ content.details }}
          </Link>
          
          <button
            @click="toggleWishlist"
            :class="wishlistButtonClasses"
          >
            <component :is="wishlistIcon" class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Expandable Quiz Playlist -->
    <div v-if="expanded && course.quizzes && course.quizzes.length > 0" class="border-t border-gray-200">
      <QuizPlaylist 
        :course-id="course.id" 
        :quizzes="course.quizzes" 
        @quiz-started="handleQuizStarted"
        @quiz-selected="handleQuizSelected"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
  ClockIcon,
  UsersIcon,
  BookOpenIcon,
  PlayIcon,
  HeartIcon
} from '@heroicons/vue/24/outline'
import { HeartIcon as HeartIconSolid } from '@heroicons/vue/24/solid'
import QuizPlaylist from '@/Components/Quiz/QuizPlaylist.vue'

// Props
const props = defineProps({
  course: { type: Object, required: true },
  expanded: { type: Boolean, default: false }
})

// Emits
const emit = defineEmits(['enroll', 'toggle-quiz', 'continue-learning'])

// Page data
const page = usePage()

// Reactive state with optimized initialization
const enrolling = ref(false)
const localWishlistStatus = ref(props.course.is_wishlisted)

// Watch for prop changes to sync local state
watch(() => props.course.is_wishlisted, (newValue) => {
  localWishlistStatus.value = newValue
})

// Authentication check with optional chaining for performance
const isAuthenticated = computed(() => page.props.auth?.user)

// Optimized computed properties for better performance
const courseImage = computed(() => 
  props.course.image || `https://picsum.photos/seed/${props.course.id}/400/300`
)

const isFree = computed(() => !props.course.price || props.course.price === 0)
const isWishlisted = computed(() => localWishlistStatus.value)
const isEnrolled = computed(() => props.course.user_enrolled)

const priceText = computed(() => 
  isFree.value ? content.value.free : `$${props.course.price}`
)

// Dynamic course state for better UX
const courseState = computed(() => {
  if (isEnrolled.value) return 'enrolled'
  if (enrolling.value) return 'enrolling'
  if (isFree.value) return 'free'
  return 'paid'
})

// Optimized class system with DRY principles
const baseClasses = {
  card: 'bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-all duration-300 group',
  image: 'w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105',
  badge: 'px-3 py-1 rounded-full font-medium backdrop-blur-sm',
  button: 'font-medium rounded-lg transition-colors flex items-center justify-center gap-2',
  text: 'transition-colors',
  input: 'border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500'
}

// Dynamic class bindings with performance optimization
const cardClasses = computed(() => baseClasses.card)
const imageClasses = computed(() => baseClasses.image)

const badgeClasses = computed(() => {
  const priceColors = isFree.value ? 'bg-green-500/90 text-white' : 'bg-blue-500/90 text-white'
  return {
    price: `${baseClasses.badge} text-sm ${priceColors}`,
    category: `${baseClasses.badge} text-xs bg-black/50 text-white`
  }
})

const titleClasses = computed(() => 
  `text-lg font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 ${baseClasses.text}`
)

const descriptionClasses = computed(() => 
  'text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-2'
)

const statsContainerClasses = computed(() => 
  'flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 mb-4'
)

const statItemClasses = computed(() => 'flex items-center gap-1')

const spinnerClasses = computed(() => 
  'animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full'
)

const secondaryButtonClasses = computed(() => 
  `flex-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 ${baseClasses.button} py-2 px-4 text-center text-sm`
)

const wishlistButtonClasses = computed(() => {
  const colors = isWishlisted.value
    ? 'bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-800/50 text-red-600 dark:text-red-400'
    : 'bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-400'
  return `px-3 py-2 rounded-lg ${baseClasses.text} ${colors}`
})

// Dynamic icon rendering
const wishlistIcon = computed(() => isWishlisted.value ? HeartIconSolid : HeartIcon)

// Quiz functionality
const completedQuizCount = computed(() => {
  if (!props.course.quizzes) return 0
  return props.course.quizzes.filter(quiz => quiz.is_completed).length
})

// Dynamic course stats
const courseStats = computed(() => [
  { key: 'duration', icon: ClockIcon, value: props.course.duration || '2h 30m' },
  { key: 'students', icon: UsersIcon, value: `${props.course.enrollments_count || 0} ${content.value.students}` },
  { key: 'lectures', icon: BookOpenIcon, value: `${props.course.lessons_count || 0} ${content.value.lectures}` }
])

// Optimized primary action with state-based rendering
const primaryAction = computed(() => {
  const actionConfig = {
    enrolled: {
      component: 'button',
      props: { disabled: false },
      classes: `w-full bg-green-600 hover:bg-green-700 text-white ${baseClasses.button} py-2.5 px-4`,
      icon: PlayIcon,
      text: content.value.continue,
      handler: handleContinueLearning
    },
    enrolling: {
      component: 'button',
      props: { disabled: true },
      classes: `w-full bg-blue-400 text-white ${baseClasses.button} py-2.5 px-4 cursor-not-allowed`,
      icon: null,
      text: content.value.enrolling,
      handler: null
    },
    free: {
      component: 'button',
      props: { disabled: false },
      classes: `w-full bg-green-600 hover:bg-green-700 text-white ${baseClasses.button} py-2.5 px-4`,
      icon: null,
      text: content.value.enrollFree,
      handler: handleEnroll
    },
    paid: {
      component: 'button',
      props: { disabled: false },
      classes: `w-full bg-blue-600 hover:bg-blue-700 text-white ${baseClasses.button} py-2.5 px-4`,
      icon: null,
      text: content.value.enroll,
      handler: handleEnroll
    }
  }
  
  return actionConfig[courseState.value]
})

// Optimized content with better organization
const content = computed(() => ({
  free: 'FREE',
  students: 'students',
  lectures: 'lectures',
  enroll: 'Enroll Now',
  enrollFree: 'Enroll Free',
  enrolling: 'Enrolling...',
  continue: 'Continue Learning',
  details: 'View Details'
}))

// Optimized methods with better error handling and performance
const handleEnroll = async () => {
  if (enrolling.value) return
  
  enrolling.value = true
  try {
    emit('enroll', props.course.id)
  } catch (error) {
    console.error('Enrollment error:', error)
  } finally {
    enrolling.value = false
  }
}

const toggleWishlist = async () => {
  // Authentication check with early return
  if (!isAuthenticated.value) {
    router.visit(route('login'))
    return
  }
  
  // Optimistic update with error recovery
  const originalStatus = localWishlistStatus.value
  localWishlistStatus.value = !originalStatus
  
  try {
    await router.post(route('courses.wishlist.toggle', props.course.id), {}, {
      preserveScroll: true,
      onSuccess: () => {
        // Reload only necessary data for better performance
        router.reload({ only: ['courses'] })
      },
      onError: (errors) => {
        // Revert optimistic update and log error
        localWishlistStatus.value = originalStatus
        console.error('Wishlist toggle failed:', errors)
      }
    })
  } catch (error) {
    // Fallback error handling
    localWishlistStatus.value = originalStatus
    console.error('Wishlist operation failed:', error)
  }
}

// Continue learning handler
const handleContinueLearning = () => {
  emit('continue-learning', props.course.id)
}

// Quiz event handlers
const handleQuizStarted = (quiz) => {
  console.log('Quiz started:', quiz)
}

const handleQuizSelected = (quiz) => {
  console.log('Quiz selected:', quiz)
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