<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lecture;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Support\Str;

class CISALectureSeeder extends Seeder
{
    public function run(): void
    {
        $cisaCourse = Course::where('title', 'like', '%CISA%')->first();
        
        if (!$cisaCourse) {
            $this->command->error('CISA course not found!');
            return;
        }

        $sections = $cisaCourse->sections()->orderBy('order')->get();
        
        if ($sections->count() !== 5) {
            $this->command->error('Expected 5 sections for CISA course, found ' . $sections->count());
            return;
        }

        $this->createLecturesForSections($cisaCourse, $sections);
        
        $this->command->info('Successfully created 71 lectures for CISA course!');
    }

    private function createLecturesForSections($course, $sections): void
    {
        $lectureData = [
            // Section 1: Information Systems Auditing Process (15 lectures)
            0 => [
                ['name' => 'Introduction to IS Auditing', 'title' => 'Fundamentals of Information Systems Auditing', 'description' => 'Overview of IS auditing principles, objectives, and professional standards', 'duration' => 45],
                ['name' => 'Audit Planning Framework', 'title' => 'Strategic Audit Planning and Risk Assessment', 'description' => 'Developing comprehensive audit plans and conducting preliminary risk assessments', 'duration' => 50],
                ['name' => 'Risk Assessment Methodologies', 'title' => 'Advanced Risk Assessment Techniques', 'description' => 'Quantitative and qualitative risk assessment methods for IT environments', 'duration' => 55],
                ['name' => 'Control Frameworks', 'title' => 'IT Control Frameworks and Standards', 'description' => 'COBIT, COSO, and other control frameworks for IT governance', 'duration' => 40],
                ['name' => 'Audit Evidence Collection', 'title' => 'Gathering and Evaluating Audit Evidence', 'description' => 'Techniques for collecting sufficient and appropriate audit evidence', 'duration' => 45],
                ['name' => 'Sampling Techniques', 'title' => 'Statistical and Non-Statistical Sampling', 'description' => 'Audit sampling methods and their application in IT audits', 'duration' => 35],
                ['name' => 'Testing Procedures', 'title' => 'Substantive and Compliance Testing', 'description' => 'Designing and executing audit tests for IT controls', 'duration' => 50],
                ['name' => 'Documentation Standards', 'title' => 'Audit Documentation and Working Papers', 'description' => 'Professional standards for audit documentation and file management', 'duration' => 30],
                ['name' => 'Interview Techniques', 'title' => 'Effective Audit Interview Methods', 'description' => 'Conducting professional interviews and gathering testimonial evidence', 'duration' => 35],
                ['name' => 'Data Analytics in Auditing', 'title' => 'Computer-Assisted Audit Techniques (CAATs)', 'description' => 'Using data analytics and automated tools for audit procedures', 'duration' => 60],
                ['name' => 'Audit Findings Analysis', 'title' => 'Analyzing and Evaluating Audit Results', 'description' => 'Interpreting audit findings and assessing their significance', 'duration' => 40],
                ['name' => 'Report Writing', 'title' => 'Professional Audit Report Writing', 'description' => 'Crafting clear, concise, and actionable audit reports', 'duration' => 45],
                ['name' => 'Management Presentations', 'title' => 'Presenting Audit Results to Management', 'description' => 'Effective communication of audit findings to stakeholders', 'duration' => 35],
                ['name' => 'Follow-up Procedures', 'title' => 'Audit Follow-up and Monitoring', 'description' => 'Tracking remediation efforts and ensuring corrective actions', 'duration' => 40],
                ['name' => 'Quality Assurance', 'title' => 'Audit Quality Assurance and Review', 'description' => 'Implementing quality control measures in audit processes', 'duration' => 35]
            ],
            
            // Section 2: Governance and Management of IT (14 lectures)
            1 => [
                ['name' => 'IT Governance Fundamentals', 'title' => 'Introduction to IT Governance', 'description' => 'Principles and frameworks for effective IT governance', 'duration' => 45],
                ['name' => 'Strategic IT Planning', 'title' => 'IT Strategic Planning and Alignment', 'description' => 'Aligning IT strategy with business objectives and goals', 'duration' => 50],
                ['name' => 'IT Organization Structure', 'title' => 'IT Organizational Design and Structure', 'description' => 'Designing effective IT organizational structures and reporting lines', 'duration' => 40],
                ['name' => 'IT Policies and Procedures', 'title' => 'Developing IT Policies and Standards', 'description' => 'Creating comprehensive IT policies, procedures, and standards', 'duration' => 45],
                ['name' => 'IT Performance Management', 'title' => 'IT Performance Measurement and KPIs', 'description' => 'Establishing metrics and KPIs for IT performance monitoring', 'duration' => 40],
                ['name' => 'IT Resource Management', 'title' => 'Managing IT Resources and Capabilities', 'description' => 'Optimizing IT resources, skills, and capabilities', 'duration' => 35],
                ['name' => 'Vendor Management', 'title' => 'IT Vendor and Third-Party Management', 'description' => 'Managing relationships with IT vendors and service providers', 'duration' => 45],
                ['name' => 'IT Investment Management', 'title' => 'IT Investment and Portfolio Management', 'description' => 'Managing IT investments and project portfolios', 'duration' => 50],
                ['name' => 'Risk Governance', 'title' => 'IT Risk Governance and Oversight', 'description' => 'Establishing risk governance frameworks and oversight mechanisms', 'duration' => 45],
                ['name' => 'Compliance Management', 'title' => 'IT Compliance and Regulatory Management', 'description' => 'Managing compliance with IT regulations and standards', 'duration' => 40],
                ['name' => 'IT Steering Committees', 'title' => 'IT Steering Committees and Decision Making', 'description' => 'Establishing effective IT governance committees and processes', 'duration' => 35],
                ['name' => 'Change Management', 'title' => 'IT Change Management and Control', 'description' => 'Implementing change management processes for IT environments', 'duration' => 45],
                ['name' => 'IT Service Management', 'title' => 'IT Service Management Frameworks', 'description' => 'ITIL and other service management frameworks', 'duration' => 50],
                ['name' => 'Governance Maturity', 'title' => 'IT Governance Maturity Assessment', 'description' => 'Assessing and improving IT governance maturity levels', 'duration' => 40]
            ],
            
            // Section 3: Information Systems Acquisition, Development and Implementation (15 lectures)
            2 => [
                ['name' => 'SDLC Overview', 'title' => 'Systems Development Life Cycle Fundamentals', 'description' => 'Overview of SDLC methodologies and best practices', 'duration' => 45],
                ['name' => 'Requirements Management', 'title' => 'Requirements Gathering and Management', 'description' => 'Techniques for gathering, documenting, and managing system requirements', 'duration' => 50],
                ['name' => 'System Design Principles', 'title' => 'System Architecture and Design', 'description' => 'Principles of secure and efficient system design', 'duration' => 55],
                ['name' => 'Agile Methodologies', 'title' => 'Agile Development and DevOps', 'description' => 'Agile development practices and DevOps integration', 'duration' => 45],
                ['name' => 'Project Management', 'title' => 'IT Project Management Best Practices', 'description' => 'Managing IT projects from initiation to closure', 'duration' => 50],
                ['name' => 'Quality Assurance', 'title' => 'Software Quality Assurance and Testing', 'description' => 'QA processes, testing methodologies, and quality metrics', 'duration' => 45],
                ['name' => 'Security by Design', 'title' => 'Secure Development Practices', 'description' => 'Integrating security controls throughout the development process', 'duration' => 50],
                ['name' => 'Code Review Processes', 'title' => 'Code Review and Static Analysis', 'description' => 'Implementing effective code review and analysis processes', 'duration' => 40],
                ['name' => 'Configuration Management', 'title' => 'Configuration and Version Control', 'description' => 'Managing system configurations and version control', 'duration' => 35],
                ['name' => 'System Implementation', 'title' => 'System Deployment and Implementation', 'description' => 'Planning and executing system implementations', 'duration' => 45],
                ['name' => 'Data Migration', 'title' => 'Data Migration and Conversion', 'description' => 'Managing data migration projects and ensuring data integrity', 'duration' => 40],
                ['name' => 'User Acceptance Testing', 'title' => 'User Acceptance Testing and Training', 'description' => 'Conducting UAT and preparing users for system adoption', 'duration' => 35],
                ['name' => 'Go-Live Support', 'title' => 'Go-Live Support and Stabilization', 'description' => 'Supporting system go-live and post-implementation stabilization', 'duration' => 40],
                ['name' => 'Post-Implementation Review', 'title' => 'Post-Implementation Review and Optimization', 'description' => 'Conducting post-implementation reviews and system optimization', 'duration' => 35],
                ['name' => 'Maintenance Planning', 'title' => 'System Maintenance and Enhancement Planning', 'description' => 'Planning for ongoing system maintenance and enhancements', 'duration' => 30]
            ],
            
            // Section 4: Information Systems Operations and Business Resilience (13 lectures)
            3 => [
                ['name' => 'IT Operations Management', 'title' => 'IT Operations Management Fundamentals', 'description' => 'Principles of effective IT operations management', 'duration' => 45],
                ['name' => 'Service Level Management', 'title' => 'Service Level Agreements and Management', 'description' => 'Developing and managing SLAs for IT services', 'duration' => 40],
                ['name' => 'Capacity Planning', 'title' => 'IT Capacity Planning and Performance Management', 'description' => 'Planning and managing IT capacity and performance', 'duration' => 45],
                ['name' => 'Incident Management', 'title' => 'Incident Response and Management', 'description' => 'Implementing effective incident response processes', 'duration' => 50],
                ['name' => 'Problem Management', 'title' => 'Problem Management and Root Cause Analysis', 'description' => 'Identifying and resolving underlying IT problems', 'duration' => 45],
                ['name' => 'Business Continuity Planning', 'title' => 'Business Continuity and Disaster Recovery Planning', 'description' => 'Developing comprehensive business continuity plans', 'duration' => 55],
                ['name' => 'Disaster Recovery', 'title' => 'Disaster Recovery Implementation and Testing', 'description' => 'Implementing and testing disaster recovery procedures', 'duration' => 50],
                ['name' => 'Backup and Recovery', 'title' => 'Data Backup and Recovery Strategies', 'description' => 'Designing and implementing backup and recovery solutions', 'duration' => 45],
                ['name' => 'High Availability', 'title' => 'High Availability and Fault Tolerance', 'description' => 'Designing systems for high availability and fault tolerance', 'duration' => 40],
                ['name' => 'Monitoring and Alerting', 'title' => 'System Monitoring and Alerting', 'description' => 'Implementing comprehensive monitoring and alerting systems', 'duration' => 35],
                ['name' => 'Performance Optimization', 'title' => 'System Performance Tuning and Optimization', 'description' => 'Optimizing system performance and resource utilization', 'duration' => 40],
                ['name' => 'Crisis Management', 'title' => 'IT Crisis Management and Communication', 'description' => 'Managing IT crises and stakeholder communication', 'duration' => 35],
                ['name' => 'Recovery Testing', 'title' => 'Business Continuity Testing and Validation', 'description' => 'Testing and validating business continuity procedures', 'duration' => 40]
            ],
            
            // Section 5: Protection of Information Assets (14 lectures)
            4 => [
                ['name' => 'Information Security Fundamentals', 'title' => 'Information Security Principles and Concepts', 'description' => 'Core principles of information security and risk management', 'duration' => 45],
                ['name' => 'Access Control Systems', 'title' => 'Access Control Models and Implementation', 'description' => 'Implementing effective access control systems and policies', 'duration' => 50],
                ['name' => 'Identity Management', 'title' => 'Identity and Access Management (IAM)', 'description' => 'Designing and managing identity and access management systems', 'duration' => 45],
                ['name' => 'Cryptography', 'title' => 'Cryptography and Encryption Technologies', 'description' => 'Understanding and implementing cryptographic controls', 'duration' => 55],
                ['name' => 'Network Security', 'title' => 'Network Security Architecture and Controls', 'description' => 'Implementing network security controls and monitoring', 'duration' => 50],
                ['name' => 'Data Classification', 'title' => 'Data Classification and Handling', 'description' => 'Classifying and protecting sensitive data assets', 'duration' => 40],
                ['name' => 'Data Loss Prevention', 'title' => 'Data Loss Prevention (DLP) Solutions', 'description' => 'Implementing DLP technologies and processes', 'duration' => 45],
                ['name' => 'Vulnerability Management', 'title' => 'Vulnerability Assessment and Management', 'description' => 'Identifying and managing system vulnerabilities', 'duration' => 45],
                ['name' => 'Security Monitoring', 'title' => 'Security Information and Event Management (SIEM)', 'description' => 'Implementing security monitoring and SIEM solutions', 'duration' => 50],
                ['name' => 'Penetration Testing', 'title' => 'Penetration Testing and Ethical Hacking', 'description' => 'Conducting penetration tests and security assessments', 'duration' => 55],
                ['name' => 'Security Awareness', 'title' => 'Security Awareness and Training Programs', 'description' => 'Developing effective security awareness programs', 'duration' => 35],
                ['name' => 'Incident Response', 'title' => 'Security Incident Response and Forensics', 'description' => 'Managing security incidents and digital forensics', 'duration' => 50],
                ['name' => 'Compliance Frameworks', 'title' => 'Security Compliance and Regulatory Requirements', 'description' => 'Managing compliance with security regulations and standards', 'duration' => 40],
                ['name' => 'Security Metrics', 'title' => 'Security Metrics and Performance Measurement', 'description' => 'Measuring and reporting on security program effectiveness', 'duration' => 35]
            ]
        ];

        $lectureOrder = 1;
        
        foreach ($sections as $sectionIndex => $section) {
            $sectionLectures = $lectureData[$sectionIndex] ?? [];
            
            foreach ($sectionLectures as $lectureInfo) {
                Lecture::create([
                    'name' => $lectureInfo['name'],
                    'title' => $lectureInfo['title'],
                    'description' => $lectureInfo['description'],
                    'slug' => Str::slug($lectureInfo['title']),
                    'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                    'duration' => $lectureInfo['duration'],
                    'order' => $lectureOrder++,
                    'course_id' => $course->id,
                    'section_id' => $section->id,
                ]);
            }
        }
    }
}