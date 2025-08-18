<script setup>
import { reactive, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import LectureList from "@/Components/Course/LectureList.vue";
import LessonModal from "@/Components/Course/LessonModal.vue";
import VideoPlayer from "@/Components/Course/VideoPlayer.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { PlusIcon } from "@heroicons/vue/24/solid";

const props = defineProps({
    course: { type: Object, required: true },
    lectures: { type: Object, required: true },
});

const emit = defineEmits(['success']);

const data = reactive({
    lessonModalOpen: false,
    videoPlayerOpen: false,
    currentLesson: null,
    currentLessonIndex: 0,
});

const totalDuration = computed(() => {
    return props.lectures?.data?.reduce((total, lecture) => {
        return total + (lecture.duration || 0);
    }, 0) || 0;
});

const openLessonModal = (lesson = null) => {
    if (data.lessonModalOpen) return; // Prevent multiple opens
    data.currentLesson = lesson;
    data.lessonModalOpen = true;
};

const playLesson = (lesson, index) => {
    data.currentLesson = lesson;
    data.currentLessonIndex = index;
    data.videoPlayerOpen = true;
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" :key="course.id">
        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Course Lectures
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Total Duration: {{ Math.floor(totalDuration / 60) }} hours {{ totalDuration % 60 }} minutes
                    </p>
                </div>
                <PrimaryButton @click="openLessonModal()">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Add Lecture
                </PrimaryButton>
            </div>

            <!-- Lectures List -->
            <LectureList
                :lectures="lectures.data"
                @edit="openLessonModal"
                @play="playLesson"
                @success="emit('success', $event)"
            />
        </div>
    </div>

    <!-- Lesson Modal -->
    <LessonModal
        :show="data.lessonModalOpen"
        :lesson="data.currentLesson"
        :course="course"
        @close="data.lessonModalOpen = false"
        @success="(msg) => { data.lessonModalOpen = false; emit('success', msg); }"
    />

    <!-- Video Player -->
    <VideoPlayer
        :show="data.videoPlayerOpen"
        :lesson="data.currentLesson"
        :lectures="lectures.data"
        :current-index="data.currentLessonIndex"
        @close="data.videoPlayerOpen = false"
    />
</template>
