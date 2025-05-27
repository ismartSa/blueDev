<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextArea from "@/Components/TextArea.vue";
import { reactive, computed, ref, nextTick } from "vue";
import { router } from "@inertiajs/vue3";
import {
    PencilIcon,
    TrashIcon,
    PlusIcon,
    PlayIcon,
    EyeIcon,
    ClockIcon,
    UsersIcon,
    StarIcon,
    BookOpenIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    VideoCameraIcon,
    ChartBarIcon,
    PauseIcon,
    SpeakerWaveIcon,
    SpeakerXMarkIcon,
    ArrowsPointingOutIcon,
    ForwardIcon,
    BackwardIcon,
    Cog6ToothIcon
} from "@heroicons/vue/24/solid";

// Props
const props = defineProps({
    course: { type: Object, required: true },
    lessons: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
    breadcrumbs: { type: Array, default: () => [] },
});

// Refs
const videoPlayer = ref(null);
const videoContainer = ref(null);

// Reactive data
const data = reactive({
    activeTab: 'overview',
    currentLessonIndex: 0,
    isPlaying: false,
    currentTime: 0,
    duration: 0,
    volume: 1,
    isMuted: false,
    isFullscreen: false,
    showControls: true,
    playbackRate: 1,

    // Modal states
    lessonModalOpen: false,
    editCourseModalOpen: false,
    deleteLessonModalOpen: false,
    videoPlayerOpen: false,

    // UI state
    processing: false,
    showSuccess: false,
    successMessage: '',
    lessonToDelete: null,
    currentLesson: null,
    controlsTimeout: null,
});

// Forms
const lessonForm = useForm({
    title: '',
    description: '',
    video_url: '',
    duration: '',
    order: 1,
    is_free: false,
    course_id: null,
});

const editCourseForm = useForm({
    title: props.course.title || '',
    description: props.course.description || '',
    price: props.course.price || 0,
    status: props.course.status || 'draft',
});

// Computed properties
const totalDuration = computed(() => {
    return props.lessons?.data?.reduce((total, lesson) => {
        return total + (lesson.duration || 0);
    }, 0) || 0;
});

const currentLessonData = computed(() => {
    return props.lessons?.data?.[data.currentLessonIndex] || null;
});

const formatDuration = (minutes) => {
    if (!minutes) return '0 minutes';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;

    if (hours > 0) {
        return `${hours} hour${hours > 1 ? 's' : ''} ${mins > 0 ? `and ${mins} minute${mins > 1 ? 's' : ''}` : ''}`;
    }
    return `${mins} minute${mins > 1 ? 's' : ''}`;
};

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

// Video Control Methods
const togglePlay = () => {
    if (!videoPlayer.value) return;

    if (data.isPlaying) {
        videoPlayer.value.pause();
    } else {
        videoPlayer.value.play();
    }
};

const updateTime = () => {
    if (!videoPlayer.value) return;
    data.currentTime = videoPlayer.value.currentTime;
    data.duration = videoPlayer.value.duration || 0;
};

const seekTo = (event) => {
    if (!videoPlayer.value) return;

    const rect = event.target.getBoundingClientRect();
    const percent = (event.clientX - rect.left) / rect.width;
    const time = percent * data.duration;

    videoPlayer.value.currentTime = time;
    data.currentTime = time;
};

const changeVolume = (event) => {
    if (!videoPlayer.value) return;

    const rect = event.target.getBoundingClientRect();
    const volume = (event.clientX - rect.left) / rect.width;

    data.volume = Math.max(0, Math.min(1, volume));
    videoPlayer.value.volume = data.volume;
    data.isMuted = data.volume === 0;
};

const toggleMute = () => {
    if (!videoPlayer.value) return;

    data.isMuted = !data.isMuted;
    videoPlayer.value.muted = data.isMuted;
};

const toggleFullscreen = () => {
    if (!videoContainer.value) return;

    if (!document.fullscreenElement) {
        videoContainer.value.requestFullscreen();
        data.isFullscreen = true;
    } else {
        document.exitFullscreen();
        data.isFullscreen = false;
    }
};

const changePlaybackRate = (rate) => {
    if (!videoPlayer.value) return;
    data.playbackRate = rate;
    videoPlayer.value.playbackRate = rate;
};

