
<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { HeartIcon, CodeBracketIcon, AcademicCapIcon, MapPinIcon, PhoneIcon, EnvelopeIcon } from '@heroicons/vue/24/outline'

const page = usePage()
const currentYear = new Date().getFullYear()
const appName = computed(() => page.props.app.name)

// Dynamic footer configuration
const footerSections = {
    courses: [
        { name: 'Web Development', href: '/explore?category=web' },
        { name: 'Data Science', href: '/explore?category=data' },
        { name: 'Digital Marketing', href: '/explore?category=marketing' },
        { name: 'Design', href: '/explore?category=design' },
        { name: 'Business', href: '/explore?category=business' }
    ],
    quickLinks: [
        { name: 'About', href: '/about' },
        { name: 'Instructors', href: '/instructors' },
        { name: 'FAQ', href: '/faq' },
        { name: 'Privacy Policy', href: '/privacy' },
        { name: 'Terms of Service', href: '/terms' }
    ],
    contact: [
        { icon: MapPinIcon, text: 'Riyadh, Saudi Arabia' },
        { icon: PhoneIcon, text: '+966 12 345 6789' },
        { icon: EnvelopeIcon, text: 'info@brive.com' }
    ]
}

const socialLinks = [
    { name: 'GitHub', href: 'https://github.com/erikwibowo', icon: CodeBracketIcon },
    { name: 'Website', href: 'https://brive.erikwibowo.com', icon: AcademicCapIcon }
]
</script>

<template>
    <footer class="bg-gray-900 dark:bg-black text-gray-400 py-16">
        <!-- Main Footer Content -->
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <!-- Brand Section -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-blue-600 rounded-full">
                            <AcademicCapIcon class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="text-white text-2xl font-bold tracking-tight">{{ appName }}</h3>
                    </div>
                    <p class="text-gray-400 leading-relaxed max-w-xs">
                        Empowering learners worldwide with quality online education and professional development courses.
                    </p>
                    <div class="flex space-x-4">
                        <a 
                            v-for="social in socialLinks" 
                            :key="social.name"
                            :href="social.href" 
                            target="_blank"
                            rel="noopener noreferrer"
                            class="p-3 rounded-lg bg-gray-800 hover:bg-blue-600 text-gray-400 hover:text-white transition-all duration-300 group"
                            :title="social.name"
                        >
                            <component :is="social.icon" class="h-5 w-5" />
                        </a>
                    </div>
                </div>

                <!-- Courses Section -->
                <div class="space-y-6">
                    <h4 class="text-white text-lg font-semibold uppercase tracking-wider">Popular Courses</h4>
                    <ul class="space-y-3">
                        <li v-for="course in footerSections.courses" :key="course.name">
                            <a 
                                :href="course.href" 
                                class="text-gray-400 hover:text-white hover:translate-x-1 transition-all duration-200 block"
                            >
                                {{ course.name }}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Quick Links Section -->
                <div class="space-y-6">
                    <h4 class="text-white text-lg font-semibold uppercase tracking-wider">Quick Links</h4>
                    <ul class="space-y-3">
                        <li v-for="link in footerSections.quickLinks" :key="link.name">
                            <a 
                                :href="link.href" 
                                class="text-gray-400 hover:text-white hover:translate-x-1 transition-all duration-200 block"
                            >
                                {{ link.name }}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Section -->
                <div class="space-y-6">
                    <h4 class="text-white text-lg font-semibold uppercase tracking-wider">Contact Info</h4>
                    <ul class="space-y-4">
                        <li v-for="contact in footerSections.contact" :key="contact.text" class="flex items-center space-x-3">
                            <component :is="contact.icon" class="h-5 w-5 text-blue-400 flex-shrink-0" />
                            <span class="text-gray-400">{{ contact.text }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-800 mt-16 pt-8">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div class="text-gray-400 text-sm">
                        © {{ currentYear }} {{ appName }}. All rights reserved.
                    </div>
                    <div class="flex items-center space-x-2 text-sm text-gray-400">
                        <span>Made with</span>
                        <HeartIcon class="h-4 w-4 text-red-500 animate-pulse" />
                        <span>by</span>
                        <a 
                            href="https://github.com/erikwibowo" 
                            target="_blank" 
                            rel="noopener noreferrer"
                            class="font-medium text-blue-400 hover:text-blue-300 transition-colors duration-200"
                        >
                            Erik Wibowo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>


