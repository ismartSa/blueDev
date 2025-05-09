<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // الحصول على جميع المستخدمين والدورات
        $users = User::all();
        $courses = Course::all();

        // إذا لم تكن هناك بيانات كافية، قم بإنشاء بعض البيانات
        if ($users->count() < 5) {
            $users = User::factory(5)->create();
        }

        if ($courses->count() < 3) {
            $courses = Course::factory(3)->create();
        }

        // تسجيل المستخدمين في الدورات بشكل عشوائي
        foreach ($users as $user) {
            // تسجيل كل مستخدم في 1-3 دورات عشوائية
            $randomCourses = $courses->random(rand(1, min(3, $courses->count())));

            foreach ($randomCourses as $course) {
                // إنشاء تسجيل بحالات مختلفة
                $status = rand(1, 10);

                if ($status <= 7) {
                    // 70% من التسجيلات مؤكدة مع تقدم متفاوت
                    $progress = rand(10, 100);
                    $completionDate = $progress == 100 ? now()->subDays(rand(1, 30)) : null;

                    Enrollment::create([
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                        'enrollment_status' => 'confirmed',
                        'enrollment_date' => now()->subDays(rand(30, 180)),
                        'completion_date' => $completionDate,
                        'progress_percentage' => $progress,
                    ]);
                } elseif ($status <= 9) {
                    // 20% من التسجيلات معلقة
                    Enrollment::create([
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                        'enrollment_status' => 'pending',
                        'enrollment_date' => now()->subDays(rand(1, 14)),
                    ]);
                } else {
                    // 10% من التسجيلات ملغاة
                    Enrollment::create([
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                        'enrollment_status' => 'cancelled',
                        'enrollment_date' => now()->subDays(rand(30, 90)),
                    ]);
                }
            }
        }
    }
}