const skipTime = (seconds) => {
    if (!videoPlayer.value) return;
    videoPlayer.value.currentTime += seconds;
};

const showControlsTemporarily = () => {
    data.showControls = true;

    if (data.controlsTimeout) {
        clearTimeout(data.controlsTimeout);
    }

    data.controlsTimeout = setTimeout(() => {
        if (data.isPlaying) {
            data.showControls = false;
        }
    }, 3000);
};

const playLesson = (lesson, index) => {
    data.currentLessonIndex = index;
    data.currentLesson = lesson;
    data.videoPlayerOpen = true;

    nextTick(() => {
        if (videoPlayer.value && lesson.video_url) {
            videoPlayer.value.src = lesson.video_url;
            videoPlayer.value.load();
        }
    });
};

const nextLesson = () => {
    if (data.currentLessonIndex < props.lessons.data.length - 1) {
        const nextIndex = data.currentLessonIndex + 1;
        playLesson(props.lessons.data[nextIndex], nextIndex);
    }
};

const previousLesson = () => {
    if (data.currentLessonIndex > 0) {
        const prevIndex = data.currentLessonIndex - 1;
        playLesson(props.lessons.data[prevIndex], prevIndex);
    }
};

// Course Management Methods
const goBack = () => {
    router.get(route('courses.index'));
};

const openLessonModal = (lesson = null) => {
    if (lesson) {
        lessonForm.title = lesson.title;
        lessonForm.description = lesson.description || '';
        lessonForm.video_url = lesson.video_url || '';
        lessonForm.duration = lesson.duration || '';
        lessonForm.order = lesson.order || 1;
        lessonForm.is_free = lesson.is_free || false;
        data.currentLesson = lesson;
    } else {
        lessonForm.reset();
        lessonForm.order = (props.lessons?.data?.length || 0) + 1;
        data.currentLesson = null;
    }

    lessonForm.course_id = props.course.id;
    data.lessonModalOpen = true;
};

const openEditCourseModal = () => {
    editCourseForm.title = props.course.title;
    editCourseForm.description = props.course.description || '';
    editCourseForm.price = props.course.price || 0;
    editCourseForm.status = props.course.status;
    data.editCourseModalOpen = true;
};

const openDeleteLessonModal = (lesson) => {
    data.lessonToDelete = lesson;
    data.deleteLessonModalOpen = true;
};

const closeModals = () => {
    data.lessonModalOpen = false;
    data.editCourseModalOpen = false;
    data.deleteLessonModalOpen = false;
    data.videoPlayerOpen = false;
    data.currentLesson = null;
    data.lessonToDelete = null;
    lessonForm.reset();
    editCourseForm.clearErrors();

    // Reset video player state
    data.isPlaying = false;
    data.currentTime = 0;
    data.duration = 0;
    data.showControls = true;
};

const submitLesson = () => {
    const route_name = data.currentLesson ? 'lectures.update' : 'lectures.store';
    const method = data.currentLesson ? 'put' : 'post';
    const params = data.currentLesson ? [route_name, data.currentLesson.id] : [route_name];

    lessonForm[method](route(...params), {
        onSuccess: () => {
            closeModals();
            showSuccessMessage(data.currentLesson ? 'Lesson updated successfully!' : 'Lesson added successfully!');
        },
        onError: (errors) => {
            console.error('Lesson error:', errors);
        }
    });
};

const submitEditCourse = () => {
    editCourseForm.put(route('courses.updatecourse', props.course.id), {
        onSuccess: () => {
            closeModals();
            showSuccessMessage('Course updated successfully!');
        },
        onError: (errors) => {
            console.error('Course update error:', errors);
        }
    });
};

const deleteLesson = () => {
    router.delete(route('lectures.destroy', data.lessonToDelete.id), {
        onSuccess: () => {
            closeModals();
            showSuccessMessage('Lesson deleted successfully!');
        },
        onError: (errors) => {
            console.error('Delete lesson error:', errors);
        }
    });
};

const showSuccessMessage = (message) => {
    data.successMessage = message;
    data.showSuccess = true;

    setTimeout(() => {
        data.showSuccess = false;
    }, 5000);
};

const getStatusBadgeClass = (status) => {
    const statusClasses = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        inactive: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        draft: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    };
    return statusClasses[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
};
</script>

