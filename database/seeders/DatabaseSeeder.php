<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\Parents;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
//        Teacher::factory()
//            ->count(20)
//            ->sequence(fn ($sequence) => [
//                'teacher_id' => 'SMS-T-' . str_pad($sequence->index + 1, 4, '0', STR_PAD_LEFT),
//            ])
//            ->create();
//
//        Staff::factory()
//            ->count(20)
//            ->sequence(fn ($sequence) => [
//                'staff_id' => 'SMS-S-' . str_pad($sequence->index + 1, 4, '0', STR_PAD_LEFT),
//            ])
//            ->create();
//
//        Parents::factory()
//            ->count(20)
//            ->sequence(fn ($sequence) => [
//                'parent_id' => 'SMS-P-' . str_pad($sequence->index + 1, 4, '0', STR_PAD_LEFT),
//            ])
//            ->create();
//
//        $classes = ['Nursery','Prep','I','II','III','IV','V','VI','VII','VIII','IX','X'];
//        $sections = ['A','B','C','D','E','F'];
//        Student::factory()
//            ->count(20)
//            ->sequence(function ($sequence) use ($classes, $sections) {
//                // Random class & section for each student
//                $class = $classes[array_rand($classes)];
//                $section = $sections[array_rand($sections)];
//
//                return [
//                    'class' => $class,
//                    'section' => $section,
//                    'student_id' => sprintf(
//                        'SMS-%s-%s-%04d',
//                        $class,
//                        $section,
//                        $sequence->index + 1
//                    ),
//                    'parent_id' => 'SMS-P-' . str_pad($sequence->index + 1, 4, '0', STR_PAD_LEFT),
//                ];
//            })
//            ->create();


        $classNames = Classes::$classes;
        $sections   = Classes::$sections;

        // 3. Insert in exact order
        foreach ($classNames as $class) {
            foreach ($sections as $section) {
                $classId = "{$class}-{$section}";

                Classes::factory()->create([
                    'class_id'      => $classId,
                    'teacher_id'    => 'SMS-T-0001',
                    'capacity'      => 30,
                    'academic_year' => now()->year.'-01-01',
                ]);
            }
        }

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);
    }
}
