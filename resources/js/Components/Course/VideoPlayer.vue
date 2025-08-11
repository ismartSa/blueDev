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

// Reactive state - optimized structure
const state = reactive({
    isPlaying: false,
    currentTime: 0,
    duration: 0,
    volume: 1,
    isMuted: false,
    showControls: true,
    playbackRate: 1,
    controlsTimeout: null,
});

// Computed properties - enhanced with better performance
const progressPercentage = computed(() => 
    state.duration > 0 ? (state.currentTime / state.duration) * 100 : 0
);

const volumePercentage = computed(() => 
    state.isMuted ? 0 : state.volume * 100
);

const currentIcon = computed(() => state.isPlaying ? PauseIcon : PlayIcon);
const volumeIcon = computed(() => 
    !state.isMuted && state.volume > 0 ? SpeakerWaveIcon : SpeakerXMarkIcon
);

const timeDisplay = computed(() => 
    `${formatTime(state.currentTime)} / ${formatTime(state.duration)}`
);

const lessonInfo = computed(() => ({
    title: props.lesson?.title || '',
    position: `Lesson ${props.currentIndex + 1} of ${props.lessons.length}`
}));

// Helper functions
const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

// Utility functions - DRY principle applied
const getClickPosition = (event) => {
    const rect = event.target.getBoundingClientRect();
    return (event.clientX - rect.left) / rect.width;
};

const updateVideoProperty = (property, value) => {
    if (videoPlayer.value) videoPlayer.value[property] = value;
};

// Video control functions - optimized and simplified
const togglePlay = () => {
    if (!videoPlayer.value) return;
    state.isPlaying ? videoPlayer.value.pause() : videoPlayer.value.play();
};

const updateTime = () => {
    if (!videoPlayer.value) return;
    state.currentTime = videoPlayer.value.currentTime;
    state.duration = videoPlayer.value.duration || 0;
};

const seekTo = (event) => {
    if (!videoPlayer.value) return;
    const percent = getClickPosition(event);
    const newTime = percent * state.duration;
    updateVideoProperty('currentTime', newTime);
    state.currentTime = newTime;
};

const changeVolume = (event) => {
    if (!videoPlayer.value) return;
    const volume = Math.max(0, Math.min(1, getClickPosition(event)));
    state.volume = volume;
    updateVideoProperty('volume', volume);
    state.isMuted = volume === 0;
};

const toggleMute = () => {
    if (!videoPlayer.value) return;
    state.isMuted = !state.isMuted;
    updateVideoProperty('muted', state.isMuted);
};

const skipTime = (seconds) => {
    if (!videoPlayer.value) return;
    updateVideoProperty('currentTime', videoPlayer.value.currentTime + seconds);
};

const changePlaybackRate = (rate) => {
    state.playbackRate = rate;
    updateVideoProperty('playbackRate', rate);
};

const showControlsTemporarily = () => {
    state.showControls = true;
    if (state.controlsTimeout) clearTimeout(state.controlsTimeout);
    state.controlsTimeout = setTimeout(() => {
        if (state.isPlaying) state.showControls = false;
    }, 3000);
};

const closePlayer = () => {
    if (videoPlayer.value) videoPlayer.value.pause();
    state.isPlaying = false;
    emit('close');
};

// Watch for changes in props.show - optimized
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

// Constants - extracted for reusability and performance
const SPEED_OPTIONS = [
    { value: 0.5, label: '0.5x' },
    { value: 0.75, label: '0.75x' },
    { value: 1, label: '1x' },
    { value: 1.25, label: '1.25x' },
    { value: 1.5, label: '1.5x' },
    { value: 2, label: '2x' }
];

