<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Course;

class CisaQuestionsSeeder extends Seeder
{
    public function run()
    {
        // Find CISA course
        $cisaCourse = Course::where('title', 'Certified Information Systems Auditor® (CISA®)')->first();
        
        if (!$cisaCourse) {
            $this->command->error('CISA course not found!');
            return;
        }

        // Get CISA quizzes
        $quizzes = $cisaCourse->quizzes;
        
        if ($quizzes->isEmpty()) {
            $this->command->error('No CISA quizzes found! Run CisaQuizSeeder first.');
            return;
        }

        // Questions data for each quiz
        $questionsData = [
            // Domain 1: Information Systems Auditing Process
            'Information Systems Auditing Process Fundamentals' => [
                [
                    'question_en' => 'What is the PRIMARY purpose of an IS audit charter?',
                    'question_ar' => 'ما هو الغرض الأساسي من ميثاق مراجعة نظم المعلومات؟',
                    'answers' => [
                        ['text_en' => 'To define the authority, responsibility, and accountability of the IS audit function', 'text_ar' => 'لتحديد سلطة ومسؤولية ومساءلة وظيفة مراجعة نظم المعلومات', 'correct' => true],
                        ['text_en' => 'To establish audit procedures and methodologies', 'text_ar' => 'لوضع إجراءات ومنهجيات المراجعة', 'correct' => false],
                        ['text_en' => 'To document audit findings and recommendations', 'text_ar' => 'لتوثيق نتائج المراجعة والتوصيات', 'correct' => false],
                        ['text_en' => 'To schedule annual audit activities', 'text_ar' => 'لجدولة أنشطة المراجعة السنوية', 'correct' => false]
                    ]
                ],
                [
                    'question_en' => 'Which of the following is the MOST important factor when determining audit scope?',
                    'question_ar' => 'أي من العوامل التالية هو الأهم عند تحديد نطاق المراجعة؟',
                    'answers' => [
                        ['text_en' => 'Available audit resources', 'text_ar' => 'موارد المراجعة المتاحة', 'correct' => false],
                        ['text_en' => 'Management expectations', 'text_ar' => 'توقعات الإدارة', 'correct' => false],
                        ['text_en' => 'Risk assessment results', 'text_ar' => 'نتائج تقييم المخاطر', 'correct' => true],
                        ['text_en' => 'Previous audit findings', 'text_ar' => 'نتائج المراجعة السابقة', 'correct' => false]
                    ]
                ],
                [
                    'question_en' => 'What is the PRIMARY objective of substantive testing in an IS audit?',
                    'question_ar' => 'ما هو الهدف الأساسي من الاختبار الموضوعي في مراجعة نظم المعلومات؟',
                    'answers' => [
                        ['text_en' => 'To verify the accuracy and completeness of data', 'text_ar' => 'للتحقق من دقة واكتمال البيانات', 'correct' => true],
                        ['text_en' => 'To test the effectiveness of controls', 'text_ar' => 'لاختبار فعالية الضوابط', 'correct' => false],
                        ['text_en' => 'To assess system performance', 'text_ar' => 'لتقييم أداء النظام', 'correct' => false],
                        ['text_en' => 'To evaluate user access rights', 'text_ar' => 'لتقييم حقوق وصول المستخدمين', 'correct' => false]
                    ]
                ]
            ],
            
            // Domain 2: Governance and Management of IT
            'IT Governance and Management Assessment' => [
                [
                    'question_en' => 'Which framework is PRIMARILY used for IT governance?',
                    'question_ar' => 'أي إطار عمل يُستخدم بشكل أساسي لحوكمة تكنولوجيا المعلومات؟',
                    'answers' => [
                        ['text_en' => 'COBIT', 'text_ar' => 'كوبيت', 'correct' => true],
                        ['text_en' => 'ITIL', 'text_ar' => 'آيتيل', 'correct' => false],
                        ['text_en' => 'ISO 27001', 'text_ar' => 'آيزو 27001', 'correct' => false],
                        ['text_en' => 'NIST', 'text_ar' => 'نيست', 'correct' => false]
                    ]
                ],
                [
                    'question_en' => 'What is the PRIMARY purpose of an IT steering committee?',
                    'question_ar' => 'ما هو الغرض الأساسي من لجنة توجيه تكنولوجيا المعلومات؟',
                    'answers' => [
                        ['text_en' => 'To provide strategic direction and oversight for IT initiatives', 'text_ar' => 'لتوفير التوجه الاستراتيجي والإشراف على مبادرات تكنولوجيا المعلومات', 'correct' => true],
                        ['text_en' => 'To manage day-to-day IT operations', 'text_ar' => 'لإدارة عمليات تكنولوجيا المعلومات اليومية', 'correct' => false],
                        ['text_en' => 'To conduct technical reviews', 'text_ar' => 'لإجراء المراجعات التقنية', 'correct' => false],
                        ['text_en' => 'To approve IT budgets only', 'text_ar' => 'لاعتماد ميزانيات تكنولوجيا المعلومات فقط', 'correct' => false]
                    ]
                ],
                [
                    'question_en' => 'Which of the following BEST describes IT strategy alignment?',
                    'question_ar' => 'أي من التالي يصف بشكل أفضل مواءمة استراتيجية تكنولوجيا المعلومات؟',
                    'answers' => [
                        ['text_en' => 'IT strategy supports and enables business objectives', 'text_ar' => 'استراتيجية تكنولوجيا المعلومات تدعم وتمكن الأهداف التجارية', 'correct' => true],
                        ['text_en' => 'IT strategy focuses on technology trends', 'text_ar' => 'استراتيجية تكنولوجيا المعلومات تركز على اتجاهات التكنولوجيا', 'correct' => false],
                        ['text_en' => 'IT strategy minimizes costs', 'text_ar' => 'استراتيجية تكنولوجيا المعلومات تقلل التكاليف', 'correct' => false],
                        ['text_en' => 'IT strategy maximizes system performance', 'text_ar' => 'استراتيجية تكنولوجيا المعلومات تعظم أداء النظام', 'correct' => false]
                    ]
                ]
            ],
            
            // Domain 3: Information Systems Acquisition, Development and Implementation
            'Systems Development and Implementation Quiz' => [
                [
                    'question_en' => 'Which SDLC phase involves defining system requirements?',
                    'question_ar' => 'أي مرحلة من مراحل دورة حياة تطوير النظم تتضمن تحديد متطلبات النظام؟',
                    'answers' => [
                        ['text_en' => 'Planning', 'text_ar' => 'التخطيط', 'correct' => false],
                        ['text_en' => 'Analysis', 'text_ar' => 'التحليل', 'correct' => true],
                        ['text_en' => 'Design', 'text_ar' => 'التصميم', 'correct' => false],
                        ['text_en' => 'Implementation', 'text_ar' => 'التنفيذ', 'correct' => false]
                    ]
                ],
                [
                    'question_en' => 'What is the PRIMARY advantage of Agile methodology?',
                    'question_ar' => 'ما هي الميزة الأساسية لمنهجية أجايل؟',
                    'answers' => [
                        ['text_en' => 'Faster delivery and adaptability to change', 'text_ar' => 'التسليم الأسرع والقدرة على التكيف مع التغيير', 'correct' => true],
                        ['text_en' => 'Comprehensive documentation', 'text_ar' => 'التوثيق الشامل', 'correct' => false],
                        ['text_en' => 'Predictable timelines', 'text_ar' => 'الجداول الزمنية القابلة للتنبؤ', 'correct' => false],
                        ['text_en' => 'Lower development costs', 'text_ar' => 'تكاليف تطوير أقل', 'correct' => false]
                    ]
                ],
                [
                    'question_en' => 'Which control is MOST important during system implementation?',
                    'question_ar' => 'أي ضابط هو الأهم أثناء تنفيذ النظام؟',
                    'answers' => [
                        ['text_en' => 'Change management controls', 'text_ar' => 'ضوابط إدارة التغيير', 'correct' => true],
                        ['text_en' => 'Performance monitoring', 'text_ar' => 'مراقبة الأداء', 'correct' => false],
                        ['text_en' => 'User training', 'text_ar' => 'تدريب المستخدمين', 'correct' => false],
                        ['text_en' => 'System documentation', 'text_ar' => 'توثيق النظام', 'correct' => false]
                    ]
                ]
            ],
            
            // Domain 4: Information Systems Operations and Business Resilience
            'IT Operations and Business Resilience' => [
                [
                    'question_en' => 'What is the PRIMARY objective of a business continuity plan?',
                    'question_ar' => 'ما هو الهدف الأساسي من خطة استمرارية الأعمال؟',
                    'answers' => [
                        ['text_en' => 'To ensure critical business functions continue during disruptions', 'text_ar' => 'لضمان استمرار الوظائف التجارية الحرجة أثناء الاضطرابات', 'correct' => true],
                        ['text_en' => 'To backup all data regularly', 'text_ar' => 'لنسخ جميع البيانات احتياطياً بانتظام', 'correct' => false],
                        ['text_en' => 'To maintain system performance', 'text_ar' => 'للحفاظ على أداء النظام', 'correct' => false],
                        ['text_en' => 'To reduce operational costs', 'text_ar' => 'لتقليل التكاليف التشغيلية', 'correct' => false]
                    ]
                ],
                [
                    'question_en' => 'Which metric is MOST important for measuring system availability?',
                    'question_ar' => 'أي مقياس هو الأهم لقياس توفر النظام؟',
                    'answers' => [
                        ['text_en' => 'Mean Time Between Failures (MTBF)', 'text_ar' => 'متوسط الوقت بين الأعطال', 'correct' => false],
                        ['text_en' => 'Recovery Time Objective (RTO)', 'text_ar' => 'هدف وقت الاستعادة', 'correct' => false],
                        ['text_en' => 'Uptime percentage', 'text_ar' => 'نسبة وقت التشغيل', 'correct' => true],
                        ['text_en' => 'Response time', 'text_ar' => 'وقت الاستجابة', 'correct' => false]
                    ]
                ],
                [
                    'question_en' => 'What is the FIRST step in incident response?',
                    'question_ar' => 'ما هي الخطوة الأولى في الاستجابة للحوادث؟',
                    'answers' => [
                        ['text_en' => 'Incident identification and classification', 'text_ar' => 'تحديد وتصنيف الحادث', 'correct' => true],
                        ['text_en' => 'Containment of the incident', 'text_ar' => 'احتواء الحادث', 'correct' => false],
                        ['text_en' => 'Evidence collection', 'text_ar' => 'جمع الأدلة', 'correct' => false],
                        ['text_en' => 'System recovery', 'text_ar' => 'استعادة النظام', 'correct' => false]
                    ]
                ]
            ],
            
            // Domain 5: Protection of Information Assets
            'Information Asset Protection and Security' => [
                [
                    'question_en' => 'Which principle of information security ensures data is not modified by unauthorized parties?',
                    'question_ar' => 'أي مبدأ من مبادئ أمن المعلومات يضمن عدم تعديل البيانات من قبل أطراف غير مخولة؟',
                    'answers' => [
                        ['text_en' => 'Confidentiality', 'text_ar' => 'السرية', 'correct' => false],
                        ['text_en' => 'Integrity', 'text_ar' => 'التكامل', 'correct' => true],
                        ['text_en' => 'Availability', 'text_ar' => 'التوفر', 'correct' => false],
                        ['text_en' => 'Non-repudiation', 'text_ar' => 'عدم الإنكار', 'correct' => false]
                    ]
                ],
                [
                    'question_en' => 'What is the PRIMARY purpose of data classification?',
                    'question_ar' => 'ما هو الغرض الأساسي من تصنيف البيانات؟',
                    'answers' => [
                        ['text_en' => 'To determine appropriate security controls based on data sensitivity', 'text_ar' => 'لتحديد الضوابط الأمنية المناسبة بناءً على حساسية البيانات', 'correct' => true],
                        ['text_en' => 'To organize data for storage efficiency', 'text_ar' => 'لتنظيم البيانات لكفاءة التخزين', 'correct' => false],
                        ['text_en' => 'To improve system performance', 'text_ar' => 'لتحسين أداء النظام', 'correct' => false],
                        ['text_en' => 'To reduce storage costs', 'text_ar' => 'لتقليل تكاليف التخزين', 'correct' => false]
                    ]
                ],
                [
                    'question_en' => 'Which access control model is based on user roles and responsibilities?',
                    'question_ar' => 'أي نموذج للتحكم في الوصول يعتمد على أدوار ومسؤوليات المستخدمين؟',
                    'answers' => [
                        ['text_en' => 'Discretionary Access Control (DAC)', 'text_ar' => 'التحكم في الوصول التقديري', 'correct' => false],
                        ['text_en' => 'Mandatory Access Control (MAC)', 'text_ar' => 'التحكم في الوصول الإلزامي', 'correct' => false],
                        ['text_en' => 'Role-Based Access Control (RBAC)', 'text_ar' => 'التحكم في الوصول القائم على الأدوار', 'correct' => true],
                        ['text_en' => 'Attribute-Based Access Control (ABAC)', 'text_ar' => 'التحكم في الوصول القائم على الخصائص', 'correct' => false]
                    ]
                ]
            ]
        ];

        // Create questions and answers for each quiz
        foreach ($questionsData as $quizTitle => $questions) {
            $quiz = $quizzes->firstWhere('title', $quizTitle);
            
            if (!$quiz) {
                $this->command->warn("Quiz '{$quizTitle}' not found, skipping...");
                continue;
            }

            foreach ($questions as $questionData) {
                $question = Question::create([
                    'quiz_id' => $quiz->id,
                    'question_title_en' => $questionData['question_en'],
                    'question_title_ar' => $questionData['question_ar'],
                    'question_type_id' => 1, // Multiple choice
                    'correct_answers_required' => 1,
                    'demo' => false
                ]);

                foreach ($questionData['answers'] as $answerData) {
                    Answer::create([
                        'question_id' => $question->id,
                        'answer_en' => $answerData['text_en'],
                        'answer_ar' => $answerData['text_ar'],
                        'is_correct' => $answerData['correct']
                    ]);
                }
            }
        }

        $this->command->info('Successfully created questions and answers for all CISA quizzes!');
    }
}