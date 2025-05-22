<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Details | LearnHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .course-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .module-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .progress-ring__circle {
            transition: stroke-dashoffset 0.5s;
            transform: rotate(-90deg);
            transform-origin: 50% 50%;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body class="font-sans bg-gray-50">
    <template>
      <div>
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-graduation-cap text-indigo-600 text-2xl"></i>
                    <span class="text-xl font-bold text-gray-800">LearnHub</span>
                </div>
                <nav class="hidden md:flex space-x-8">
                    <a href="#" class="text-gray-600 hover:text-indigo-600">Home</a>
                    <a href="#" class="text-gray-600 hover:text-indigo-600">Courses</a>
                    <a href="#" class="text-gray-600 hover:text-indigo-600">Instructors</a>
                    <a href="#" class="text-gray-600 hover:text-indigo-600">Pricing</a>
                </nav>
                <div class="flex items-center space-x-4">
                    <button class="px-4 py-2 rounded-md text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
                        Sign In
                    </button>
                </div>
            </div>
        </header>

        <!-- Course Hero Section -->
        <section class="course-hero text-white py-16">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row items-start">
                    <div class="md:w-2/3 mb-8 md:mb-0">
                        <span class="bg-white text-indigo-600 px-3 py-1 rounded-full text-sm font-semibold mb-4 inline-block">Web Development</span>
                        <h1 class="text-4xl md:text-5xl font-bold mb-4">Complete JavaScript Mastery 2024</h1>
                        <p class="text-xl mb-6 opacity-90">Go from zero to expert in modern JavaScript development. Master fundamentals, frameworks, and advanced concepts to build real-world applications.</p>

                        <div class="flex flex-wrap items-center gap-4 mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-400 mr-1"></i>
                                <span class="font-medium">4.9</span>
                                <span class="text-gray-200 ml-1">(2,458 reviews)</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-users text-gray-200 mr-2"></i>
                                <span>12,458 students enrolled</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-clock text-gray-200 mr-2"></i>
                                <span>32 hours</span>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button class="px-6 py-3 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-gray-100 transition">
                                <i class="fas fa-play mr-2"></i> Preview Course
                            </button>
                            <button class="px-6 py-3 bg-indigo-800 text-white rounded-lg font-semibold hover:bg-indigo-900 transition">
                                <i class="fas fa-shopping-cart mr-2"></i> Enroll Now
                            </button>
                        </div>
                    </div>
                    <div class="md:w-1/3 md:pl-8">
                        <div class="bg-white bg-opacity-10 backdrop-blur-md rounded-xl p-6 border border-white border-opacity-20">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 rounded-full bg-indigo-500 flex items-center justify-center">
                                    <i class="fas fa-user text-white text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-semibold">Alex Johnson</h4>
                                    <p class="text-sm opacity-80">Senior JavaScript Developer</p>
                                </div>
                            </div>
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between">
                                    <span class="opacity-80">Course Level</span>
                                    <span class="font-medium">Intermediate</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="opacity-80">Updated</span>
                                    <span class="font-medium">June 2024</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="opacity-80">Language</span>
                                    <span class="font-medium">English</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="opacity-80">Certificate</span>
                                    <span class="font-medium">Yes</span>
                                </div>
                            </div>
                            <div class="border-t border-white border-opacity-20 pt-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-xl font-bold">$89.99</span>
                                    <span class="line-through opacity-70">$129.99</span>
                                    <span class="bg-yellow-500 text-yellow-900 px-2 py-1 rounded text-xs font-bold">30% OFF</span>
                                </div>
                                <p class="text-xs opacity-70 mt-1">Offer ends in 2 days</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Course Content Section -->
        <section class="py-12 bg-white">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row">
                    <!-- Main Content -->
                    <div class="md:w-2/3 md:pr-8">
                        <!-- Tabs Navigation -->
                        <div class="flex border-b border-gray-200 mb-8">
                            <button class="tab-btn py-3 px-6 font-medium text-gray-600 border-b-2 border-transparent hover:text-indigo-600 active" data-tab="overview">
                                Overview
                            </button>
                            <button class="tab-btn py-3 px-6 font-medium text-gray-600 border-b-2 border-transparent hover:text-indigo-600" data-tab="curriculum">
                                Curriculum
                            </button>
                            <button class="tab-btn py-3 px-6 font-medium text-gray-600 border-b-2 border-transparent hover:text-indigo-600" data-tab="instructor">
                                Instructor
                            </button>
                            <button class="tab-btn py-3 px-6 font-medium text-gray-600 border-b-2 border-transparent hover:text-indigo-600" data-tab="reviews">
                                Reviews
                            </button>
                        </div>

                        <!-- Tab Contents -->
                        <div class="tab-content active" id="overview">
                            <h2 class="text-2xl font-bold mb-4">Course Description</h2>
                            <p class="text-gray-700 mb-6">This comprehensive JavaScript course takes you from the absolute basics to advanced concepts. You'll learn modern JavaScript (ES6+), functional programming, asynchronous JavaScript, and how to build real-world applications with popular frameworks.</p>

                            <h3 class="text-xl font-semibold mb-3 mt-6">What You'll Learn</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                                <div class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <span>Master JavaScript fundamentals: variables, functions, objects, arrays</span>
                                </div>
                                <div class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <span>Understand ES6+ features like arrow functions, destructuring, promises</span>
                                </div>
                                <div class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <span>Work with asynchronous JavaScript using async/await</span>
                                </div>
                                <div class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <span>Build projects with React, Vue, and Node.js</span>
                                </div>
                                <div class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <span>Debug and optimize JavaScript applications</span>
                                </div>
                                <div class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                    <span>Test JavaScript code with Jest and other testing frameworks</span>
                                </div>
                            </div>

                            <h3 class="text-xl font-semibold mb-3">Who This Course Is For</h3>
                            <ul class="list-disc pl-5 text-gray-700 mb-8 space-y-2">
                                <li>Beginners who want to learn JavaScript from scratch</li>
                                <li>Frontend developers looking to deepen their JavaScript knowledge</li>
                                <li>Backend developers who want to add JavaScript to their skillset</li>
                                <li>Anyone preparing for JavaScript developer interviews</li>
                            </ul>

                            <h3 class="text-xl font-semibold mb-3">Requirements</h3>
                            <ul class="list-disc pl-5 text-gray-700 mb-8 space-y-2">
                                <li>Basic understanding of HTML and CSS</li>
                                <li>A computer with internet access</li>
                                <li>No prior JavaScript experience required</li>
                            </ul>
                        </div>

                        <div class="tab-content" id="curriculum">
                            <div class="flex justify-between items-center mb-6">
                                <h2 class="text-2xl font-bold">Course Curriculum</h2>
                                <div class="text-sm text-gray-600">
                                    <span>32 hours</span>
                                    <span class="mx-2">•</span>
                                    <span>125 lectures</span>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <!-- Module 1 -->
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="bg-gray-50 px-5 py-4 flex justify-between items-center cursor-pointer module-header">
                                        <div>
                                            <h3 class="font-semibold">Module 1: JavaScript Fundamentals</h3>
                                            <p class="text-sm text-gray-600 mt-1">8 lectures • 2 hours 15 min</p>
                                        </div>
                                        <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300"></i>
                                    </div>
                                    <div class="module-content hidden">
                                        <div class="border-t border-gray-200">
                                            <div class="px-5 py-3 flex items-center hover:bg-gray-50 module-item">
                                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-4">
                                                    <i class="fas fa-play text-indigo-600 text-xs"></i>
                                                </div>
                                                <div class="flex-grow">
                                                    <h4 class="font-medium">Introduction to JavaScript</h4>
                                                    <p class="text-sm text-gray-500">15 min</p>
                                                </div>
                                                <div class="flex items-center">
                                                    <i class="fas fa-lock text-gray-400 mr-3"></i>
                                                    <i class="far fa-eye text-gray-400"></i>
                                                </div>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-lock text-gray-400 mr-3"></i>
                                                <i class="far fa-eye text-gray-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Module 2 -->
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="bg-gray-50 px-5 py-4 flex justify-between items-center cursor-pointer module-header">
                                        <div>
                                            <h3 class="font-semibold">Module 2: DOM Manipulation</h3>
                                            <p class="text-sm text-gray-600 mt-1">10 lectures • 3 hours 40 min</p>
                                        </div>
                                        <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300"></i>
                                    </div>
                                    <div class="module-content hidden">
                                        <div class="border-t border-gray-200">
                                            <div class="px-5 py-3 flex items-center hover:bg-gray-50 module-item">
                                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-4">
                                                    <i class="fas fa-play text-indigo-600 text-xs"></i>
                                                </div>
                                                <div class="flex-grow">
                                                    <h4 class="font-medium">Selecting DOM Elements</h4>
                                                    <p class="text-sm text-gray-500">25 min</p>
                                                </div>
                                                <div class="flex items-center">
                                                    <i class="fas fa-lock text-gray-400 mr-3"></i>
                                                    <i class="far fa-eye text-gray-400"></i>
                                                </div>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-lock text-gray-400 mr-3"></i>
                                                <i class="far fa-eye text-gray-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Module 3 -->
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="bg-gray-50 px-5 py-4 flex justify-between items-center cursor-pointer module-header">
                                        <div>
                                            <h3 class="font-semibold">Module 3: Modern JavaScript (ES6+)</h3>
                                            <p class="text-sm text-gray-600 mt-1">15 lectures • 4 hours 10 min</p>
                                        </div>
                                        <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300"></i>
                                    </div>
                                    <div class="module-content hidden">
                                        <div class="border-t border-gray-200">
                                            <div class="px-5 py-3 flex items-center hover:bg-gray-50 module-item">
                                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-4">
                                                    <i class="fas fa-play text-indigo-600 text-xs"></i>
                                                </div>
                                                <div class="flex-grow">
                                                    <h4 class="font-medium">Arrow Functions</h4>
                                                    <p class="text-sm text-gray-500">20 min</p>
                                                </div>
                                                <div class="flex items-center">
                                                    <i class="fas fa-lock text-gray-400 mr-3"></i>
                                                    <i class="far fa-eye text-gray-400"></i>
                                                </div>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-lock text-gray-400 mr-3"></i>
                                                <i class="far fa-eye text-gray-400"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content" id="instructor">
                        <h2 class="text-2xl font-bold mb-6">About the Instructor</h2>
                        <div class="flex flex-col md:flex-row items-start gap-6">
                            <div class="w-full md:w-1/4">
                                <div class="bg-gray-100 rounded-lg p-4">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Instructor" class="w-full rounded-lg mb-4">
                                    <div class="text-center">
                                        <h3 class="font-bold text-lg">Alex Johnson</h3>
                                        <p class="text-gray-600 mb-3">Senior JavaScript Developer</p>
                                        <div class="flex justify-center space-x-3">
                                            <a href="#" class="text-blue-500 hover:text-blue-700">
                                                <i class="fab fa-twitter"></i>
                                            </a>
                                            <a href="#" class="text-blue-700 hover:text-blue-900">
                                                <i class="fab fa-linkedin"></i>
                                            </a>
                                            <a href="#" class="text-gray-800 hover:text-gray-600">
                                                <i class="fab fa-github"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="w-full md:w-3/4">
                                <h3 class="text-xl font-semibold mb-3">Biography</h3>
                                <p class="text-gray-700 mb-4">Alex Johnson is a senior JavaScript developer with over 10 years of experience building web applications. He has worked with companies like Google, Airbnb, and Netflix, helping them build scalable frontend architectures.</p>

                                <p class="text-gray-700 mb-6">Alex specializes in modern JavaScript frameworks and has contributed to open-source projects like React and Vue.js. He's passionate about teaching and has helped over 50,000 students learn JavaScript through his online courses.</p>

                                <h3 class="text-xl font-semibold mb-3">Experience</h3>
                                <div class="space-y-4 mb-6">
                                    <div class="border-l-4 border-indigo-500 pl-4 py-1">
                                        <h4 class="font-medium">Senior Frontend Developer</h4>
                                        <p class="text-gray-600">TechCorp • 2018 - Present</p>
                                    </div>
                                    <div class="border-l-4 border-indigo-500 pl-4 py-1">
                                        <h4 class="font-medium">JavaScript Engineer</h4>
                                        <p class="text-gray-600">WebSolutions • 2014 - 2018</p>
                                    </div>
                                </div>

                                <h3 class="text-xl font-semibold mb-3">Other Courses</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                        <h4 class="font-medium mb-1">React Masterclass</h4>
                                        <p class="text-sm text-gray-600 mb-2">Advanced React patterns and best practices</p>
                                        <div class="flex items-center text-sm text-gray-500">
                                            <i class="fas fa-star text-yellow-400 mr-1"></i>
                                            <span>4.8</span>
                                            <span class="mx-2">•</span>
                                            <span>8,245 students</span>
                                        </div>
                                    </div>
                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                        <h4 class="font-medium mb-1">Node.js for Beginners</h4>
                                        <p class="text-sm text-gray-600 mb-2">Build backend applications with Node.js</p>
                                        <div class="flex items-center text-sm text-gray-500">
                                            <i class="fas fa-star text-yellow-400 mr-1"></i>
                                            <span>4.7</span>
                                            <span class="mx-2">•</span>
                                            <span>5,892 students</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content" id="reviews">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold">Student Reviews</h2>
                            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                <i class="fas fa-plus mr-2"></i> Write a Review
                            </button>
                        </div>

                        <div class="flex flex-col md:flex-row items-start gap-8 mb-8">
                            <div class="w-full md:w-1/3 bg-gray-50 p-6 rounded-lg">
                                <div class="text-center mb-4">
                                    <div class="text-5xl font-bold text-indigo-600 mb-2">4.9</div>
                                    <div class="flex justify-center">
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                        <i class="fas fa-star text-yellow-400"></i>
                                    </div>
                                    <p class="text-gray-600 mt-2">Based on 2,458 reviews</p>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center">
                                        <span class="w-16 text-sm text-gray-600">5 stars</span>
                                        <div class="flex-grow mx-2">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-yellow-400 h-2 rounded-full" style="width: 85%"></div>
                                            </div>
                                        </div>
                                        <span class="w-10 text-sm text-gray-600">85%</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-16 text-sm text-gray-600">4 stars</span>
                                        <div class="flex-grow mx-2">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-yellow-400 h-2 rounded-full" style="width: 12%"></div>
                                            </div>
                                        </div>
                                        <span class="w-10 text-sm text-gray-600">12%</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-16 text-sm text-gray-600">3 stars</span>
                                        <div class="flex-grow mx-2">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-yellow-400 h-2 rounded-full" style="width: 2%"></div>
                                            </div>
                                        </div>
                                        <span class="w-10 text-sm text-gray-600">2%</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-16 text-sm text-gray-600">2 stars</span>
                                        <div class="flex-grow mx-2">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-yellow-400 h-2 rounded-full" style="width: 1%"></div>
                                            </div>
                                        </div>
                                        <span class="w-10 text-sm text-gray-600">1%</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="w-16 text-sm text-gray-600">1 star</span>
                                        <div class="flex-grow mx-2">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-yellow-400 h-2 rounded-full" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <span class="w-10 text-sm text-gray-600">0%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="w-full md:w-2/3">
                                <div class="space-y-6">
                                    <!-- Review 1 -->
                                    <div class="border-b border-gray-200 pb-6">
                                        <div class="flex items-start mb-3">
                                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="User" class="w-10 h-10 rounded-full mr-4">
                                            <div>
                                                <h4 class="font-medium">Sarah Miller</h4>
                                                <div class="flex items-center text-sm text-gray-500">
                                                    <div class="flex mr-2">
                                                        <i class="fas fa-star text-yellow-400"></i>
                                                        <i class="fas fa-star text-yellow-400"></i>
                                                        <i class="fas fa-star text-yellow-400"></i>
                                                        <i class="fas fa-star text-yellow-400"></i>
                                                        <i class="fas fa-star text-yellow-400"></i>
                                                    </div>
                                                    <span>2 weeks ago</span>
                                                </div>
                                            </div>
                                        </div>
                                        <h5 class="font-medium mb-2">Best JavaScript course I've taken!</h5>
                                        <p class="text-gray-700">This course exceeded all my expectations. The instructor explains complex concepts in a way that's easy to understand. The projects are practical and helped me build a portfolio that landed me my first developer job!</p>
                                    </div>

                                    <!-- Review 2 -->
                                    <div class="border-b border-gray-200 pb-6">
                                        <div class="flex items-start mb-3">
                                            <img src="https://randomuser.me/api/portraits/men/22.jpg" alt="User" class="w-10 h-10 rounded-full mr-4">
                                            <div>
                                                <h4 class="font-medium">Michael Chen</h4>
                                                <div class="flex items-center text-sm text-gray-500">
                                                    <div class="flex mr-2">
                                                        <i class="fas fa-star text-yellow-400"></i>
                                                        <i class="fas fa-star text-yellow-400"></i>
                                                        <i class="fas fa-star text-yellow-400"></i>
                                                        <i class="fas fa-star text-yellow-400"></i>
                                                        <i class="fas fa-star text-yellow-400"></i>
                                                    </div>
                                                    <span>1 month ago</span>
                                                </div>
                                            </div>
                                        </div>
                                        <h5 class="font-medium mb-2">Comprehensive and well-structured</h5>
                                        <p class="text-gray-700">As someone with some programming experience but new to JavaScript, this course was perfect. The pace was just right, and the exercises reinforced the concepts well. The React section was particularly valuable.</p>
                                    </div>

                                    <!-- Review 3 -->
                                    <div class="pb-6">
                                        <div class="flex items-start mb-3">
                                            <img src="https://randomuser.me/api/portraits/women/63.jpg" alt="User" class="w-10 h-10 rounded-full mr-4">
                                            <div>
                                                <h4 class="font-medium">Emily Rodriguez</h4>
                                                <div class="flex items-center text-sm text-gray-500">
                                                    <div class="flex mr-2">
                                                        <i class="fas fa-star text-yellow-400"></i>
                                                        <i class="fas fa-star text-yellow-400"></i>
                                                        <i class="fas fa-star text-yellow-400"></i>
                                                        <i class="fas fa-star text-yellow-400"></i>
                                                        <i class="fas fa-star-half-alt text-yellow-400"></i>
                                                    </div>
                                                    <span>2 months ago</span>
                                                </div>
                                            </div>
                                        </div>
                                        <h5 class="font-medium mb-2">Great content, could use more exercises</h5>
                                        <p class="text-gray-700">The course content is excellent and very thorough. My only suggestion would be to include more coding exercises to practice each concept. The instructor's explanations are clear and the real-world examples are helpful.</p>
                                    </div>
                                </div>

                                <button class="w-full py-3 border border-gray-300 rounded-md text-gray-700 font-medium hover:bg-gray-50">
                                    Load More Reviews
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="md:w-1/3 mt-8 md:mt-0">
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 sticky top-4">
                        <h3 class="text-xl font-bold mb-4">Course Includes</h3>
                        <ul class="space-y-3 mb-6">
                            <li class="flex items-center">
                                <i class="fas fa-video text-indigo-500 mr-3"></i>
                                <span>32 hours on-demand video</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-file-alt text-indigo-500 mr-3"></i>
                                <span>45 downloadable resources</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-mobile-alt text-indigo-500 mr-3"></i>
                                <span>Access on mobile and TV</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-infinity text-indigo-500 mr-3"></i>
                                <span>Full lifetime access</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-trophy text-indigo-500 mr-3"></i>
                                <span>Certificate of completion</span>
                            </li>
                        </ul>

                        <div class="border-t border-gray-200 pt-4 mb-6">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600">Price:</span>
                                <div class="text-right">
                                    <span class="text-xl font-bold text-indigo-600">$89.99</span>
                                    <span class="line-through text-gray-500 ml-2">$129.99</span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-gray-600">Discount:</span>
                                <span class="font-medium text-green-600">30% OFF</span>
                            </div>
                            <button class="w-full py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 mb-3">
                                <i class="fas fa-shopping-cart mr-2"></i> Enroll Now
                            </button>
                            <button class="w-full py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50">
                                <i class="far fa-heart mr-2"></i> Add to Wishlist
                            </button>
                        </div>

                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="font-medium mb-3">Share this course</h4>
                            <div class="flex space-x-3">
                                <a href="#" class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center hover:bg-blue-600">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="w-10 h-10 rounded-full bg-blue-400 text-white flex items-center justify-center hover:bg-blue-500">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="w-10 h-10 rounded-full bg-red-500 text-white flex items-center justify-center hover:bg-red-600">
                                    <i class="fab fa-pinterest-p"></i>
                                </a>
                                <a href="#" class="w-10 h-10 rounded-full bg-gray-700 text-white flex items-center justify-center hover:bg-gray-800">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 mt-6">
                        <h3 class="text-xl font-bold mb-4">Similar Courses</h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="w-20 h-20 rounded-lg bg-indigo-100 flex-shrink-0 overflow-hidden">
                                    <img src="https://via.placeholder.com/80x80" alt="Course" class="w-full h-full object-cover">
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">React - The Complete Guide</h4>
                                    <div class="flex items-center text-sm text-gray-500 mt-1">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span>4.8</span>
                                        <span class="mx-1">•</span>
                                        <span>$94.99</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-20 h-20 rounded-lg bg-indigo-100 flex-shrink-0 overflow-hidden">
                                    <img src="https://via.placeholder.com/80x80" alt="Course" class="w-full h-full object-cover">
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">Node.js Developer Course</h4>
                                    <div class="flex items-center text-sm text-gray-500 mt-1">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span>4.7</span>
                                        <span class="mx-1">•</span>
                                        <span>$79.99</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-20 h-20 rounded-lg bg-indigo-100 flex-shrink-0 overflow-hidden">
                                    <img src="https://via.placeholder.com/80x80" alt="Course" class="w-full h-full object-cover">
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">Modern HTML & CSS</h4>
                                    <div class="flex items-center text-sm text-gray-500 mt-1">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span>4.6</span>
                                        <span class="mx-1">•</span>
                                        <span>$59.99</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Frequently Asked Questions</h2>

            <div class="max-w-3xl mx-auto">
                <div class="space-y-4">
                    <!-- FAQ Item 1 -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-white px-5 py-4 flex justify-between items-center cursor-pointer faq-header">
                            <h3 class="font-medium">When does the course start and finish?</h3>
                            <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300"></i>
                        </div>
                        <div class="faq-content hidden">
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-200">
                                <p class="text-gray-700">The course starts now and never ends! It is a completely self-paced online course - you decide when you start and when you finish. You'll have full access to all course materials immediately after enrollment.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-white px-5 py-4 flex justify-between items-center cursor-pointer faq-header">
                            <h3 class="font-medium">How long do I have access to the course?</h3>
                            <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300"></i>
                        </div>
                        <div class="faq-content hidden">
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-200">
                                <p class="text-gray-700">How does lifetime access sound? After enrolling, you have unlimited access to this course for as long as you like - across any and all devices you own. This includes all future updates and additional content added to the course.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-white px-5 py-4 flex justify-between items-center cursor-pointer faq-header">
                            <h3 class="font-medium">What if I am unhappy with the course?</h3>
                            <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300"></i>
                        </div>
                        <div class="faq-content hidden">
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-200">
                                <p class="text-gray-700">We would never want you to be unhappy! If you are unsatisfied with your purchase, contact us in the first 30 days and we will give you a full refund, no questions asked.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-white px-5 py-4 flex justify-between items-center cursor-pointer faq-header">
                            <h3 class="font-medium">Do I need any prior programming experience?</h3>
                            <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300"></i>
                        </div>
                        <div class="faq-content hidden">
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-200">
                                <p class="text-gray-700">This course is designed for complete beginners with no prior programming experience. We start from the absolute basics and gradually build up to more advanced concepts. The only requirement is basic computer literacy.</p>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Item 5 -->
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-white px-5 py-4 flex justify-between items-center cursor-pointer faq-header">
                            <h3 class="font-medium">Will I receive a certificate after completing the course?</h3>
                            <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300"></i>
                        </div>
                        <div class="faq-content hidden">
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-200">
                                <p class="text-gray-700">Yes! Upon completing all course lectures and exercises, you'll receive a certificate of completion that you can download and share with your professional network or include in your resume/CV.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-8">
                    <p class="text-gray-600 mb-4">Still have questions?</p>
                    <button class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700">
                        <i class="fas fa-envelope mr-2"></i> Contact Support
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fas fa-graduation-cap text-indigo-400 text-2xl"></i>
                        <span class="text-xl font-bold">LearnHub</span>
                    </div>
                    <p class="text-gray-400 mb-4">Learn from the best instructors and transform your career with our online courses.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Careers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Blog</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Press</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Affiliates</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Support</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Refund Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Subscribe to our newsletter</h4>
                    <p class="text-gray-400 mb-4">Get the latest updates on new courses and special offers.</p>
                    <div class="flex">
                        <input type="email" placeholder="Your email" class="px-4 py-2 rounded-l-md w-full text-gray-900">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-r-md hover:bg-indigo-700">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-500">
                <p>© 2024 LearnHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Tab functionality
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                // Add active class to clicked button and corresponding content
                button.classList.add('active');
                const tabId = button.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Module accordion functionality
        const moduleHeaders = document.querySelectorAll('.module-header');
        moduleHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const content = header.nextElementSibling;
                const icon = header.querySelector('i');

                content.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            });
        });

        // FAQ accordion functionality
        const faqHeaders = document.querySelectorAll('.faq-header');
        faqHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const content = header.nextElementSibling;
                const icon = header.querySelector('i');

                content.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            });
        });
    </script>
