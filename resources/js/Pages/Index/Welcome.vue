<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import SwitchDarkMode from "@/Components/SwitchDarkMode.vue";
import { Head, Link } from "@inertiajs/vue3";
import SwitchLangNavbar from "@/Components/SwitchLangNavbar.vue";
import Footer from "@/Pages/Index/Partials/Footer.vue"; // استيراد مكون الفوتر
import { computed } from 'vue';

// تعريف Props
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

// دالة للحصول على صورة الكورس
const getCourseImage = (course) => {
    if (course.thumbnail) {
        return course.thumbnail;
    }

    // استخدام خدمة Picsum Photos للحصول على صور عشوائية جميلة
    // نستخدم معرف الكورس كرقم للصورة لضمان ثبات الصورة لنفس الكورس
    const courseId = course.id || Math.floor(Math.random() * 1000);
    return `https://picsum.photos/seed/${courseId}/800/450`;
};

// تعريف الترجمات
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

// إضافة قسم "لماذا تختار أكاديمية النخبة؟"
const features = [
    {
        icon: 'fas fa-certificate',
        title: 'شهادات معتمدة',
        description: 'احصل على شهادات معتمدة عند إتمامك للكورسات يمكنك إضافتها لسيرتك الذاتية'
    },
    {
        icon: 'fas fa-chalkboard-teacher',
        title: 'مدربون محترفون',
        description: 'تعلم من أفضل المدربين المحترفين في مجالاتهم'
    },
    {
        icon: 'fas fa-users',
        title: 'مجتمع نشط',
        description: 'انضم إلى مجتمع نشط من المتعلمين وشارك في المناقشات'
    }
];

// إضافة قسم "آراء متعلمينا"
const testimonials = [
    {
        name: 'محمد أحمد',
        role: 'مطور ويب',
        quote: 'الكورسات ساعدتني بشكل كبير في تطوير مهاراتي البرمجية'
    },
    {
        name: 'فاطمة علي',
        role: 'مصممة جرافيك',
        quote: 'أكاديمية النخبة هي أفضل مكان لتعلم التصميم الجرافيكي'
    }
];
</script>

