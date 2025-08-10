<script setup>
import { ref, reactive, computed, watch, nextTick } from 'vue';
import Modal from '@/Components/Modal.vue';
import {
    PlayIcon,
    PauseIcon,
    SpeakerWaveIcon,
    SpeakerXMarkIcon,
    ForwardIcon,
    BackwardIcon,
    XMarkIcon
} from '@heroicons/vue/24/solid';

// Props definition
const props = defineProps({
    show: { type: Boolean, default: false },
    lesson: { type: Object, default: null },
    lessons: { type: Array, default: () => [] },
    currentIndex: { type: Number, default: 0 },
});

const emit = defineEmits(['close', 'next', 'previous']);

// Refs
const videoPlayer = ref(null);
const videoContainer = ref(null);

// Reactive state
const data = reactive({
    isPlaying: false,
    currentTime: 0,
    duration: 0,
    volume: 1,
    isMuted: false,
    isFullscreen: false,
    showControls: true,
    playbackRate: 1,
    controlsTimeout: null,
});

// Computed properties
const progressPercentage = computed(() => 
    data.duration > 0 ? (data.currentTime / data.duration) * 100 : 0
);

const volumePercentage = computed(() => 
    data.isMuted ? 0 : data.volume * 100
);

// Helper functions
const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

// Video control functions
const togglePlay = () => {
    if (!videoPlayer.value) return;
    data.isPlaying ? videoPlayer.value.pause() : videoPlayer.value.play();
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

const skipTime = (seconds) => {
    if (!videoPlayer.value) return;
    videoPlayer.value.currentTime += seconds;
};

const changePlaybackRate = (rate) => {
    if (!videoPlayer.value) return;
    data.playbackRate = rate;
    videoPlayer.value.playbackRate = rate;
};

const showControlsTemporarily = () => {
    data.showControls = true;
    if (data.controlsTimeout) clearTimeout(data.controlsTimeout);
    data.controlsTimeout = setTimeout(() => {
        if (data.isPlaying) data.showControls = false;
    }, 3000);
};

const closePlayer = () => {
    if (videoPlayer.value) videoPlayer.value.pause();
    data.isPlaying = false;
    emit('close');
};

// Watch for changes in props.show
watch(() => props.show, (newVal) => {
    if (newVal && props.lesson?.video_url) {
        nextTick(() => {
            if (videoPlayer.value) {
                videoPlayer.value.src = props.lesson.video_url;
                videoPlayer.value.load();
            }
        });
    }
});

// Playback speed options
const speedOptions = [
    { value: 0.5, label: '0.5x' },
    { value: 0.75, label: '0.75x' },
    { value: 1, label: '1x' },
    { value: 1.25, label: '1.25x' },
    { value: 1.5, label: '1.5x' },
    { value: 2, label: '2x' }
];

// Common CSS classes
const classes = {
    controlButton: 'hover:text-red-500 transition-colors',
    progressBar: 'w-full h-1 bg-gray-600 rounded cursor-pointer',
    progressFill: 'h-full bg-red-500 rounded',
    volumeBar: 'w-20 h-1 bg-gray-600 rounded cursor-pointer',
    volumeFill: 'h-full bg-white rounded'
};
</script>

<template>
    <Modal :show="show" @close="closePlayer" max-width="6xl">
        <div class="bg-black relative" ref="videoContainer">
            <!-- Video Element -->
            <video
                ref="videoPlayer"
                class="w-full h-auto max-h-[70vh]"
                @play="data.isPlaying = true"
                @pause="data.isPlaying = false"
                @timeupdate="updateTime"
                @loadedmetadata="updateTime"
                @mousemove="showControlsTemporarily"
                @click="togglePlay"
            >
                Your browser does not support the video tag.
            </video>

            <!-- Video Controls -->
            <div
                v-show="data.showControls"
                class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4"
            >
                <!-- Progress Bar -->
                <div class="mb-4">
                    <div :class="classes.progressBar" @click="seekTo">
                        <div :class="classes.progressFill" :style="{ width: progressPercentage + '%' }"></div>
                    </div>
                </div>

                <!-- Control Buttons -->
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center space-x-4">
                        <!-- Play/Pause -->
                        <button @click="togglePlay" :class="classes.controlButton">
                            <component :is="data.isPlaying ? PauseIcon : PlayIcon" class="h-6 w-6" />
                        </button>

                        <!-- Skip Backward -->
                        <button @click="skipTime(-10)" :class="classes.controlButton">
                            <BackwardIcon class="h-5 w-5" />
                        </button>

                        <!-- Skip Forward -->
                        <button @click="skipTime(10)" :class="classes.controlButton">
                            <ForwardIcon class="h-5 w-5" />
                        </button>

                        <!-- Volume -->
                        <div class="flex items-center space-x-2">
                            <button @click="toggleMute" :class="classes.controlButton">
                                <component 
                                    :is="!data.isMuted && data.volume > 0 ? SpeakerWaveIcon : SpeakerXMarkIcon" 
                                    class="h-5 w-5" 
                                />
                            </button>
                            <div :class="classes.volumeBar" @click="changeVolume">
                                <div :class="classes.volumeFill" :style="{ width: volumePercentage + '%' }"></div>
                            </div>
                        </div>

                        <!-- Time Display -->
                        <span class="text-sm">
                            {{ formatTime(data.currentTime) }} / {{ formatTime(data.duration) }}
                        </span>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Playback Speed -->
                        <select
                            v-model="data.playbackRate"
                            @change="changePlaybackRate(data.playbackRate)"
                            class="bg-transparent text-white text-sm border-none focus:ring-0"
                        >
                            <option v-for="option in speedOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>

                        <!-- Close Button -->
                        <button @click="closePlayer" :class="classes.controlButton">
                            <XMarkIcon class="h-6 w-6" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Lesson Info Overlay -->
            <div class="absolute top-4 left-4 bg-black/60 text-white p-3 rounded-lg">
                <h3 class="font-semibold">{{ lesson?.title }}</h3>
                <p class="text-sm text-gray-300">Lesson {{ currentIndex + 1 }} of {{ lessons.length }}</p>
            </div>
        </div>
    </Modal>
</template>
