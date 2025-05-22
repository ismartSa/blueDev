<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Programming', 'slug' => 'programming', 'description' => 'دورات في البرمجة وتطوير البرمجيات'],
            ['name' => 'Design', 'slug' => 'design', 'description' => 'دورات في التصميم الجرافيكي وتجربة المستخدم'],
            ['name' => 'Business', 'slug' => 'business', 'description' => 'دورات في إدارة الأعمال والتسويق'],
            ['name' => 'Languages', 'slug' => 'languages', 'description' => 'دورات في تعليم اللغات'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
