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

// استخدام computed property لإدارة المحاضرة الحالية
const currentLecture = computed(() => {
    return {
        id: props.firstLecture.id,
        title: props.firstLecture.title,
        video_url: props.firstLecture.video_url,
        previous_lecture_id: props.firstLecture.previous_lecture_id,
        next_lecture_id: props.firstLecture.next_lecture_id,
        completed: props.firstLecture.completed
    };
});

// استخدام watch لتتبع تغييرات المحاضرة
watch(() => props.firstLecture, (newVal) => {
    if (newVal.id !== currentLecture.value.id) {
        updateLecture(newVal);
    }
});
</script>

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
    axios.post('/lectures/mark-completed', {
        lecture_id: currentLecture.value.id
    })
    .then(() => {
        if (currentLecture.value.next_lecture_id) {
            router.push({
                path: `/courses/${props.course.id}/player/${props.course.slug}/watch/${currentLecture.value.next_lecture_id}`,
                // إضافة تأثير سلس للانتقال
                onComplete: () => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        } else {
            // استخدام إشعار بدلاً من alert
            usePage().props.flash.success = 'تهانينا! لقد أكملت جميع محاضرات هذه الدورة';
        }
    })
    .catch(error => {
        console.error('Error marking lecture as completed:', error);
        // إضافة معالجة الأخطاء
        usePage().props.flash.error = 'حدث خطأ أثناء حفظ تقدمك، يرجى المحاولة مرة أخرى';
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

        <!-- تحسين شريط التقدم -->
        <div class="mb-6">
            <ProgressBar :percentage="completionPercentage" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- قسم الفيديو -->
            <div class="lg:col-span-2">
                <VideoPlayer
                    :src="currentLecture.video_url"
                    :title="currentLecture.title"
                    @ended="onVideoEnded"
                />

                <NavigationButtons
                    :previous="currentLecture.previous_lecture_id"
                    :next="currentLecture.next_lecture_id"
                    :course="course"
                />
            </div>

            <!-- قسم المحتوى -->
            <div class="lg:col-span-1">
                <CourseContentAccordion
                    :sections="sections"
                    :lectures="lectures"
                    :course="course"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<template>
    <div :dir="page.props.locale === 'ar' ? 'rtl' : 'ltr'">
        <!-- محتوى الواجهة -->
    </div>
</template>
