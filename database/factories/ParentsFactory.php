<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ParentsFactory extends Factory
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
        // Generate a short username and domain to fit within 24 chars
        $username = substr(fake()->userName(), 0, 10);   // max 10 chars
        $domain   = substr(fake()->domainName(), 0, 10); // max 10 chars
        $email    = $username . '@' . $domain;

        // Ensure total email length <= 24
        $email = substr($email, 0, 24);
        return [
            'parent_id' => null,
            'name' => substr(fake()->name(), 0, 23),
            'cnic' => fake()->numerify('#####-#######-#'),
            'email' => $email,
            'password' => '12345678',
            'image'=> 'users/parents/MUomwBVJMuLvWGEzIZSAbbig0V7qmDa7HblLnmIC.jpg',
            'address' => fake()->address(),
            'mobile' => fake()->numerify('###########'),
            'designation' => 'Govt Servant',
            'qualification' => 'MA',
        ];
    }

}
