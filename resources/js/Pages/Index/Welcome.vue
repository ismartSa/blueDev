<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import SwitchDarkMode from "@/Components/SwitchDarkMode.vue";
import { Head, Link } from "@inertiajs/vue3";
import SwitchLangNavbar from "@/Components/SwitchLangNavbar.vue";
import Footer from "@/Pages/Index/Partials/Footer.vue"; // Import Footer component
import { computed } from 'vue';

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

// Function to get course image
const getCourseImage = (course) => {
    if (course.thumbnail) {
        return course.thumbnail;
    }

    // Use Picsum Photos service to get beautiful random images
    // We use the course ID as the image number to ensure consistency for the same course
    const courseId = course.id || Math.floor(Math.random() * 1000);
    return `https://picsum.photos/seed/${courseId}/800/450`;
};

// Define translations
const translations = computed(() => ({
    label: {
        welcome: 'Welcome',
        dashboard: 'Dashboard',
        login: 'Login',
        register: 'Register',
        explore_courses: 'Explore Our Courses',
        view_details: 'View Details'
    }
}));

const lang = () => translations.value;

// Add "Why Choose Elite Academy?" section
const features = [
    {
        icon: 'fas fa-certificate',
        title: 'Certified Certificates',
        description: 'Get certified certificates upon completion of courses that you can add to your CV'
    },
    {
        icon: 'fas fa-chalkboard-teacher',
        title: 'Professional Trainers',
        description: 'Learn from the best professional trainers in their fields'
    },
    {
        icon: 'fas fa-users',
        title: 'Active Community',
        description: 'Join an active community of learners and participate in discussions'
    }
];

// Add "Our Learners' Opinions" section
const testimonials = [
    {
        name: 'Mohammed Ahmed',
        role: 'Web Developer',
        quote: 'The courses helped me greatly in developing my programming skills'
    },
    {
        name: 'Fatima Ali',
        role: 'Graphic Designer',
        quote: 'Elite Academy is the best place to learn graphic design'
    }
];
</script>

<template>
    <Head :title="lang().label.welcome" />
    <div class="min-h-screen bg-slate-100 dark:bg-slate-900">
        <!-- Header -->
        <header class="bg-white dark:bg-slate-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <ApplicationLogo class="h-10 w-auto text-primary fill-current" />
                        <p class="text-2xl ml-4 text-primary">{{ $page.props.app.name }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <SwitchLangNavbar />
                        <SwitchDarkMode />
                        <div v-if="canLogin" class="flex items-center space-x-4">
                            <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                                class="btn-primary">{{ lang().label.dashboard }}</Link>
                            <template v-else>
                                <Link :href="route('login')" class="btn-secondary">{{ lang().label.login }}</Link>
                                <Link v-if="canRegister" :href="route('register')"
                                    class="btn-primary">{{ lang().label.register }}</Link>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main section -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Hero Section -->
            <section class="gradient-bg text-white py-24 rounded-lg mb-12">
                <div class="container mx-auto px-4 flex flex-col md:flex-row items-center">
                    <div class="md:w-1/2 mb-10 md:mb-0">
                        <h2 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">
                            Develop your skills with professional video courses
                        </h2>
                        <p class="text-xl mb-8 text-indigo-100 max-w-md">
                            Join thousands of learners who have developed their professional careers through our exclusive courses presented by elite experts
                        </p>
                        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                            <button class="bg-white text-indigo-600 px-8 py-4 rounded-lg font-bold hover:bg-indigo-50 transition duration-300 flex items-center justify-center shadow-lg">
                                <i class="fas fa-play-circle mr-2"></i> Start Learning Now
                            </button>
                            <button class="border-2 border-white text-white px-8 py-4 rounded-lg font-bold hover:bg-white hover:text-indigo-600 transition duration-300 flex items-center justify-center shadow-lg">
                                <i class="fas fa-info-circle mr-2"></i> Learn More
                            </button>
                        </div>
                    </div>
                    <div class="md:w-1/2">
                        <div class="relative">
                            <div class="video-container rounded-xl overflow-hidden shadow-2xl transform hover:scale-105 transition duration-300">
                                <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                            <div class="absolute -bottom-4 -left-4 bg-yellow-400 text-gray-900 px-6 py-3 rounded-lg font-bold shadow-lg transform rotate-3">
                                Exclusive for Elite members
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ... rest of the template remains unchanged ... -->
        </main>

        <!-- Footer -->
        <Footer />
    </div>
</template>

<style scoped>
.btn-primary {
    @apply px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors duration-200;
}

.btn-secondary {
    @apply px-4 py-2 border border-primary text-primary rounded-lg hover:bg-primary/10 transition-colors duration-200;
}

.gradient-bg {
    background: linear-gradient(135deg, #4338ca 0%, #7c3aed 100%);
}

.video-container {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    overflow: hidden;
}

.video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
</style>
