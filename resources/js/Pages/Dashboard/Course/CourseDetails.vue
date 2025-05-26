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
import { reactive, computed } from "vue";
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
    ChartBarIcon
} from "@heroicons/vue/24/solid";

// Props
const props = defineProps({
    course: { type: Object, required: true },
    lessons: { type: Object, required: true },
    stats: { type: Object, default: () => ({}) },
    breadcrumbs: { type: Array, default: () => [] },
});

// Reactive data
const data = reactive({
    activeTab: 'overview',

    // Modal states
    lessonModalOpen: false,
    editCourseModalOpen: false,
    deleteLessonModalOpen: false,

    // UI state
    processing: false,
    showSuccess: false,
    successMessage: '',
    lessonToDelete: null,
    currentLesson: null,
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

const formatDuration = (minutes) => {
    if (!minutes) return '0 دقيقة';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;

    if (hours > 0) {
        return `${hours} ساعة ${mins > 0 ? `و ${mins} دقيقة` : ''}`;
    }
    return `${mins} دقيقة`;
};

// Methods
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
    data.currentLesson = null;
    data.lessonToDelete = null;
    lessonForm.reset();
    editCourseForm.clearErrors();
};

const submitLesson = () => {
    const route_name = data.currentLesson ? 'lectures.update' : 'lectures.store';
    const method = data.currentLesson ? 'put' : 'post';
    const params = data.currentLesson ? [route_name, data.currentLesson.id] : [route_name];

    lessonForm[method](route(...params), {
        onSuccess: () => {
            closeModals();
            showSuccessMessage(data.currentLesson ? 'تم تحديث الدرس بنجاح!' : 'تم إضافة الدرس بنجاح!');
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
            showSuccessMessage('تم تحديث الكورس بنجاح!');
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
            showSuccessMessage('تم حذف الدرس بنجاح!');
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
    <Head :title="`تفاصيل الكورس: ${course.title}`" />
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
                            العودة للكورسات
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
                                    {{ course.status === 'active' ? 'نشط' : course.status === 'draft' ? 'مسودة' : 'غير نشط' }}
                                </span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                    تم الإنشاء: {{ course.created_at }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <SecondaryButton @click="openEditCourseModal" class="flex items-center gap-2">
                            <PencilIcon class="w-4 h-4" />
                            تعديل الكورس
                        </SecondaryButton>
                        <PrimaryButton @click="openLessonModal()" class="flex items-center gap-2">
                            <PlusIcon class="w-4 h-4" />
                            إضافة درس
                        </PrimaryButton>
                    </div>
                </div>

                <!-- Course Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-blue-600 dark:text-blue-400">إجمالي الدروس</h3>
                                <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ lessons?.data?.length || 0 }}</p>
                            </div>
                            <BookOpenIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>

                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-green-600 dark:text-green-400">الطلاب المسجلين</h3>
                                <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ stats.enrolled_students || 0 }}</p>
                            </div>
                            <UsersIcon class="w-8 h-8 text-green-600 dark:text-green-400" />
                        </div>
                    </div>

                    <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-purple-600 dark:text-purple-400">المدة الإجمالية</h3>
                                <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ formatDuration(totalDuration) }}</p>
                            </div>
                            <ClockIcon class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>

                    <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg border border-orange-200 dark:border-orange-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-orange-600 dark:text-orange-400">السعر</h3>
                                <p class="text-2xl font-bold text-orange-900 dark:text-orange-100">{{ course.price ? `${course.price} ريال` : 'مجاني' }}</p>
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
                            نظرة عامة
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
                            الدروس ({{ lessons?.data?.length || 0 }})
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <!-- Overview Tab -->
                    <div v-show="data.activeTab === 'overview'" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">معلومات الكورس</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">الوصف</label>
                                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                                            {{ course.description || 'لا يوجد وصف متاح' }}
                                        </p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">السعر</label>
                                            <p class="mt-1 text-gray-900 dark:text-white font-semibold">
                                                {{ course.price ? `${course.price} ريال` : 'مجاني' }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">الفئة</label>
                                            <p class="mt-1 text-gray-600 dark:text-gray-400">
                                                {{ course.category?.name || 'غير محدد' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">إحصائيات سريعة</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 dark:text-gray-400">إجمالي المشاهدات</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ stats.total_views || 0 }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 dark:text-gray-400">التقييم</span>
                                        <div class="flex items-center gap-1">
                                            <StarIcon class="w-4 h-4 text-yellow-400" />
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ course.rating || 'غير متاح' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 dark:text-gray-400">آخر تحديث</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ course.updated_at }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lessons Tab -->
                    <div v-show="data.activeTab === 'lessons'" class="space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">دروس الكورس</h3>
                            <PrimaryButton @click="openLessonModal()" class="flex items-center gap-2">
                                <PlusIcon class="w-4 h-4" />
                                إضافة درس جديد
                            </PrimaryButton>
                        </div>

                        <div v-if="!lessons?.data?.length" class="text-center py-12">
                            <VideoCameraIcon class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                            <p class="text-gray-500 dark:text-gray-400 mb-4">لا توجد دروس في هذا الكورس بعد</p>
                            <PrimaryButton @click="openLessonModal()" class="flex items-center gap-2 mx-auto">
                                <PlusIcon class="w-4 h-4" />
                                إضافة أول درس
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
                                                    {{ lesson.views || 0 }} مشاهدة
                                                </span>
                                                <span v-if="lesson.is_free" class="text-green-600 dark:text-green-400 font-medium">
                                                    مجاني
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <SecondaryButton
                                            @click="openLessonModal(lesson)"
                                            class="p-2 text-xs"
                                            title="تعديل الدرس"
                                        >
                                            <PencilIcon class="w-4 h-4" />
                                        </SecondaryButton>
                                        <DangerButton
                                            @click="openDeleteLessonModal(lesson)"
                                            class="p-2 text-xs"
                                            title="حذف الدرس"
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

        <!-- Add/Edit Lesson Modal -->
        <Modal :show="data.lessonModalOpen" @close="closeModals" max-width="2xl">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">
                    {{ data.currentLesson ? 'تعديل الدرس' : 'إضافة درس جديد' }}
                </h2>

                <form @submit.prevent="submitLesson" class="space-y-6">
                    <div>
                        <InputLabel for="lesson-title" value="عنوان الدرس" />
                        <TextInput
                            id="lesson-title"
                            v-model="lessonForm.title"
                            type="text"
                            class="mt-1 block w-full"
                            :class="{ 'border-red-500': lessonForm.errors.title }"
                            placeholder="أدخل عنوان الدرس"
                            required
                        />
                        <InputError :message="lessonForm.errors.title" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="lesson-description" value="وصف الدرس" />
                        <TextArea
                            id="lesson-description"
                            v-model="lessonForm.description"
                            class="mt-1 block w-full"
                            :class="{ 'border-red-500': lessonForm.errors.description }"
                            rows="3"
                            placeholder="أدخل وصف الدرس (اختياري)"
                        />
                        <InputError :message="lessonForm.errors.description" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="lesson-video" value="رابط الفيديو" />
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
                            <InputLabel for="lesson-duration" value="المدة (بالدقائق)" />
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
                            <InputLabel for="lesson-order" value="ترتيب الدرس" />
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
                                درس مجاني
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
                            <InputLabel for="course-price" value="Price (SAR)" />
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
