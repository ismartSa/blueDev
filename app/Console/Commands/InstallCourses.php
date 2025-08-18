<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Course;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

class InstallCourses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:courses {--force : Force reinstall courses}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install sample courses with categories';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        // Define categories
        $categories = [
            ['name' => 'Web Development', 'slug' => 'web-development', 'description' => 'Learn modern web development technologies'],
            ['name' => 'Mobile Development', 'slug' => 'mobile-development', 'description' => 'Build mobile applications for iOS and Android'],
            ['name' => 'Data Science', 'slug' => 'data-science', 'description' => 'Analyze data and build machine learning models'],
            ['name' => 'DevOps', 'slug' => 'devops', 'description' => 'Learn deployment and infrastructure management'],
            ['name' => 'Cybersecurity', 'slug' => 'cybersecurity', 'description' => 'Protect systems and data from cyber threats']
        ];

        // Define sample courses
        $courses = [
            [
                'title' => 'Laravel 11 Complete Course',
                'description' => 'Master Laravel 11 with modern PHP development practices',
                'category' => 'Web Development',
                'price' => 99.99,
                'duration' => 40,
                'level' => 'intermediate'
            ],
            [
                'title' => 'Vue.js 3 with Inertia.js',
                'description' => 'Build modern SPAs with Vue.js 3 and Inertia.js',
                'category' => 'Web Development',
                'price' => 79.99,
                'duration' => 30,
                'level' => 'beginner'
            ],
            [
                'title' => 'React Native Mobile Development',
                'description' => 'Create cross-platform mobile apps with React Native',
                'category' => 'Mobile Development',
                'price' => 129.99,
                'duration' => 50,
                'level' => 'intermediate'
            ],
            [
                'title' => 'Python Data Analysis',
                'description' => 'Analyze data using Python, Pandas, and NumPy',
                'category' => 'Data Science',
                'price' => 89.99,
                'duration' => 35,
                'level' => 'beginner'
            ],
            [
                'title' => 'Docker & Kubernetes',
                'description' => 'Containerize and orchestrate applications',
                'category' => 'DevOps',
                'price' => 149.99,
                'duration' => 45,
                'level' => 'advanced'
            ],
            [
                'title' => 'Ethical Hacking Fundamentals',
                'description' => 'Learn penetration testing and security assessment',
                'category' => 'Cybersecurity',
                'price' => 199.99,
                'duration' => 60,
                'level' => 'intermediate'
            ],
            // CISA Courses
            [
                'title' => 'CISA Certification Complete Guide',
                'description' => 'Comprehensive preparation for Certified Information Systems Auditor certification',
                'category' => 'Cybersecurity',
                'price' => 299.99,
                'duration' => 80,
                'level' => 'advanced'
            ],
            [
                'title' => 'Information Systems Auditing',
                'description' => 'Learn IS audit processes, controls, and risk assessment methodologies',
                'category' => 'Cybersecurity',
                'price' => 249.99,
                'duration' => 65,
                'level' => 'intermediate'
            ],
            [
                'title' => 'IT Governance and Risk Management',
                'description' => 'Master IT governance frameworks and risk management strategies',
                'category' => 'Cybersecurity',
                'price' => 199.99,
                'duration' => 55,
                'level' => 'intermediate'
            ],
            [
                'title' => 'Business Continuity and Disaster Recovery',
                'description' => 'Design and implement business continuity and disaster recovery plans',
                'category' => 'Cybersecurity',
                'price' => 179.99,
                'duration' => 45,
                'level' => 'intermediate'
            ],
            [
                'title' => 'Information Security Management',
                'description' => 'Implement and manage information security programs and controls',
                'category' => 'Cybersecurity',
                'price' => 229.99,
                'duration' => 60,
                'level' => 'advanced'
            ]
        ];

        $this->info('Installing categories...');
        $this->createCategories($categories, $force);

        $this->info('Installing courses...');
        $this->createCourses($courses, $force);

        $this->info('All courses and categories have been installed successfully!');

        if (!$force) {
            $this->warn('Use --force flag to update existing courses and categories.');
        }

        return Command::SUCCESS;
    }

    /**
     * Create categories
     */
    private function createCategories(array $categories, bool $force): void
    {
        foreach ($categories as $categoryData) {
            // Ensure slug is generated if not provided
            if (!isset($categoryData['slug'])) {
                $categoryData['slug'] = Str::slug($categoryData['name']);
            }

            $category = $force 
                ? Category::updateOrCreate(
                    ['slug' => $categoryData['slug']], 
                    $categoryData
                )
                : Category::firstOrCreate(
                    ['slug' => $categoryData['slug']], 
                    $categoryData
                );

            $this->line("Category '{$category->name}' " . ($force ? 'updated' : 'created'));
        }
    }

    /**
     * Create courses
     */
    private function createCourses(array $courses, bool $force): void
    {
        // Get first instructor or create one
        $instructor = User::role('instructor')->first() 
            ?? User::role('admin')->first() 
            ?? User::role('superadmin')->first();

        if (!$instructor) {
            $this->error('No instructor, admin, or superadmin user found. Please create users first.');
            return;
        }

        foreach ($courses as $courseData) {
            $category = Category::where('name', $courseData['category'])->first();
            
            if (!$category) {
                $this->error("Category '{$courseData['category']}' not found.");
                continue;
            }

            $courseAttributes = [
                'title' => $courseData['title'],
                'name' => $courseData['title'],
                'slug' => Str::slug($courseData['title']),
                'description' => $courseData['description'],
                'body' => $courseData['description'] . ' This comprehensive course will guide you through all the essential concepts and practical applications.',
                'price' => $courseData['price'],
                'duration' => $courseData['duration'],
                'level' => $courseData['level'],
                'category_id' => $category->id,
                'user_id' => $instructor->id,
                'status' => true
            ];

            $course = $force 
                ? Course::updateOrCreate(
                    ['title' => $courseData['title']], 
                    $courseAttributes
                )
                : Course::firstOrCreate(
                    ['title' => $courseData['title']], 
                    $courseAttributes
                );

            $this->line("Course '{$course->title}' " . ($force ? 'updated' : 'created'));
        }
    }
}