<template>
    <Head :title="lang().label.welcome" />
    <div class="min-h-screen bg-slate-100 dark:bg-slate-900">
        <!-- الهيدر -->
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

        <!-- القسم الرئيسي -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Hero Section -->
            <section class="gradient-bg text-white py-24 rounded-lg mb-12">
                <div class="container mx-auto px-4 flex flex-col md:flex-row items-center">
                    <div class="md:w-1/2 mb-10 md:mb-0">
                        <h2 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">
                            طور مهاراتك مع كورسات فيديو احترافية
                        </h2>
                        <p class="text-xl mb-8 text-indigo-100 max-w-md">
                            انضم إلى آلاف المتعلمين الذين طوروا مسيرتهم المهنية من خلال كورساتنا الحصرية التي يقدمها نخبة من الخبراء
                        </p>
                        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                            <button class="bg-white text-indigo-600 px-8 py-4 rounded-lg font-bold hover:bg-indigo-50 transition duration-300 flex items-center justify-center shadow-lg">
                                <i class="fas fa-play-circle mr-2"></i> ابدأ التعلم الآن
                            </button>
                            <button class="border-2 border-white text-white px-8 py-4 rounded-lg font-bold hover:bg-white hover:text-indigo-600 transition duration-300 flex items-center justify-center shadow-lg">
                                <i class="fas fa-info-circle mr-2"></i> تعرف أكثر
                            </button>
                        </div>
                    </div>
                    <div class="md:w-1/2">
                        <div class="relative">
                            <div class="video-container rounded-xl overflow-hidden shadow-2xl transform hover:scale-105 transition duration-300">
                                <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                            <div class="absolute -bottom-4 -left-4 bg-yellow-400 text-gray-900 px-6 py-3 rounded-lg font-bold shadow-lg transform rotate-3">
                                حصري لأعضاء النخبة
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- عرض الكورسات -->
            <section class="py-16">
                <div class="container mx-auto px-4">
                    <div class="flex justify-between items-center mb-12">
                        <h2 class="text-3xl md:text-4xl font-bold">أحدث الكورسات</h2>
                        <div class="hidden md:flex space-x-2 rtl:space-x-reverse">
                            <button class="bg-white dark:bg-slate-700 p-3 rounded-full shadow hover:bg-gray-100 dark:hover:bg-slate-600 transition">
                                <i class="fas fa-chevron-right text-gray-600 dark:text-gray-300"></i>
                            </button>
                            <button class="bg-white dark:bg-slate-700 p-3 rounded-full shadow hover:bg-gray-100 dark:hover:bg-slate-600 transition">
                                <i class="fas fa-chevron-left text-gray-600 dark:text-gray-300"></i>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <div v-for="course in courses" :key="course.id"
                            class="bg-white dark:bg-slate-800 rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 flex flex-col group">
                            <div class="relative overflow-hidden">
                                <img :src="getCourseImage(course)" :alt="course.title" class="w-full h-56 object-cover transition-transform duration-500 group-hover:scale-110" />
                                <div class="absolute top-4 right-4 bg-yellow-400 text-gray-900 px-3 py-1 rounded-lg font-bold text-sm">
                                    {{ course.category || 'كورس جديد' }}
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-6">
                                    <Link :href="route('courses.details', { id: course.id, courseSlug: course.slug })"
                                        class="bg-white text-indigo-600 px-6 py-2 rounded-lg font-bold transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                        {{ lang().label.view_details }}
                                    </Link>
                                </div>
                            </div>
                            <div class="p-6 flex-grow">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex text-yellow-400">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                    </div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-slate-700 px-2 py-1 rounded-lg">{{ course.rating || '4.5' }} ({{ course.reviews_count || '120' }})</span>
                                </div>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-3 line-clamp-2 h-14 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    {{ course.title }}
                                </h2>
                                <p class="text-slate-600 dark:text-slate-400 mb-4 line-clamp-3 h-18">
                                    {{ course.description }}
                                </p>
                                <div class="flex items-center mb-4">
                                    <div class="w-10 h-10 rounded-full overflow-hidden mr-3 bg-indigo-100 border-2 border-indigo-200">
                                        <i class="fas fa-user-tie text-indigo-600 flex items-center justify-center h-full"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white block">{{ course.instructor || 'مدرب محترف' }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ course.instructor_title || 'خبير تطوير' }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-4 justify-between">
                                    <div class="flex items-center">
                                        <i class="far fa-clock mr-1 text-indigo-500"></i>
                                        <span>{{ course.duration || '12 ساعة' }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="far fa-play-circle mr-1 text-indigo-500"></i>
                                        <span>{{ course.lessons_count || '24 درس' }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="far fa-user mr-1 text-indigo-500"></i>
                                        <span>{{ course.students_count || '1,240' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700 p-6 bg-gray-50 dark:bg-slate-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-primary font-bold text-xl">{{ course.price || '299 ر.س' }}</span>
                                        <span class="text-gray-500 line-through text-sm ml-2">{{ course.original_price || '499 ر.س' }}</span>
                                    </div>
                                    <button class="bg-indigo-600 text-white p-2 rounded-full hover:bg-indigo-700 transition">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-12">
                        <Link href="/courses" class="bg-white dark:bg-slate-700 border border-indigo-600 text-indigo-600 dark:text-white px-8 py-3 rounded-lg font-bold hover:bg-indigo-600 hover:text-white transition duration-300 inline-flex items-center shadow-md">
                            عرض جميع الكورسات
                            <i class="fas fa-arrow-left mr-2"></i>
                        </Link>
                    </div>
                </div>
            </section>

            <!-- إضافة قسم "لماذا تختار أكاديمية النخبة؟" -->
            <section class="py-20 bg-white dark:bg-slate-800 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-full opacity-5">
                    <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-600 rounded-full"></div>
                    <div class="absolute top-1/2 right-0 w-80 h-80 bg-yellow-400 rounded-full"></div>
                    <div class="absolute bottom-0 left-1/3 w-64 h-64 bg-green-500 rounded-full"></div>
                </div>
                <div class="container mx-auto px-4 relative z-10">
                    <div class="text-center mb-16">
                        <span class="bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 px-4 py-1 rounded-full text-sm font-medium inline-block mb-4">لماذا تختارنا</span>
                        <h2 class="text-4xl font-bold mb-4">لماذا تختار أكاديمية النخبة؟</h2>
                        <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">نقدم لك تجربة تعليمية فريدة من نوعها مع أفضل المدربين والمحتوى التعليمي المميز</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div v-for="(feature, index) in features" :key="index" class="p-8 rounded-xl border border-gray-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300 bg-white dark:bg-slate-800 hover:border-indigo-200 dark:hover:border-indigo-800 group">
                            <div class="bg-indigo-100 dark:bg-indigo-900 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-8 group-hover:bg-indigo-600 transition-colors duration-300">
                                <i :class="feature.icon + ' text-indigo-600 dark:text-indigo-400 text-4xl group-hover:text-white transition-colors duration-300'"></i>
                            </div>
                            <h3 class="text-2xl font-bold mb-4 text-center group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ feature.title }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-center">{{ feature.description }}</p>
                        </div>
                    </div>
                    <div class="mt-16 text-center">
                        <div class="flex flex-wrap justify-center gap-4">
                            <div class="flex items-center bg-gray-100 dark:bg-slate-700 px-6 py-3 rounded-lg">
                                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-graduation-cap text-indigo-600 dark:text-indigo-400"></i>
                                </div>
                                <div class="text-left">
                                    <span class="block text-3xl font-bold text-indigo-600 dark:text-indigo-400">+200</span>
                                    <span class="text-gray-600 dark:text-gray-400">كورس تعليمي</span>
                                </div>
                            </div>
                            <div class="flex items-center bg-gray-100 dark:bg-slate-700 px-6 py-3 rounded-lg">
                                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-users text-indigo-600 dark:text-indigo-400"></i>
                                </div>
                                <div class="text-left">
                                    <span class="block text-3xl font-bold text-indigo-600 dark:text-indigo-400">+5000</span>
                                    <span class="text-gray-600 dark:text-gray-400">طالب</span>
                                </div>
                            </div>
                            <div class="flex items-center bg-gray-100 dark:bg-slate-700 px-6 py-3 rounded-lg">
                                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-chalkboard-teacher text-indigo-600 dark:text-indigo-400"></i>
                                </div>
                                <div class="text-left">
                                    <span class="block text-3xl font-bold text-indigo-600 dark:text-indigo-400">+50</span>
                                    <span class="text-gray-600 dark:text-gray-400">مدرب محترف</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- إضافة قسم "آراء متعلمينا" -->
            <section class="py-20 bg-slate-100 dark:bg-slate-900 relative">
                <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-white dark:from-slate-800 to-transparent"></div>
                <div class="container mx-auto px-4 relative z-10">
                    <div class="text-center mb-16">
                        <span class="bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 px-4 py-1 rounded-full text-sm font-medium inline-block mb-4">شهادات النجاح</span>
                        <h2 class="text-4xl font-bold mb-4">آراء متعلمينا</h2>
                        <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">استمع إلى تجارب طلابنا الناجحين وكيف ساعدتهم أكاديمية النخبة في تحقيق أهدافهم</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div v-for="(testimonial, index) in testimonials" :key="index" class="bg-white dark:bg-slate-800 p-8 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 relative">
                            <div class="absolute top-6 right-8 text-indigo-200 dark:text-indigo-900 opacity-50">
                                <i class="fas fa-quote-right text-6xl"></i>
                            </div>
                            <div class="relative z-10">
                                <div class="flex text-yellow-400 mb-4">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 mb-6 text-lg leading-relaxed relative z-10">"{{ testimonial.quote }}"</p>
                                <div class="flex items-center">
                                    <div class="bg-indigo-100 dark:bg-indigo-900 w-16 h-16 rounded-full flex items-center justify-center mr-4 border-4 border-white dark:border-slate-700 shadow">
                                        <i class="fas fa-user text-indigo-600 dark:text-indigo-400 text-2xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-xl text-gray-900 dark:text-white">{{ testimonial.name }}</h3>
                                        <p class="text-indigo-600 dark:text-indigo-400">{{ testimonial.role }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 text-center">
                        <button class="bg-white dark:bg-slate-800 border border-indigo-600 text-indigo-600 dark:text-white px-8 py-3 rounded-lg font-bold hover:bg-indigo-600 hover:text-white transition duration-300 shadow-md">
                            عرض المزيد من الآراء
                        </button>
                    </div>
                </div>
            </section>

            <!-- إضافة قسم "جاهز لبدء رحلة التعلم الخاصة بك؟" -->
            <section class="py-20 bg-gradient-to-r from-indigo-600 to-purple-600 text-white relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-full">
                    <div class="absolute top-10 left-10 w-40 h-40 bg-white opacity-10 rounded-full"></div>
                    <div class="absolute bottom-10 right-10 w-60 h-60 bg-white opacity-10 rounded-full"></div>
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-white opacity-5 rounded-full"></div>
                </div>
                <div class="container mx-auto px-4 text-center relative z-10">
                    <h2 class="text-4xl md:text-5xl font-bold mb-8 leading-tight">جاهز لبدء رحلة التعلم الخاصة بك؟</h2>
                    <p class="text-indigo-100 mb-12 text-xl max-w-2xl mx-auto">
                        انضم إلى آلاف المتعلمين وابدأ رحلتك التعليمية اليوم مع أفضل المدربين والمحتوى التعليمي
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <Link href="/courses" class="bg-white text-indigo-600 px-10 py-4 rounded-lg font-bold hover:bg-indigo-50 transition duration-300 text-lg shadow-lg flex items-center justify-center">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            عرض جميع الكورسات
                        </Link>
                        <Link href="/register" class="bg-transparent border-2 border-white text-white px-10 py-4 rounded-lg font-bold hover:bg-white hover:text-indigo-600 transition duration-300 text-lg shadow-lg flex items-center justify-center">
                            <i class="fas fa-user-plus mr-2"></i>
                            سجل الآن مجاناً
                        </Link>
                    </div>
                    <div class="mt-12 flex justify-center space-x-8 rtl:space-x-reverse">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-indigo-200 mr-2"></i>
                            <span>ضمان استرداد المال</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-indigo-200 mr-2"></i>
                            <span>دعم فني على مدار الساعة</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-indigo-200 mr-2"></i>
                            <span>شهادات معتمدة</span>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- الفوتر -->
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
