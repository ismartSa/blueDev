<template>
    <Head title="Course Details" />
    <AuthenticatedLayout>
        <template #header>
            <div :class="UI.HEADER" v-memo="[enrollmentStatus]">
                <div :class="UI.CONTAINER">
                    <div :class="UI.HEADER_CONTENT">
                        <div :class="UI.HEADER_LEFT">
                            <BookOpenIcon :class="UI.HEADER_ICON" />
                            <div>
                                <h1 :class="UI.HEADER_TITLE">{{ course.title }}</h1>
                                <p :class="UI.HEADER_SUBTITLE">{{ course.name }} • {{ sectionsCount }} sections</p>
                            </div>
                        </div>
                        <div :class="UI.HEADER_RIGHT">
                            <div class="w-2 h-2 rounded-full" :class="enrollmentStatus.color"></div>
                            <span :class="UI.HEADER_SUBTITLE">{{ enrollmentStatus.text }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div :class="UI.MAIN">
            <!-- Flash Messages -->
            <Transition v-bind="UI.TRANSITION">
                <div v-if="$page.props.flash.success" :class="UI.FLASH_CONTAINER">
                    <div :class="UI.FLASH_SUCCESS">
                        <CheckCircleIcon :class="UI.FLASH_ICON" />
                        <span class="font-medium">{{ $page.props.flash.success }}</span>
                    </div>
                </div>
            </Transition>

            <div :class="UI.CONTAINER">
                <div :class="UI.GRID">
                    <!-- Course Navigation Sidebar -->
                    <div class="lg:col-span-3">
                        <nav :class="UI.SIDEBAR" v-memo="[activeSection, isAdmin]">
                            <!-- Course Overview -->
                            <button
                                @click="setActiveSection('overview')"
                                :class="sidebarClasses.overview"
                            >
                                <InformationCircleIcon :class="UI.SIDEBAR_ICON" />
                                <span class="truncate">Course Overview</span>
                                <ChevronRightIcon v-if="activeSection === 'overview'" :class="UI.SIDEBAR_CHEVRON" />
                            </button>
                            
                            <!-- Course Sections -->
                            <button
                                v-for="section in sortedSections"
                                :key="section.id"
                                @click="setActiveSection(section.id)"
                                :class="sidebarClasses[section.id]"
                            >
                                <component :is="sectionIcons[section.id] || 'FolderIcon'" :class="UI.SIDEBAR_ICON" />
                                <span class="truncate">{{ section.title }}</span>
                                <ChevronRightIcon v-if="activeSection === section.id" :class="UI.SIDEBAR_CHEVRON" />
                            </button>
                        </nav>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-9">
                        <!-- Loading State -->
                        <div v-if="!isInitialized" :class="UI.CONTENT_CARD">
                            <div class="flex items-center justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                <span class="ml-3 text-gray-600 dark:text-gray-400">Loading course content...</span>
                            </div>
                        </div>
                        
                        <!-- No Data State -->
                        <div v-else-if="!hasValidData" :class="UI.CONTENT_CARD">
                            <div class="flex flex-col items-center justify-center">
                                <BookOpenIcon class="w-12 h-12 text-gray-400 mb-4" />
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Course Content Available</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-center">This course doesn't have any sections or content yet.</p>
                            </div>
                        </div>
                        
                        <!-- Success/Error Message -->
                        <div v-if="message.show" :class="[
                            'fixed top-4 right-4 z-50 max-w-md p-4 rounded-lg shadow-lg transition-all duration-300',
                            message.type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'
                        ]">
                            <div class="flex items-center gap-3">
                                <component :is="message.type === 'success' ? 'CheckCircleIcon' : 'ExclamationTriangleIcon'" 
                                    :class="[
                                        'h-5 w-5',
                                        message.type === 'success' ? 'text-green-600' : 'text-red-600'
                                    ]" />
                                <p class="text-sm font-medium">{{ message.text }}</p>
                                <button @click="message.show = false" class="ml-auto text-gray-400 hover:text-gray-600">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Course Overview Section -->
                        <div v-show="activeSection === 'overview'" v-memo="[activeSection]">
                            <div :class="UI.CONTENT_CARD">
                                <!-- Course Image -->
                                <div class="px-6 py-4">
                                    <div class="rounded-lg overflow-hidden w-full max-w-md mx-auto">
                                        <img :src="course.image" :alt="`${course.title} Image`" class="w-full h-48 object-cover" />
                                    </div>
                                </div>
                                
                                <div :class="UI.CONTENT_HEADER">
                                    <h3 :class="UI.CONTENT_TITLE">
                                        <InformationCircleIcon class="w-5 h-5 mr-2" />
                                        Course Information
                                    </h3>
                                </div>
                                <div :class="UI.CONTENT_BODY">
                                    <div :class="UI.CONTENT_GRID">
                                        <!-- Course Description -->
                                        <div :class="UI.SETTING_ITEM">
                                            <h4 :class="UI.SETTING_LABEL">Description</h4>
                                            <p class="text-gray-600 dark:text-gray-300">{{ course.description }}</p>
                                        </div>
                                        
                                        <!-- Course Info Grid -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div :class="UI.SETTING_ITEM">
                                                <h4 :class="UI.SETTING_LABEL">Instructor</h4>
                                                <p class="text-gray-600 dark:text-gray-300">{{ course.name }}</p>
                                            </div>
                                            
                                            <div v-if="hasContent(course.prerequisites)" :class="UI.SETTING_ITEM">
                                                <h4 :class="UI.SETTING_LABEL">Prerequisites</h4>
                                                <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300 space-y-1">
                                                    <li v-for="(item, index) in course.prerequisites" :key="index" class="text-sm">{{ item }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <!-- Learning Outcomes -->
                                        <div v-if="hasContent(course.learningOutcomes)" :class="UI.SETTING_ITEM">
                                            <h4 :class="UI.SETTING_LABEL">Learning Outcomes</h4>
                                            <ul class="list-disc ml-4 text-gray-600 dark:text-gray-300 space-y-1">
                                                <li v-for="(item, index) in course.learningOutcomes" :key="index" class="text-sm">{{ item }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Course Sections Content -->
                        <div v-for="section in sortedSections" :key="section.id" v-show="activeSection === section.id" v-memo="[activeSection, section.id]">
                            <div :class="UI.CONTENT_CARD">
                                <div :class="UI.CONTENT_HEADER">
                                    <h3 :class="UI.CONTENT_TITLE">
                                        <component :is="sectionIcons[section.id] || 'FolderIcon'" class="w-5 h-5 mr-2" />
                                        {{ section.title }}
                                    </h3>
                                </div>
                                <div :class="UI.CONTENT_BODY">
                                    <Section
                                        :section="section"
                                        :lectures="getLecturesBySection(section.id)"
                                        :course-slug="course.slug"
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
import { ref, onMounted, computed, nextTick, shallowRef } from 'vue'
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {
    CheckCircleIcon, BookOpenIcon, ChevronRightIcon, InformationCircleIcon,
    FolderIcon, ExclamationTriangleIcon, UserIcon, PlusIcon
} from '@heroicons/vue/24/outline'
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
    enrolled: { type: Boolean, default: false }
});

