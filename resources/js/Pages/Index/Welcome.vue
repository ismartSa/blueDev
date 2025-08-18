<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import SwitchDarkMode from "@/Components/SwitchDarkMode.vue";
import { Head, Link } from "@inertiajs/vue3";
import SwitchLangNavbar from "@/Components/SwitchLangNavbar.vue";
import Footer from "@/Pages/Index/Partials/Footer.vue";
import { computed, ref, onMounted } from 'vue';
import { ChevronDownIcon, UserIcon, Cog6ToothIcon, ArrowRightOnRectangleIcon, BookOpenIcon } from '@heroicons/vue/24/outline';

// Define Props
defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
    courses: {
        type: [Array, Object],
        default: () => []
    }
});

// Reactive states for new design
const isVisible = ref(false);
const currentSlide = ref(0);
const hoveredCard = ref(null);
const showUserDropdown = ref(false);

// Initialize on mount
onMounted(() => {
    setTimeout(() => isVisible.value = true, 200);
    // Auto-slide testimonials
    setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % testimonials.length;
    }, 4000);
});

// Optimized image loading
const getCourseImage = (course) => {
    if (course.thumbnail) return course.thumbnail;
    return `https://picsum.photos/seed/${course.id || Math.random()}/600/400`;
};

// Centralized content with bilingual support
const content = computed(() => ({
    hero: {
        title: 'Master Skills That Matter',
        titleAr: 'أتقن المهارات المهمة',
        subtitle: 'Unlock your potential with world-class online education',
        subtitleAr: 'اطلق إمكاناتك مع التعليم الإلكتروني عالمي المستوى',
        cta: 'Explore Courses',
        ctaAr: 'استكشف الدورات'
    },
    nav: {
        home: 'Home',
        courses: 'Courses',
        about: 'About',
        contact: 'Contact',
        login: 'Login',
        register: 'Register',
        dashboard: 'Dashboard',
        myCourses: 'My Courses',
        profile: 'Profile',
        logout: 'Logout'
    }
}));

// Simplified features with modern approach
const features = [
    {
        icon: '🎯',
        title: 'Personalized Learning',
        titleAr: 'تعلم مخصص',
        desc: 'AI-powered recommendations tailored to your goals',
        descAr: 'توصيات مدعومة بالذكاء الاصطناعي مصممة لأهدافك'
    },
    {
        icon: '⚡',
        title: 'Lightning Fast',
        titleAr: 'سريع البرق',
        desc: 'Optimized platform for seamless learning experience',
        descAr: 'منصة محسنة لتجربة تعلم سلسة'
    },
    {
        icon: '🏆',
        title: 'Industry Certified',
        titleAr: 'معتمد من الصناعة',
        desc: 'Certificates recognized by top employers worldwide',
        descAr: 'شهادات معترف بها من كبار أصحاب العمل عالمياً'
    },
    {
        icon: '🌍',
        title: 'Global Access',
        titleAr: 'وصول عالمي',
        desc: 'Learn anywhere, anytime with mobile-first design',
        descAr: 'تعلم في أي مكان وأي وقت بتصميم يركز على الهاتف المحمول'
    }
];

// Compact statistics
const metrics = [
    { value: '25K+', label: 'Students', labelAr: 'طالب' },
    { value: '150+', label: 'Courses', labelAr: 'دورة' },
    { value: '95%', label: 'Success Rate', labelAr: 'معدل النجاح' },
    { value: '24/7', label: 'Support', labelAr: 'دعم' }
];

// Streamlined testimonials
const testimonials = [
    {
        name: 'Alex Chen',
        role: 'Software Engineer',
        company: 'Google',
        quote: 'The best investment I made for my career growth.',
        avatar: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=80&h=80&fit=crop&crop=face'
    },
    {
        name: 'Sarah Kim',
        role: 'Product Manager',
        company: 'Microsoft',
        quote: 'Practical skills that I use every day at work.',
        avatar: 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=80&h=80&fit=crop&crop=face'
    },
    {
        name: 'David Wilson',
        role: 'Data Scientist',
        company: 'Netflix',
        quote: 'Transformed my understanding of machine learning.',
        avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=80&h=80&fit=crop&crop=face'
    }
];

// Popular categories
const categories = [
    { name: 'Web Development', icon: '💻', courses: 45 },
    { name: 'Data Science', icon: '📊', courses: 32 },
    { name: 'Design', icon: '🎨', courses: 28 },
    { name: 'Business', icon: '💼', courses: 38 },
    { name: 'Marketing', icon: '📈', courses: 25 },
    { name: 'Mobile Dev', icon: '📱', courses: 22 }
];

</script>