</body>
</html>

<template>
  <div>
    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="container mx-auto px-4 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-2">
          <i class="fas fa-graduation-cap text-indigo-600 text-2xl"></i>
          <span class="text-xl font-bold text-gray-800">LearnHub</span>
        </div>
        <nav class="hidden md:flex space-x-8">
          <a href="#" class="text-gray-600 hover:text-indigo-600">Home</a>
          <a href="#" class="text-gray-600 hover:text-indigo-600">Courses</a>
          <a href="#" class="text-gray-600 hover:text-indigo-600">Instructors</a>
          <a href="#" class="text-gray-600 hover:text-indigo-600">Pricing</a>
        </nav>
        <div class="flex items-center space-x-4">
          <button class="px-4 py-2 rounded-md text-gray-600 hover:bg-gray-100">
            <i class="fas fa-search"></i>
          </button>
          <button class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">
            Sign In
          </button>
        </div>
      </div>
    </header>

    <!-- Course Hero Section -->
    <section class="course-hero text-white py-16">
      <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-start">
          <div class="md:w-2/3 mb-8 md:mb-0">
            <span class="bg-white text-indigo-600 px-3 py-1 rounded-full text-sm font-semibold mb-4 inline-block">Web Development</span>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Complete JavaScript Mastery 2024</h1>
            <p class="text-xl mb-6 opacity-90">Go from zero to expert in modern JavaScript development. Master fundamentals, frameworks, and advanced concepts to build real-world applications.</p>

            <div class="flex flex-wrap items-center gap-4 mb-6">
              <div class="flex items-center">
                <i class="fas fa-star text-yellow-400 mr-1"></i>
                <span class="font-medium">4.9</span>
                <span class="text-gray-200 ml-1">(2,458 reviews)</span>
              </div>
              <div class="flex items-center">
                <i class="fas fa-users text-gray-200 mr-2"></i>
                <span>12,458 students enrolled</span>
              </div>
              <div class="flex items-center">
                <i class="fas fa-clock text-gray-200 mr-2"></i>
                <span>32 hours</span>
              </div>
            </div>

            <div class="flex flex-wrap gap-3">
              <button class="px-6 py-3 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-gray-100 transition">
                <i class="fas fa-play mr-2"></i> Preview Course
              </button>
              <button class="px-6 py-3 bg-indigo-800 text-white rounded-lg font-semibold hover:bg-indigo-900 transition">
                <i class="fas fa-shopping-cart mr-2"></i> Enroll Now
              </button>
            </div>
          </div>
          <div class="md:w-1/3 md:pl-8">
            <div class="bg-white bg-opacity-10 backdrop-blur-md rounded-xl p-6 border border-white border-opacity-20">
              <div class="flex items-center mb-4">
                <div class="w-12 h-12 rounded-full bg-indigo-500 flex items-center justify-center">
                  <i class="fas fa-user text-white text-xl"></i>
                </div>
                <div class="ml-3">
                  <h4 class="font-semibold">Alex Johnson</h4>
                  <p class="text-sm opacity-80">Senior JavaScript Developer</p>
                </div>
              </div>
              <div class="space-y-3 mb-6">
                <div class="flex justify-between">
                  <span class="opacity-80">Course Level</span>
                  <span class="font-medium">Intermediate</span>
                </div>
                <div class="flex justify-between">
                  <span class="opacity-80">Updated</span>
                  <span class="font-medium">June 2024</span>
                </div>
                <div class="flex justify-between">
                  <span class="opacity-80">Language</span>
                  <span class="font-medium">English</span>
                </div>
                <div class="flex justify-between">
                  <span class="opacity-80">Certificate</span>
                  <span class="font-medium">Yes</span>
                </div>
              </div>
              <div class="border-t border-white border-opacity-20 pt-4">
                <div class="flex justify-between items-center">
                  <span class="text-xl font-bold">$89.99</span>
                  <span class="line-through opacity-70">$129.99</span>
                  <span class="bg-yellow-500 text-yellow-900 px-2 py-1 rounded text-xs font-bold">30% OFF</span>
                </div>
                <p class="text-xs opacity-70 mt-1">Offer ends in 2 days</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Course Content Section -->
    <section class="py-12 bg-white">
      <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row">
          <!-- Main Content -->
          <div class="md:w-2/3 md:pr-8">
            <!-- Tabs Navigation -->
            <div class="flex border-b border-gray-200 mb-8">
              <button class="tab-btn py-3 px-6 font-medium text-gray-600 border-b-2 border-transparent hover:text-indigo-600 active" data-tab="overview">
                Overview
              </button>
              <button class="tab-btn py-3 px-6 font-medium text-gray-600 border-b-2 border-transparent hover:text-indigo-600" data-tab="curriculum">
                Curriculum
              </button>
              <button class="tab-btn py-3 px-6 font-medium text-gray-600 border-b-2 border-transparent hover:text-indigo-600" data-tab="instructor">
                Instructor
              </button>
              <button class="tab-btn py-3 px-6 font-medium text-gray-600 border-b-2 border-transparent hover:text-indigo-600" data-tab="reviews">
                Reviews
              </button>
            </div>

            <!-- Tab Contents -->
            <div class="tab-content active" id="overview">
              <h2 class="text-2xl font-bold mb-4">Course Description</h2>
              <p class="text-gray-700 mb-6">This comprehensive JavaScript course takes you from the absolute basics to advanced concepts. You'll learn modern JavaScript (ES6+), functional programming, asynchronous JavaScript, and how to build real-world applications with popular frameworks.</p>

              <h3 class="text-xl font-semibold mb-3 mt-6">What You'll Learn</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div class="flex items-start">
                  <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                  <span>Master JavaScript fundamentals: variables, functions, objects, arrays</span>
                </div>
                <div class="flex items-start">
                  <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                  <span>Understand ES6+ features like arrow functions, destructuring, promises</span>
                </div>
                <div class="flex items-start">
                  <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                  <span>Work with asynchronous JavaScript using async/await</span>
                </div>
                <div class="flex items-start">
                  <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                  <span>Build projects with React, Vue, and Node.js</span>
                </div>
                <div class="flex items-start">
                  <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                  <span>Debug and optimize JavaScript applications</span>
                </div>
                <div class="flex items-start">
                  <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                  <span>Test JavaScript code with Jest and other testing frameworks</span>
                </div>
              </div>

              <h3 class="text-xl font-semibold mb-3">Who This Course Is For</h3>
              <ul class="list-disc pl-5 text-gray-700 mb-8 space-y-2">
                <li>Beginners who want to learn JavaScript from scratch</li>
                <li>Frontend developers looking to deepen their JavaScript knowledge</li>
                <li>Backend developers who want to add JavaScript to their skillset</li>
                <li>Anyone preparing for JavaScript developer interviews</li>
              </ul>

              <h3 class="text-xl font-semibold mb-3">Requirements</h3>
              <ul class="list-disc pl-5 text-gray-700 mb-8 space-y-2">
                <li>Basic understanding of HTML and CSS</li>
                <li>A computer with internet access</li>
                <li>No prior JavaScript experience required</li>
              </ul>
            </div>

            <div class="tab-content" id="curriculum">
              <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Course Curriculum</h2>
                <div class="text-sm text-gray-600">
                  <span>32 hours</span>
                  <span class="mx-2">•</span>
                  <span>125 lectures</span>
                </div>
              </div>

              <div class="space-y-4">
                <!-- Module 1 -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                  <div class="bg-gray-50 px-5 py-4 flex justify-between items-center cursor-pointer module-header">
                    <div>
                      <h3 class="font-semibold">Module 1: JavaScript Fundamentals</h3>
                      <p class="text-sm text-gray-600 mt-1">8 lectures • 2 hours 15 min</p>
                    </div>
                    <div class="module-content hidden">
                      <div class="border-t border-gray-200">
                        <div class="px-5 py-3 flex items-center hover:bg-gray-50 module-item">
                          <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-4">
                            <i class="fas fa-play text-indigo-600 text-xs"></i>
                          </div>
                          <div class="flex-grow">
                            <h4 class="font-medium">Introduction to JavaScript</h4>
                            <p class="text-sm text-gray-500">15 min</p>
                          </div>
                          <div class="flex items-center">
                            <i class="fas fa-lock text-gray-400 mr-3"></i>
                            <i class="far fa-eye text-gray-400"></i>
                          </div>
                        </div>
                        <div class="flex items-center">
                          <i class="fas fa-lock text-gray-400 mr-3"></i>
                          <i class="far fa-eye text-gray-400"></i>
                        </div>
                      </div>
                    </div>
                </div>

                <!-- Module 2 -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                  <div class="bg-gray-50 px-5 py-4 flex justify-between items-center cursor-pointer module-header">
                    <div>
                      <h3 class="font-semibold">Module 2: DOM Manipulation</h3>
                      <p class="text-sm text-gray-600 mt-1">10 lectures • 3 hours 40 min</p>
                    </div>
                    <div class="module-content hidden">
                      <div class="border-t border-gray-200">
                        <div class="px-5 py-3 flex items-center hover:bg-gray-50 module-item">
                          <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-4">
                            <i class="fas fa-play text-indigo-600 text-xs"></i>
                          </div>
                          <div class="flex-grow">
                            <h4 class="font-medium">Selecting DOM Elements</h4>
                            <p class="text-sm text-gray-500">25 min</p>
                          </div>
                          <div class="flex items-center">
                            <i class="fas fa-lock text-gray-400 mr-3"></i>
                            <i class="far fa-eye text-gray-400"></i>
                          </div>
                        </div>
                        <div class="flex items-center">
                          <i class="fas fa-lock text-gray-400 mr-3"></i>
                          <i class="far fa-eye text-gray-400"></i>
                        </div>
                      </div>
                    </div>
                </div>

                <!-- Module 3 -->
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                  <div class="bg-gray-50 px-5 py-4 flex justify-between items-center cursor-pointer module-header">
                    <div>
                      <h3 class="font-semibold">Module 3: Modern JavaScript (ES6+)</h3>
                      <p class="text-sm text-gray-600 mt-1">15 lectures • 4 hours 10 min</p>
                    </div>
                    <div class="module-content hidden">
                      <div class="border-t border-gray-200">
                        <div class="px-5 py-3 flex items-center hover:bg-gray-50 module-item">
                          <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-4">
                            <i class="fas fa-play text-indigo-600 text-xs"></i>
                          </div>
                          <div class="flex-grow">
                            <h4 class="font-medium">Arrow Functions</h4>
                            <p class="text-sm text-gray-500">20 min</p>
                          </div>
                          <div class="flex items-center">
                            <i class="fas fa-lock text-gray-400 mr-3"></i>
                            <i class="far fa-eye text-gray-400"></i>
                          </div>
                        </div>
                        <div class="flex items-center">
                          <i class="fas fa-lock text-gray-400 mr-3"></i>
                          <i class="far fa-eye text-gray-400"></i>
                        </div>
                      </div>
                    </div>
                </div>
              </div>
            </div>
          </div>

          <div class="tab-content" id="instructor">
            <h2 class="text-2xl font-bold mb-6">About the Instructor</h2>
            <div class="flex flex-col md:flex-row items-start gap-6">
              <div class="w-full md:w-1/4">
                <div class="bg-gray-100 rounded-lg p-4">
                  <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Instructor" class="w-full rounded-lg mb-4">
                  <div class="text-center">
                    <h3 class="font-bold text-lg">Alex Johnson</h3>
                    <p class="text-gray-600 mb-3">Senior JavaScript Developer</p>
                    <div class="flex justify-center space-x-3">
                      <a href="#" class="text-blue-500 hover:text-blue-700">
                        <i class="fab fa-twitter"></i>
                      </a>
                      <a href="#" class="text-blue-700 hover:text-blue-900">
                        <i class="fab fa-linkedin"></i>
                      </a>
                      <a href="#" class="text-gray-800 hover:text-gray-600">
                        <i class="fab fa-github"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="w-full md:w-3/4">
                <h3 class="text-xl font-semibold mb-3">Biography</h3>
                <p class="text-gray-700 mb-4">Alex Johnson is a senior JavaScript developer with over 10 years of experience building web applications. He has worked with companies like Google, Airbnb, and Netflix, helping them build scalable frontend architectures.</p>

                <p class="text-gray-700 mb-6">Alex specializes in modern JavaScript frameworks and has contributed to open-source projects like React and Vue.js. He's passionate about teaching and has helped over 50,000 students learn JavaScript through his online courses.</p>

                <h3 class="text-xl font-semibold mb-3">Experience</h3>
                <div class="space-y-4 mb-6">
                  <div class="border-l-4 border-indigo-500 pl-4 py-1">
                    <h4 class="font-medium">Senior Frontend Developer</h4>
                    <p class="text-gray-600">TechCorp • 2018 - Present</p>
                  </div>
                  <div class="border-l-4 border-indigo-500 pl-4 py-1">
                    <h4 class="font-medium">JavaScript Engineer</h4>
                    <p class="text-gray-600">WebSolutions • 2014 - 2018</p>
                  </div>
                </div>

                <h3 class="text-xl font-semibold mb-3">Other Courses</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                    <h4 class="font-medium mb-1"