<template>
    <Head :title="`Course Details: ${course.title}`" />
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Success Message -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 transform translate-y-2"
                enter-to-class="opacity-100 transform translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 transform translate-y-0"
                leave-to-class="opacity-0 transform translate-y-2"
            >
                <div
                    v-if="data.showSuccess"
                    class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4"
                >
                    <div class="flex items-center">
                        <CheckCircleIcon class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" />
                        <p class="text-green-800 dark:text-green-200">{{ data.successMessage }}</p>
                        <button
                            @click="data.showSuccess = false"
                            class="mr-auto text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Header -->
            <div class="bg-white dark:bg-slate-800 shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <SecondaryButton @click="goBack" class="flex items-center gap-2">
                            <ArrowLeftIcon class="w-4 h-4" />
                            Back to Courses
                        </SecondaryButton>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ course.title }}
                            </h1>
                            <div class="flex items-center gap-4 mt-2">
                                <span
                                    class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                                    :class="getStatusBadgeClass(course.status)"
                                >
                                    {{ course.status === 'active' ? 'Active' : course.status === 'draft' ? 'Draft' : 'Inactive' }}
                                </span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    Created: {{ course.created_at }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <SecondaryButton @click="openEditCourseModal" class="flex items-center gap-2">
                            <PencilIcon class="w-4 h-4" />
                            Edit Course
                        </SecondaryButton>
                        <PrimaryButton @click="openLessonModal()" class="flex items-center gap-2">
                            <PlusIcon class="w-4 h-4" />
                            Add Lesson
                        </PrimaryButton>
                    </div>
                </div>

                <!-- Course Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Lessons</h3>
                                <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ lessons?.data?.length || 0 }}</p>
                            </div>
                            <BookOpenIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>

                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-green-600 dark:text-green-400">Enrolled Students</h3>
                                <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ stats.enrolled_students || 0 }}</p>
                            </div>
                            <UsersIcon class="w-8 h-8 text-green-600 dark:text-green-400" />
                        </div>
                    </div>

                    <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-purple-600 dark:text-purple-400">Total Duration</h3>
                                <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ formatDuration(totalDuration) }}</p>
                            </div>
                            <ClockIcon class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>

                    <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg border border-orange-200 dark:border-orange-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-orange-600 dark:text-orange-400">Price</h3>
                                <p class="text-2xl font-bold text-orange-900 dark:text-orange-100">{{ course.price ? `${course.price}` : 'Free' }}</p>
                            </div>
                            <ChartBarIcon class="w-8 h-8 text-orange-600 dark:text-orange-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white dark:bg-slate-800 shadow rounded-lg">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex">
                        <button
                            @click="data.activeTab = 'overview'"
                            :class="[
                                'px-6 py-3 font-medium text-sm border-b-2 transition-colors',
                                data.activeTab === 'overview'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                            ]"
                        >
                            Overview
                        </button>
                        <button
                            @click="data.activeTab = 'lessons'"
                            :class="[
                                'px-6 py-3 font-medium text-sm border-b-2 transition-colors',
                                data.activeTab === 'lessons'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                            ]"
                        >
                            Lessons ({{ lessons?.data?.length || 0 }})
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <!-- Overview Tab -->
                    <div v-show="data.activeTab === 'overview'" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Course Information</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                                            {{ course.description || 'No description available' }}
                                        </p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Price</label>
                                            <p class="mt-1 text-gray-900 dark:text-white font-semibold">
                                                {{ course.price ? `${course.price}` : 'Free' }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                                            <p class="mt-1 text-gray-600 dark:text-gray-400">
                                                {{ course.category?.name || 'Not specified' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Statistics</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 dark:text-gray-400">Total Views</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ stats.total_views || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 dark:text-gray-400">Rating</span>
                                        <div class="flex items-center gap-1">
                                            <StarIcon class="w-4 h-4 text-yellow-400" />
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ course.rating || 'Not available' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 dark:text-gray-400">Last Updated</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ course.updated_at }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lessons Tab -->
                    <div v-show="data.activeTab === 'lessons'" class="space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Course Lessons</h3>
                            <PrimaryButton @click="openLessonModal()" class="flex items-center gap-2">
                                <PlusIcon class="w-4 h-4" />
                                Add New Lesson
                            </PrimaryButton>
                        </div>

                        <div v-if="!lessons?.data?.length" class="text-center py-12">
                            <VideoCameraIcon class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                            <p class="text-gray-500 dark:text-gray-400 mb-4">No lessons in this course yet</p>
                            <PrimaryButton @click="openLessonModal()" class="flex items-center gap-2 mx-auto">
                                <PlusIcon class="w-4 h-4" />
                                Add First Lesson
                            </PrimaryButton>
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="(lesson, index) in lessons.data"
                                :key="lesson.id"
                                class="bg-gray-50 dark:bg-slate-700/50 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                                <span class="text-blue-600 dark:text-blue-400 font-semibold">{{ index + 1 }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-white">{{ lesson.title }}</h4>
                                            <div class="flex items-center gap-4 mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                <span class="flex items-center gap-1">
                                                    <ClockIcon class="w-4 h-4" />
                                                    {{ formatDuration(lesson.duration) }}
                                                </span>
                                                <span class="flex items-center gap-1">
                                                    <EyeIcon class="w-4 h-4" />
                                                    {{ lesson.views || 0 }} views
                                                </span>
                                                <span v-if="lesson.is_free" class="text-green-600 dark:text-green-400 font-medium">
                                                    Free
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <PrimaryButton
                                            v-if="lesson.video_url"
                                            @click="playLesson(lesson, index)"
                                            class="p-2 text-xs"
                                            title="Play Lesson"
                                        >
                                            <PlayIcon class="w-4 h-4" />
                                        </PrimaryButton>
                                        <SecondaryButton
                                            @click="openLessonModal(lesson)"
                                            class="p-2 text-xs"
                                            title="Edit Lesson"
                                        >
                                            <PencilIcon class="w-4 h-4" />
                                        </SecondaryButton>
                                        <DangerButton
                                            @click="openDeleteLessonModal(lesson)"
                                            class="p-2 text-xs"
                                            title="Delete Lesson"
                                        >
                                            <TrashIcon class="w-4 h-4" />
                                        </DangerButton>
                                   
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Player Modal -->
        <Modal :show="data.videoPlayerOpen" @close="closeModals" max-width="6xl">
            <div class="p-6">
                <div class="space-y-4">
                    <!-- Video Player Header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ currentLessonData?.title }}
                            </h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Lesson {{ data.currentLessonIndex + 1 }} of {{ lessons.data.length }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <SecondaryButton
                                @click="previousLesson"
                                :disabled="data.currentLessonIndex === 0"
                                class="flex items-center gap-2"
                            >
                                <BackwardIcon class="w-4 h-4" />
                                Previous
                            </SecondaryButton>
                            <SecondaryButton
                                @click="nextLesson"
                                :disabled="data.currentLessonIndex === lessons.data.length - 1"
                                class="flex items-center gap-2"
                            >
                                Next
                                <ForwardIcon class="w-4 h-4" />
                            </SecondaryButton>
                        </div>
                    </div>

                    <!-- Video Player Container -->
                    <div
                        ref="videoContainer"
                        class="relative bg-black rounded-lg overflow-hidden"
                        @mousemove="showControlsTemporarily"
                        @click="showControlsTemporarily"
                    >
                        <video
                            ref="videoPlayer"
                            class="w-full h-96 object-contain"
                            @loadedmetadata="updateTime"
                            @timeupdate="updateTime"
                            @play="data.isPlaying = true"
                            @pause="data.isPlaying = false"
                            @ended="nextLesson"
                        >
                            <source :src="currentLessonData?.video_url" type="video/mp4">
                            Your browser does not support video playback
                        </video>

                        <!-- Video Controls Overlay -->
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0"
                            enter-to-class="opacity-100"
                            leave-active-class="transition ease-in duration-200"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div
                                v-show="data.showControls"
                                class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20 flex flex-col justify-between p-4"
                            >
                                <!-- Top Controls -->
                                <div class="flex justify-between items-center">
                                    <div class="text-white">
                                        <h3 class="font-medium">{{ currentLessonData?.title }}</h3>
                                        <p class="text-sm opacity-80">{{ formatTime(data.currentTime) }} / {{ formatTime(data.duration) }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <!-- Playback Speed Control -->
                                        <div class="relative group">
                                            <button class="text-white p-2 rounded-md hover:bg-white/20 transition-colors">
                                                <Cog6ToothIcon class="w-5 h-5" />
                                            </button>
                                            <div class="absolute bottom-full right-0 mb-2 hidden group-hover:block">
                                                <div class="bg-black/80 rounded-md p-2 text-white text-sm whitespace-nowrap">
                                                    <div class="space-y-1">
                                                        <button
                                                            v-for="rate in [0.5, 0.75, 1, 1.25, 1.5, 2]"
                                                            :key="rate"
                                                            @click="changePlaybackRate(rate)"
                                                            :class="[
                                                                'block w-full text-left px-2 py-1 rounded hover:bg-white/20',
                                                                data.playbackRate === rate ? 'bg-blue-600' : ''
                                                            ]"
                                                        >
                                                            {{ rate }}x
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Fullscreen Toggle -->
                                        <button
                                            @click="toggleFullscreen"
                                            class="text-white p-2 rounded-md hover:bg-white/20 transition-colors"
                                        >
                                            <ArrowsPointingOutIcon class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Bottom Video Controls -->
                                <div class="space-y-3">
                                    <!-- Video Progress Bar -->
                                    <div class="relative">
                                        <div
                                            @click="seekTo"
                                            class="w-full h-1 bg-white/30 rounded-full cursor-pointer hover:h-2 transition-all"
                                        >
                                            <div
                                                class="h-full bg-blue-500 rounded-full"
                                                :style="{ width: data.duration ? (data.currentTime / data.duration) * 100 + '%' : '0%' }"
                                            ></div>
                                        </div>
                                    </div>

                                    <!-- Video Control Buttons -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <!-- Play/Pause Button -->
                                            <button
                                                @click="togglePlay"
                                                class="text-white p-2 rounded-full hover:bg-white/20 transition-colors"
                                            >
                                                <PlayIcon v-if="!data.isPlaying" class="w-6 h-6" />
                                                <PauseIcon v-else class="w-6 h-6" />
                                            </button>

                                            <!-- Skip Forward/Backward Buttons -->
                                            <button
                                                @click="skipTime(-10)"
                                                class="text-white p-1 rounded hover:bg-white/20 transition-colors"
                                                title="Rewind 10 seconds"
                                            >
                                                <BackwardIcon class="w-5 h-5" />
                                            </button>
                                            <button
                                                @click="skipTime(10)"
                                                class="text-white p-1 rounded hover:bg-white/20 transition-colors"
                                                title="Forward 10 seconds"
                                            >
                                                <ForwardIcon class="w-5 h-5" />
                                            </button>

                                            <!-- Volume Controls -->
                                            <div class="flex items-center gap-2">
                                                <button
                                                    @click="toggleMute"
                                                    class="text-white p-1 rounded hover:bg-white/20 transition-colors"
                                                >
                                                    <SpeakerWaveIcon v-if="!data.isMuted && data.volume > 0" class="w-5 h-5" />
                                                    <SpeakerXMarkIcon v-else class="w-5 h-5" />
                                                </button>
                                                <div
                                                    @click="changeVolume"
                                                    class="w-20 h-1 bg-white/30 rounded-full cursor-pointer hover:h-2 transition-all"
                                                >
                                                    <div
                                                        class="h-full bg-white rounded-full"
                                                        :style="{ width: (data.volume * 100) + '%' }"
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Current Time Display -->
                                        <div class="text-white text-sm">
                                            {{ formatTime(data.currentTime) }} / {{ formatTime(data.duration) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Transition>

                        <!-- Play Button Overlay (when video is paused) -->
                        <div
                            v-if="!data.isPlaying"
                            class="absolute inset-0 flex items-center justify-center"
                        >
                            <button
                                @click="togglePlay"
                                class="bg-black/50 text-white p-4 rounded-full hover:bg-black/70 transition-colors"
                            >
                                <PlayIcon class="w-12 h-12" />
                            </button>
                        </div>
                    </div>

                    <!-- Lesson Description Section -->
                    <div v-if="currentLessonData?.description" class="mt-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Lesson Description</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ currentLessonData.description }}</p>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Add/Edit Lesson Modal -->
        <Modal :show="data.lessonModalOpen" @close="closeModals" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">
                    {{ data.currentLesson ? 'Edit Lesson' : 'Add New Lesson' }}
                </h2>

                <form @submit.prevent="submitLesson" class="space-y-6">
                    <div>
                        <InputLabel for="lesson-title" value="Lesson Title" />
                        <TextInput
                            id="lesson-title"
                            v-model="lessonForm.title"
                            type="text"
                            class="mt-1 block w-full"
                            :class="{ 'border-red-500': lessonForm.errors.title }"
                            placeholder="Enter lesson title"
                            required
                        />
                        <InputError :message="lessonForm.errors.title" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="lesson-description" value="Lesson Description" />
                        <TextArea
                            id="lesson-description"
                            v-model="lessonForm.description"
                            class="mt-1 block w-full"
                            :class="{ 'border-red-500': lessonForm.errors.description }"
                            rows="3"
                            placeholder="Enter lesson description (optional)"
                        />
                        <InputError :message="lessonForm.errors.description" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="lesson-video" value="Video URL" />
                            <TextInput
                                id="lesson-video"
                                v-model="lessonForm.video_url"
                                type="url"
                                class="mt-1 block w-full"
                                :class="{ 'border-red-500': lessonForm.errors.video_url }"
                                placeholder="https://example.com/video.mp4"
                            />
                            <InputError :message="lessonForm.errors.video_url" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="lesson-duration" value="Duration (minutes)" />
                            <TextInput
                                id="lesson-duration"
                                v-model="lessonForm.duration"
                                type="number"
                                class="mt-1 block w-full"
                                :class="{ 'border-red-500': lessonForm.errors.duration }"
                                placeholder="30"
                                min="1"
                            />
                            <InputError :message="lessonForm.errors.duration" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="lesson-order" value="Lesson Order" />
                            <TextInput
                                id="lesson-order"
                                v-model="lessonForm.order"
                                type="number"
                                class="mt-1 block w-full"
                                :class="{ 'border-red-500': lessonForm.errors.order }"
                                min="1"
                            />
                            <InputError :message="lessonForm.errors.order" class="mt-2" />
                        </div>

                        <div class="flex items-center pt-6">
                            <input
                                id="lesson-is-free"
                                type="checkbox"
                                v-model="lessonForm.is_free"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                            />
                            <label for="lesson-is-free" class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Free Lesson
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton type="button" @click="closeModals">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="lessonForm.processing">
                            {{ data.currentLesson ? 'Update Lesson' : 'Add Lesson' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="data.deleteLessonModalOpen" @close="closeModals">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Are you sure you want to delete this lesson?
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    This action cannot be undone.
                </p>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="closeModals">
                        Cancel
                    </SecondaryButton>
                    <DangerButton @click="deleteLesson" :disabled="lessonForm.processing">
                        Yes, Delete
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Edit Course Modal -->
        <Modal :show="data.editCourseModalOpen" @close="closeModals" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                    Edit Course Details
                </h2>
                <form @submit.prevent="submitEditCourse" class="space-y-6">
                    <div>
                        <InputLabel for="course-title" value="Course Title" />
                        <TextInput
                            id="course-title"
                            v-model="editCourseForm.title"
                            type="text"
                            class="mt-1 block w-full"
                            :class="{ 'border-red-500': editCourseForm.errors.title }"
                            placeholder="Enter course title"
                            required
                        />
                        <InputError :message="editCourseForm.errors.title" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="course-description" value="Course Description" />
                        <TextArea
                            id="course-description"
                            v-model="editCourseForm.description"
                            class="mt-1 block w-full"
                            rows="3"
                            :class="{ 'border-red-500': editCourseForm.errors.description }"
                            placeholder="Enter course description"
                        />
                        <InputError :message="editCourseForm.errors.description" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="course-price" value="Price ($)" />
                            <TextInput
                                id="course-price"
                                v-model="editCourseForm.price"
                                type="number"
                                class="mt-1 block w-full"
                                :class="{ 'border-red-500': editCourseForm.errors.price }"
                                placeholder="100"
                                min="0"
                            />
                            <InputError :message="editCourseForm.errors.price" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="course-status" value="Status" />
                            <select
                                id="course-status"
                                v-model="editCourseForm.status"
                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white"
                            >
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <InputError :message="editCourseForm.errors.status" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <SecondaryButton type="button" @click="closeModals">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="editCourseForm.processing">
                            Save Changes
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