// Consolidated UI Configuration - DRY principle applied
const UI = {
    // Layout classes
    HEADER: 'bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700',
    CONTAINER: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
    MAIN: 'min-h-screen bg-gray-50 dark:bg-gray-900 py-6',
    GRID: 'lg:grid lg:grid-cols-12 gap-6',
    CARD: 'bg-white dark:bg-gray-800 shadow-sm rounded-2xl p-6 mt-6',
    
    // Header components
    HEADER_CONTENT: 'flex items-center justify-between h-16',
    HEADER_LEFT: 'flex items-center space-x-4',
    HEADER_RIGHT: 'flex items-center space-x-2',
    HEADER_ICON: 'w-8 h-8 text-gray-600 dark:text-gray-400',
    HEADER_TITLE: 'text-2xl font-semibold text-gray-900 dark:text-white',
    HEADER_SUBTITLE: 'text-sm text-gray-500 dark:text-gray-400',
    
    // Flash messages
    FLASH_CONTAINER: 'mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-6',
    FLASH_SUCCESS: 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-xl flex items-center',
    FLASH_ICON: 'w-5 h-5 mr-3 flex-shrink-0',
    
    // Sidebar navigation
    SIDEBAR: 'bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-2 space-y-1',
    SIDEBAR_ITEM_BASE: 'group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 w-full text-left',
    SIDEBAR_ITEM_ACTIVE: 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 shadow-sm',
    SIDEBAR_ITEM_INACTIVE: 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50',
    SIDEBAR_ICON: 'w-5 h-5 mr-3 flex-shrink-0',
    SIDEBAR_CHEVRON: 'w-4 h-4 ml-auto text-blue-600 dark:text-blue-400',
    
    // Content sections
    CONTENT_CARD: 'bg-white dark:bg-gray-800 shadow-sm rounded-2xl overflow-hidden',
    CONTENT_HEADER: 'px-6 py-4 border-b border-gray-200 dark:border-gray-700',
    CONTENT_TITLE: 'text-lg font-semibold text-gray-900 dark:text-white flex items-center',
    CONTENT_BODY: 'p-6',
    CONTENT_GRID: 'grid grid-cols-1 gap-6',
    
    // Setting items
    SETTING_ITEM: 'bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4',
    SETTING_LABEL: 'block text-sm font-semibold text-gray-900 dark:text-white mb-1',
    SETTING_DESC: 'text-sm text-gray-600 dark:text-gray-400',
    
    // Buttons
    BUTTON_BASE: 'inline-flex items-center justify-center font-medium rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200',
    BUTTON_ICON: 'w-4 h-4 mr-2',
    
    // Transitions
    TRANSITION: {
        'enter-active-class': 'transition ease-out duration-300',
        'enter-from-class': 'opacity-0 transform scale-95',
        'enter-to-class': 'opacity-100 transform scale-100',
        'leave-active-class': 'transition ease-in duration-200',
        'leave-from-class': 'opacity-100 transform scale-100',
        'leave-to-class': 'opacity-0 transform scale-95'
    }
}