<template>
    <Head :title="content.nav.home" />
    <div class="min-h-screen bg-white dark:bg-gray-900 transition-colors duration-300" :class="{ 'opacity-100': isVisible, 'opacity-0': !isVisible }">
        
        <!-- Minimalist Header -->
        <header class="fixed top-0 w-full bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm border-b border-gray-100 dark:border-gray-800 z-50">
            <div class="max-w-6xl mx-auto px-6">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-3">
                        <ApplicationLogo class="h-8 w-auto" />
                        <span class="text-xl font-bold text-gray-900 dark:text-white">{{ $page.props.app.name }}</span>
                    </div>
                    
                    <nav class="hidden md:flex items-center space-x-8">
                        <a v-for="item in ['home', 'courses', 'about', 'contact']" :key="item" 
                           :href="`#${item}`" class="nav-link">{{ content.nav[item] }}</a>
                    </nav>
                    
                    <div class="flex items-center space-x-4">
                        <SwitchLangNavbar />
                        <SwitchDarkMode />
                        <div v-if="canLogin" class="flex items-center space-x-3">
                            <!-- User Dropdown for Authenticated Users -->
                            <div v-if="$page.props.auth.user" class="relative">
                                <button 
                                    @click="showUserDropdown = !showUserDropdown"
                                    @blur="setTimeout(() => showUserDropdown = false, 150)"
                                    class="flex items-center space-x-2 px-3 py-2 rounded-full bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                                >
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="hidden sm:block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $page.props.auth.user.name }}
                                    </span>
                                    <ChevronDownIcon class="w-4 h-4 text-gray-500 transition-transform" :class="{ 'rotate-180': showUserDropdown }" />
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div v-show="showUserDropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
                                    <Link :href="route('dashboard')" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        <Cog6ToothIcon class="w-4 h-4 mr-3" />
                                        {{ content.nav.dashboard }}
                                    </Link>
                                    <Link :href="route('my-courses.index')" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        <BookOpenIcon class="w-4 h-4 mr-3" />
                                        {{ content.nav.myCourses }}
                                    </Link>
                                    <Link :href="route('profile.edit')" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        <UserIcon class="w-4 h-4 mr-3" />
                                        {{ content.nav.profile }}
                                    </Link>
                                    <hr class="my-1 border-gray-200 dark:border-gray-700" />
                                    <Link :href="route('logout')" method="post" class="flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                        <ArrowRightOnRectangleIcon class="w-4 h-4 mr-3" />
                                        {{ content.nav.logout }}
                                    </Link>
                                </div>
                            </div>
                            
                            <!-- Guest User Links -->
                            <template v-else>
                                <Link :href="route('login')" class="btn-ghost">{{ content.nav.login }}</Link>
                                <Link v-if="canRegister" :href="route('register')" class="btn-primary">
                                    {{ content.nav.register }}
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="pt-32 pb-32 px-6">
            <div class="max-w-5xl mx-auto">
                <div class="text-center space-y-12">
                    <div class="space-y-8">
                        <h1 class="text-6xl md:text-8xl font-light text-gray-900 dark:text-white leading-none tracking-tight">
                            {{ content.hero.title }}
                        </h1>
                        <p class="text-2xl font-light text-gray-500 dark:text-gray-400 max-w-3xl mx-auto leading-relaxed">
                            {{ content.hero.subtitle }}
                        </p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-6 justify-center pt-8">
                        <Link :href="canRegister ? route('register') : route('login')" class="btn-hero">
                            {{ content.hero.cta }}
                        </Link>
                        <button class="btn-outline">Watch Demo</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Metrics Section -->
        <section class="py-24 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-6xl mx-auto px-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-12">
                    <div v-for="metric in metrics" :key="metric.label" class="metric-card">
                        <div class="text-5xl font-light text-gray-900 dark:text-white mb-2">{{ metric.value }}</div>
                        <div class="text-lg font-light text-gray-500 dark:text-gray-400">{{ metric.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-32">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center mb-24">
                    <h2 class="text-5xl font-light text-gray-900 dark:text-white mb-6 tracking-tight">Why Choose Us</h2>
                    <p class="text-xl font-light text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">Everything you need to succeed in your learning journey</p>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-12">
                    <div v-for="(feature, index) in features" :key="index" 
                         class="feature-card" 
                         @mouseenter="hoveredCard = index" 
                         @mouseleave="hoveredCard = null">
                        <div class="text-5xl mb-6">{{ feature.icon }}</div>
                        <h3 class="text-xl font-medium text-gray-900 dark:text-white mb-4 tracking-tight">{{ feature.title }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 font-light leading-relaxed">{{ feature.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="py-32 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-6xl mx-auto px-6">
                <div class="text-center mb-24">
                    <h2 class="text-5xl font-light text-gray-900 dark:text-white mb-6 tracking-tight">Popular Categories</h2>
                    <p class="text-xl font-light text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">Explore our most in-demand courses</p>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8">
                    <div v-for="category in categories" :key="category.name" class="category-card">
                        <div class="text-4xl mb-4">{{ category.icon }}</div>
                        <h3 class="font-medium text-gray-900 dark:text-white mb-2 tracking-tight">{{ category.name }}</h3>
                        <p class="text-sm font-light text-gray-500 dark:text-gray-400">{{ category.courses }} courses</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-32">
            <div class="max-w-5xl mx-auto px-6">
                <div class="text-center mb-24">
                    <h2 class="text-5xl font-light text-gray-900 dark:text-white mb-6 tracking-tight">Student Success</h2>
                    <p class="text-xl font-light text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">Real stories from our community</p>
                </div>
                
                <div class="relative">
                    <div class="testimonial-slider">
                        <div v-for="(testimonial, index) in testimonials" :key="index" 
                             class="testimonial-slide" 
                             :class="{ 'active': currentSlide === index }">
                            <div class="testimonial-content">
                                <blockquote class="text-2xl font-light text-gray-700 dark:text-gray-300 mb-8 leading-relaxed">
                                    "{{ testimonial.quote }}"
                                </blockquote>
                                <div class="flex items-center justify-center space-x-6">
                                    <img :src="testimonial.avatar" :alt="testimonial.name" class="w-16 h-16 rounded-full" />
                                    <div class="text-left">
                                        <div class="font-medium text-gray-900 dark:text-white text-lg">{{ testimonial.name }}</div>
                                        <div class="font-light text-gray-500 dark:text-gray-400">{{ testimonial.role }} at {{ testimonial.company }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Slide Indicators -->
                    <div class="flex justify-center space-x-3 mt-12">
                        <button v-for="(_, index) in testimonials" :key="index" 
                                @click="currentSlide = index" 
                                class="slide-indicator" 
                                :class="{ 'active': currentSlide === index }"></button>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-32 bg-black dark:bg-gray-900">
            <div class="max-w-4xl mx-auto text-center px-6">
                <h2 class="text-5xl font-light text-white mb-8 tracking-tight">Start Your Journey Today</h2>
                <p class="text-xl font-light text-gray-300 mb-12 max-w-2xl mx-auto leading-relaxed">Join thousands of learners who are already transforming their careers</p>
                <Link :href="canRegister ? route('register') : route('login')" class="btn-cta">
                    Get Started Free
                </Link>
            </div>
        </section>

        <Footer />
    </div>
</template>

<style scoped>
/* Apple-inspired Button System */
.btn-primary {
    @apply px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-full font-medium transition-all duration-300 shadow-sm hover:shadow-md;
}

.btn-ghost {
    @apply px-8 py-3 text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 rounded-full font-medium transition-all duration-300;
}

.btn-hero {
    @apply px-12 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-full font-medium text-lg shadow-sm hover:shadow-lg transition-all duration-300;
}

.btn-outline {
    @apply px-12 py-4 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-blue-600 hover:text-blue-600 rounded-full font-medium transition-all duration-300;
}

.btn-cta {
    @apply px-12 py-4 bg-white text-black hover:bg-gray-50 rounded-full font-medium text-lg shadow-sm hover:shadow-lg transition-all duration-300;
}

/* Navigation */
.nav-link {
    @apply text-gray-600 dark:text-gray-300 hover:text-black dark:hover:text-white font-light transition-colors duration-300;
}

/* Apple-inspired Card Components */
.metric-card {
    @apply text-center transition-all duration-300;
}

.feature-card {
    @apply bg-white dark:bg-gray-800 rounded-3xl p-8 border-0 shadow-sm hover:shadow-lg transition-all duration-500 cursor-pointer;
}

.category-card {
    @apply bg-white dark:bg-gray-800 rounded-3xl p-8 text-center border-0 shadow-sm hover:shadow-lg transition-all duration-500 cursor-pointer;
}

/* Apple-inspired Testimonial Slider */
.testimonial-slider {
    @apply relative h-64;
}

.testimonial-slide {
    @apply absolute inset-0 opacity-0 transition-opacity duration-700;
}

.testimonial-slide.active {
    @apply opacity-100;
}

.testimonial-content {
    @apply bg-white dark:bg-gray-800 rounded-3xl p-12 border-0 shadow-sm h-full flex flex-col justify-center;
}

.slide-indicator {
    @apply w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600 transition-all duration-300;
}

.slide-indicator.active {
    @apply bg-black dark:bg-white w-8;
}

/* Apple-inspired Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-in {
    animation: fadeIn 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

/* Responsive Design */
@media (max-width: 768px) {
    .btn-hero, .btn-outline {
        @apply px-8 py-3 text-base;
    }
    
    .feature-card, .category-card {
        @apply p-6;
    }
    
    .testimonial-content {
        @apply p-8;
    }
}

/* Smooth Scrolling */
html {
    scroll-behavior: smooth;
}

/* Apple-inspired Scrollbar */
::-webkit-scrollbar {
    width: 4px;
}

::-webkit-scrollbar-track {
    @apply bg-transparent;
}

::-webkit-scrollbar-thumb {
    @apply bg-gray-300 dark:bg-gray-600 rounded-full;
}

::-webkit-scrollbar-thumb:hover {
    @apply bg-gray-400 dark:bg-gray-500;
}

/* Apple-inspired Transitions */
* {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.25, 0.46, 0.45, 0.94);
    transition-duration: 300ms;
}

/* Typography Enhancements */
h1, h2, h3 {
    letter-spacing: -0.02em;
}

/* Backdrop Blur for Header */
.backdrop-blur-sm {
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
}
</style>
