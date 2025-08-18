<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\Course;
use App\Models\Section;

class CisaQuizSeeder extends Seeder
{
    public function run()
    {
        // Find CISA course
        $cisaCourse = Course::where('title', 'Certified Information Systems Auditor® (CISA®)')->first();
        
        if (!$cisaCourse) {
            $this->command->error('CISA course not found!');
            return;
        }

        // Get CISA sections
        $sections = $cisaCourse->sections;
        
        // CISA Quiz data with real certification content
        $quizzes = [
            [
                'title' => 'Information Systems Auditing Process Fundamentals',
                'description' => 'Test your knowledge of IS audit planning, risk assessment, evidence collection, and audit reporting standards according to ISACA guidelines. This quiz covers Domain 1 of CISA certification with 25 questions requiring 75% to pass.',
                'time_limit' => 45,
                'passing_score' => 75
            ],
            [
                'title' => 'IT Governance and Management Assessment',
                'description' => 'Evaluate your understanding of IT governance frameworks, strategic planning, organizational structures, and management practices. Focus on COBIT, ITIL, and other governance frameworks for Domain 2 of CISA certification.',
                'time_limit' => 40,
                'passing_score' => 70
            ],
            [
                'title' => 'Systems Development and Implementation Quiz',
                'description' => 'Assess your knowledge of SDLC methodologies, project management, system implementation, and change management processes. Domain 3 quiz covering Agile, Waterfall, and DevOps methodologies.',
                'time_limit' => 50,
                'passing_score' => 72
            ],
            [
                'title' => 'IT Operations and Business Resilience',
                'description' => 'Test your understanding of IT operations management, incident response, business continuity planning, and disaster recovery strategies. Domain 4 assessment focusing on operational excellence and SLA management.',
                'time_limit' => 35,
                'passing_score' => 73
            ],
            [
                'title' => 'Information Asset Protection and Security',
                'description' => 'Evaluate your knowledge of information security controls, access management, encryption, data classification, and privacy protection. Domain 5 comprehensive quiz covering CIA triad and risk management.',
                'time_limit' => 55,
                'passing_score' => 74
            ]
        ];

        foreach ($quizzes as $quizData) {
            Quiz::create([
                'title' => $quizData['title'],
                'description' => $quizData['description'],
                'course_id' => $cisaCourse->id,
                'time_limit' => $quizData['time_limit'],
                'passing_score' => $quizData['passing_score'],
                'is_active' => true
            ]);
        }

        $this->command->info('Successfully created 5 CISA quizzes with real certification data!');
    }
}