// Configuration maps for dynamic rendering - cached for performance
const ICONS = Object.freeze({
    overview: InformationCircleIcon,
    section: FolderIcon
})

// Access page props for user permissions
const page = usePage()
const isAdmin = computed(() => 
    page.props.auth.can?.['create course'] || 
    page.props.auth.user?.roles?.some(role => role.name === 'admin')
)

// Reactive state - use shallowRef for better performance
const activeSection = ref('overview')
const data = shallowRef({ createOpen: false })
const form = useForm({ course_id: props.course.id, user_id: props.user.id })
const isInitialized = ref(false)
const message = ref({ type: '', text: '', show: false })

// Check if we have valid data
const hasValidData = computed(() => 
    props.course && props.sections && 
    Object.keys(props.course).length > 0 && 
    props.sections.length > 0
)

// Initialize course data asynchronously with performance monitoring
const initializeCourse = async () => {
    const startTime = performance.now()
    
    await nextTick()
    
    // Handle case where course might be empty or undefined
    if (!props.course || Object.keys(props.course).length === 0) {
        isInitialized.value = true
        return
    }
    
    isInitialized.value = true
    
    // Log performance metrics in development
    if (import.meta.env.DEV) {
        const endTime = performance.now()
        console.log(`Course initialization took ${(endTime - startTime).toFixed(2)}ms`)
    }
}

// Initialize course data immediately
onMounted(async () => {
    await initializeCourse()
})

// Check user permissions
const can = (permissions) => {
    const userPermissions = page.props.auth?.permissions || []
    return permissions.some(permission => userPermissions.includes(permission))
}

// Optimized computed properties
const sectionsCount = computed(() => props.sections?.length || 0)

