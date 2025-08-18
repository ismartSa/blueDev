<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'title' => 'Complete Web Development Bootcamp',
                'description' => 'Master HTML, CSS, JavaScript, React, Node.js, and MongoDB to become a full-stack developer.',
                'body' => 'This comprehensive bootcamp takes you from beginner to professional web developer. Learn modern web technologies including responsive design, JavaScript ES6+, React framework, backend development with Node.js, database management with MongoDB, and deployment strategies. Build real-world projects and create a professional portfolio.',
                'duration' => 120,
                'status' => true,
                'intro_video' => 'https://example.com/web-dev-bootcamp.mp4',
                'price' => 199.99,
            ],
            [
                'title' => 'Project Management Professional (PMP)® Certification',
                'name' => 'PMP Certification',
                'description' => 'Complete PMP certification preparation course based on PMBOK Guide 7th Edition with practice exams.',
                'body' => 'Prepare for the PMP certification exam with this comprehensive course covering all knowledge areas from the PMBOK Guide 7th Edition. Learn project integration, scope, schedule, cost, quality, resource, communications, risk, procurement, and stakeholder management. Includes 200+ practice questions, real-world case studies, and exam strategies.',
                'duration' => 60,
                'status' => true,
                'intro_video' => 'https://example.com/pmp-certification.mp4',
                'price' => 299.99,
            ],
            [
                'title' => 'Python Programming for Data Science',
                'description' => 'Learn Python programming with focus on data analysis, visualization, and machine learning.',
                'body' => 'Master Python programming for data science applications. Cover Python fundamentals, NumPy for numerical computing, Pandas for data manipulation, Matplotlib and Seaborn for visualization, and Scikit-learn for machine learning. Work with real datasets and build predictive models.',
                'duration' => 80,
                'status' => true,
                'intro_video' => 'https://example.com/python-data-science.mp4',
                'price' => 149.99,
            ],
            [
                'title' => 'Digital Marketing Mastery',
                'description' => 'Complete digital marketing course covering SEO, social media, PPC, email marketing, and analytics.',
                'body' => 'Become a digital marketing expert with this comprehensive course. Learn search engine optimization (SEO), Google Ads and Facebook advertising, social media marketing strategies, email marketing automation, content marketing, and web analytics. Create and execute successful marketing campaigns.',
                'duration' => 50,
                'status' => true,
                'intro_video' => 'https://example.com/digital-marketing.mp4',
                'price' => 179.99,
            ],
            [
                'title' => 'AWS Cloud Practitioner Certification',
                'description' => 'Prepare for AWS Cloud Practitioner certification and learn cloud computing fundamentals.',
                'body' => 'Get certified as an AWS Cloud Practitioner with this comprehensive preparation course. Learn cloud computing concepts, AWS core services (EC2, S3, RDS, Lambda), security and compliance, pricing models, and architectural best practices. Includes hands-on labs and practice exams.',
                'duration' => 40,
                'status' => true,
                'intro_video' => 'https://example.com/aws-cloud-practitioner.mp4',
                'price' => 129.99,
            ],
            [
                'title' => 'UI/UX Design Fundamentals',
                'description' => 'Learn user interface and user experience design principles using Figma and Adobe XD.',
                'body' => 'Master UI/UX design with this practical course. Learn design thinking methodology, user research techniques, wireframing and prototyping, visual design principles, and usability testing. Use industry-standard tools like Figma and Adobe XD to create professional designs.',
                'duration' => 45,
                'status' => true,
                'intro_video' => 'https://example.com/ui-ux-design.mp4',
                'price' => 159.99,
            ],
            [
                'title' => 'Cybersecurity Essentials',
                'description' => 'Learn cybersecurity fundamentals including network security, ethical hacking, and risk management.',
                'body' => 'Understand cybersecurity principles and practices. Cover network security, encryption, vulnerability assessment, penetration testing basics, incident response, and security policies. Learn to protect systems and data from cyber threats.',
                'duration' => 55,
                'status' => true,
                'intro_video' => 'https://example.com/cybersecurity-essentials.mp4',
                'price' => 189.99,
            ],
            [
                'title' => 'Mobile App Development with React Native',
                'description' => 'Build cross-platform mobile applications for iOS and Android using React Native.',
                'body' => 'Create professional mobile applications with React Native. Learn component-based architecture, navigation, state management with Redux, API integration, device features access, and app store deployment. Build and publish real mobile apps.',
                'duration' => 70,
                'status' => true,
                'intro_video' => 'https://example.com/react-native-mobile.mp4',
                'price' => 169.99,
            ],
            [
                'title' => 'Certified Information Systems Auditor® (CISA®)',
                'description' => 'Comprehensive CISA certification preparation with real-world audit scenarios and practical case studies.',
                'body' => 'Master information systems auditing with this comprehensive CISA certification course. Learn audit processes, governance frameworks, risk management, information systems acquisition and implementation, and business continuity. Includes real audit scenarios, case studies, and practice exams to ensure certification success.',
                'duration' => 120,
                'status' => true,
                'intro_video' => 'https://example.com/cisa-certification.mp4',
                'price' => 399.99,
            ],
        ];

        $this->createCoursesWithSections($courses);
    }

    /**
     * Create courses with their realistic sections
     */
    private function createCoursesWithSections(array $coursesData): void
    {
        $courseSections = [
            'Complete Web Development Bootcamp' => [
                ['title' => 'HTML & CSS Fundamentals', 'description' => 'Learn HTML structure, semantic elements, CSS styling, flexbox, and grid layouts'],
                ['title' => 'JavaScript Programming', 'description' => 'Master JavaScript fundamentals, DOM manipulation, ES6+ features, and async programming'],
                ['title' => 'React Framework', 'description' => 'Build dynamic user interfaces with React components, hooks, and state management'],
                ['title' => 'Backend Development', 'description' => 'Create server-side applications with Node.js, Express, and RESTful APIs'],
                ['title' => 'Database & Deployment', 'description' => 'Work with MongoDB, authentication, and deploy applications to production']
            ],
            'Project Management Professional (PMP)® Certification' => [
                ['title' => 'Project Management Fundamentals', 'description' => 'Introduction to project management principles, lifecycle, and PMBOK Guide framework'],
                ['title' => 'Integration & Scope Management', 'description' => 'Learn project charter, scope definition, WBS creation, and change control'],
                ['title' => 'Schedule & Cost Management', 'description' => 'Master scheduling techniques, cost estimation, budgeting, and earned value management'],
                ['title' => 'Quality & Risk Management', 'description' => 'Understand quality planning, risk identification, analysis, and response strategies']
            ],
            'Python Programming for Data Science' => [
                ['title' => 'Python Fundamentals', 'description' => 'Learn Python syntax, data types, control structures, and object-oriented programming'],
                ['title' => 'Data Manipulation with Pandas', 'description' => 'Master data cleaning, transformation, and analysis using Pandas library'],
                ['title' => 'Data Visualization', 'description' => 'Create compelling visualizations with Matplotlib, Seaborn, and Plotly'],
                ['title' => 'Machine Learning Basics', 'description' => 'Introduction to ML algorithms, model training, and evaluation with Scikit-learn']
            ],
            'Digital Marketing Mastery' => [
                ['title' => 'SEO & Content Marketing', 'description' => 'Learn search engine optimization, keyword research, and content strategy'],
                ['title' => 'Social Media Marketing', 'description' => 'Master Facebook, Instagram, LinkedIn, and Twitter marketing strategies'],
                ['title' => 'Paid Advertising', 'description' => 'Create effective Google Ads and Facebook Ads campaigns with ROI optimization'],
                ['title' => 'Analytics & Conversion', 'description' => 'Track performance with Google Analytics and optimize conversion rates']
            ],
            'AWS Cloud Practitioner Certification' => [
                ['title' => 'Cloud Computing Fundamentals', 'description' => 'Understand cloud concepts, deployment models, and AWS global infrastructure'],
                ['title' => 'Core AWS Services', 'description' => 'Learn EC2, S3, RDS, Lambda, and other essential AWS services'],
                ['title' => 'Security & Compliance', 'description' => 'Master AWS security best practices, IAM, and compliance frameworks'],
                ['title' => 'Pricing & Support', 'description' => 'Understand AWS pricing models, cost optimization, and support plans']
            ],
            'UI/UX Design Fundamentals' => [
                ['title' => 'Design Thinking Process', 'description' => 'Learn user-centered design methodology and design thinking principles'],
                ['title' => 'User Research & Testing', 'description' => 'Conduct user interviews, surveys, and usability testing for better insights'],
                ['title' => 'Wireframing & Prototyping', 'description' => 'Create wireframes and interactive prototypes using Figma and Adobe XD'],
                ['title' => 'Visual Design & Handoff', 'description' => 'Apply visual design principles and prepare designs for development']
            ],
            'Cybersecurity Essentials' => [
                ['title' => 'Security Fundamentals', 'description' => 'Understand cybersecurity principles, threat landscape, and risk assessment'],
                ['title' => 'Network Security', 'description' => 'Learn firewalls, VPNs, intrusion detection, and network monitoring'],
                ['title' => 'Ethical Hacking Basics', 'description' => 'Introduction to penetration testing, vulnerability assessment, and security tools'],
                ['title' => 'Incident Response', 'description' => 'Develop incident response plans and learn digital forensics basics']
            ],
            'Mobile App Development with React Native' => [
                ['title' => 'React Native Fundamentals', 'description' => 'Learn React Native setup, components, and cross-platform development concepts'],
                ['title' => 'Navigation & State Management', 'description' => 'Implement navigation patterns and manage app state with Redux'],
                ['title' => 'Device Features & APIs', 'description' => 'Access camera, GPS, push notifications, and integrate with third-party APIs'],
                ['title' => 'Testing & Deployment', 'description' => 'Test your apps and deploy to App Store and Google Play Store']
            ],
            'Certified Information Systems Auditor® (CISA®)' => [
                ['title' => 'Information Systems Auditing Process', 'description' => 'Master audit planning, risk assessment, evidence collection, and reporting standards'],
                ['title' => 'Governance and Management of IT', 'description' => 'Learn IT governance frameworks, strategic planning, and organizational structures'],
                ['title' => 'Information Systems Acquisition, Development and Implementation', 'description' => 'Understand SDLC, project management, system implementation, and change management'],
                ['title' => 'Information Systems Operations and Business Resilience', 'description' => 'Cover operations management, incident response, business continuity, and disaster recovery'],
                ['title' => 'Protection of Information Assets', 'description' => 'Learn information security, access controls, encryption, and data protection strategies']
            ]
        ];

        foreach ($coursesData as $courseData) {
            $course = Course::factory()->create($courseData);
            
            $sections = $courseSections[$courseData['title']] ?? [];
            foreach ($sections as $index => $sectionData) {
                Section::create([
                    'course_id' => $course->id,
                    'title' => $sectionData['title'],
                    'description' => $sectionData['description'],
                    'order' => $index + 1,
                ]);
            }
        }
    }
}