// Enhanced CSS classes with better organization
const CSS_CLASSES = {
    button: {
        control: 'hover:text-red-500 transition-colors duration-200',
        close: 'hover:text-red-500 transition-colors duration-200'
    },
    bar: {
        progress: 'w-full h-1 bg-gray-600 rounded cursor-pointer hover:h-2 transition-all',
        progressFill: 'h-full bg-red-500 rounded transition-all',
        volume: 'w-20 h-1 bg-gray-600 rounded cursor-pointer hover:h-2 transition-all',
        volumeFill: 'h-full bg-white rounded transition-all'
    },
    container: {
        controls: 'absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4 transition-opacity duration-300',
        info: 'absolute top-4 left-4 bg-black/60 text-white p-3 rounded-lg backdrop-blur-sm'
    },
    select: 'bg-transparent text-white text-sm border-none focus:ring-0 cursor-pointer',
    icon: {
        sm: 'h-5 w-5',
        md: 'h-6 w-6'
    }
};
</script>

<template>
    <Modal :show="show" @close="closePlayer" max-width="6xl">
        <div class="bg-black relative" ref="videoContainer">
            <!-- Video Element -->
            <video
                ref="videoPlayer"
                class="w-full h-auto max-h-[70vh] cursor-pointer"
                @play="state.isPlaying = true"
                @pause="state.isPlaying = false"
                @timeupdate="updateTime"
                @loadedmetadata="updateTime"
                @mousemove="showControlsTemporarily"
                @click="togglePlay"
            >
                Your browser does not support the video tag.
            </video>

            <!-- Video Controls - Enhanced with better UX -->
            <div
                v-show="state.showControls"
                :class="CSS_CLASSES.container.controls"
            >
                <!-- Progress Bar -->
                <div class="mb-4">
                    <div :class="CSS_CLASSES.bar.progress" @click="seekTo">
                        <div 
                            :class="CSS_CLASSES.bar.progressFill" 
                            :style="{ width: progressPercentage + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Control Buttons -->
                <div class="flex items-center justify-between text-white">
                    <div class="flex items-center space-x-4">
                        <!-- Play/Pause -->
                        <button @click="togglePlay" :class="CSS_CLASSES.button.control">
                            <component :is="currentIcon" :class="CSS_CLASSES.icon.md" />
                        </button>

                        <!-- Skip Controls -->
                        <button @click="skipTime(-10)" :class="CSS_CLASSES.button.control" title="Skip backward 10s">
                            <BackwardIcon :class="CSS_CLASSES.icon.sm" />
                        </button>

                        <button @click="skipTime(10)" :class="CSS_CLASSES.button.control" title="Skip forward 10s">
                            <ForwardIcon :class="CSS_CLASSES.icon.sm" />
                        </button>

                        <!-- Volume Control -->
                        <div class="flex items-center space-x-2">
                            <button @click="toggleMute" :class="CSS_CLASSES.button.control" title="Toggle mute">
                                <component :is="volumeIcon" :class="CSS_CLASSES.icon.sm" />
                            </button>
                            <div :class="CSS_CLASSES.bar.volume" @click="changeVolume">
                                <div 
                                    :class="CSS_CLASSES.bar.volumeFill" 
                                    :style="{ width: volumePercentage + '%' }"
                                ></div>
                            </div>
                        </div>

                        <!-- Time Display -->
                        <span class="text-sm font-mono">{{ timeDisplay }}</span>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Playback Speed -->
                        <select
                            v-model="state.playbackRate"
                            @change="changePlaybackRate(state.playbackRate)"
                            :class="CSS_CLASSES.select"
                            title="Playback speed"
                        >
                            <option v-for="option in SPEED_OPTIONS" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>

                        <!-- Close Button -->
                        <button @click="closePlayer" :class="CSS_CLASSES.button.close" title="Close player">
                            <XMarkIcon :class="CSS_CLASSES.icon.md" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Lesson Info Overlay - Enhanced -->
            <div :class="CSS_CLASSES.container.info">
                <h3 class="font-semibold text-lg">{{ lessonInfo.title }}</h3>
                <p class="text-sm text-gray-300 mt-1">{{ lessonInfo.position }}</p>
            </div>
        </div>
    </Modal>
</template>