const enrollmentStatus = computed(() => {
    const states = {
        processing: { color: 'bg-yellow-400', text: 'Processing...' },
        enrolled: { color: 'bg-green-400', text: 'Enrolled' },
        available: { color: 'bg-blue-400', text: 'Available' },
        default: { color: 'bg-gray-400', text: 'Not Available' }
    }
    
    return form.processing ? states.processing :
           props.enrolled ? states.enrolled :
           states.available
})

// Computed properties for better performance
const sortedSections = computed(() => 
    [...(props.sections || [])].sort((a, b) => a.order - b.order)
)

const getLecturesBySection = (sectionId) => 
    props.lectures?.filter(lecture => lecture.section_id === sectionId) || []

// Pre-computed classes - memoized for performance
const sidebarClasses = computed(() => {
    const classes = { overview: '' }
    const active = activeSection.value
    
    // Overview section
    classes.overview = active === 'overview' 
        ? `${UI.SIDEBAR_ITEM_BASE} ${UI.SIDEBAR_ITEM_ACTIVE}`
        : `${UI.SIDEBAR_ITEM_BASE} ${UI.SIDEBAR_ITEM_INACTIVE}`
    
    // Section classes
    sortedSections.value.forEach(section => {
        classes[section.id] = active === section.id 
            ? `${UI.SIDEBAR_ITEM_BASE} ${UI.SIDEBAR_ITEM_ACTIVE}`
            : `${UI.SIDEBAR_ITEM_BASE} ${UI.SIDEBAR_ITEM_INACTIVE}`
    })
    
    return classes
})

const sectionIcons = computed(() => {
    const icons = {}
    sortedSections.value.forEach(section => {
        icons[section.id] = FolderIcon
    })
    return icons
})

// Optimized utility functions - performance enhanced
const setActiveSection = (sectionKey) => {
    if (activeSection.value !== sectionKey) {
        activeSection.value = sectionKey
    }
}

const getButtonClass = (type) => {
    // Consistent button sizing and styling - DRY principle applied
    const baseClasses = `${UI.BUTTON_BASE} px-6 py-2.5 text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed shadow-sm`
    
    const variants = {
        enroll: `${baseClasses} text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500`,
        admin: `${baseClasses} text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-blue-500`
    }
    return variants[type] || variants.admin
}

// Enrollment handler with English comments
const enroll = () => {
    form.post(route('courses.enroll', { courseId: props.course.id }), {
        preserveScroll: true,
        onSuccess: () => {
            showMessage('success', 'Successfully enrolled in the course!')
            form.reset()
        },
        onError: (errors) => {
            const errorMsg = Object.values(errors).flat().join(', ') || 'Enrollment failed'
            showMessage('error', errorMsg)
        }
    })
}

// Dynamic action buttons configuration
const actionButtons = computed(() => {
    const buttons = [
        {
            key: 'enroll',
            component: 'button',
            text: props.enrolled ? 'Enrolled' : 'Enroll Now',
            type: 'enroll',
            icon: props.enrolled ? 'CheckCircleIcon' : 'UserIcon',
            props: {
                disabled: form.processing || props.enrolled,
                type: 'button'
            },
            action: enroll
        }
    ]

    // Add admin buttons conditionally
    if (can(['create course'])) {
        buttons.push(
            {
                key: 'add-section',
                component: 'button',
                text: 'Add Section',
                type: 'admin',
                icon: 'PlusIcon',
                props: { type: 'button' },
                action: () => data.value.createOpen = true
            },
            {
                key: 'add-lecture',
                component: Link,
                text: 'Add Lecture',
                type: 'admin',
                icon: 'PlusIcon',
                props: {
                    href: route('lecture.create', { course: props.course.id })
                },
                action: () => {}
            }
        )
    }

    return buttons
})

// Utility function to check content existence
const hasContent = (content) => content && content.length > 0

// Message handling utility
const showMessage = (type, text) => {
    message.value = { type, text, show: true }
    setTimeout(() => {
        message.value.show = false
    }, 5000)
}
</script>
