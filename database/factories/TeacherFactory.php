<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class TeacherFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => '12345678',
            'image'=> 'users/teachers/MUomwBVJMuLvWGEzIZSAbbig0V7qmDa7HblLnmIC.jpg',
            'address' => fake()->address(),
            'mobile' => fake()->phoneNumber(),
            'designation' => 'Teacher',
            'qualification' => 'MSC',
        ];
    }

}
