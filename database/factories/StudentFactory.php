<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class StudentFactory extends Factory
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
            'student_id' => null,
            'parent_id' => null,
            'name' => fake()->name(),
            'password' => '12345678',
            'image'=> 'users/students/MUomwBVJMuLvWGEzIZSAbbig0V7qmDa7HblLnmIC.jpg',
            'class' => null,
            'section' => null,
        ];
    }

}
