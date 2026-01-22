<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Designation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            // যদি Designation তৈরি না থাকে, তবে ডিফল্ট তৈরি হবে
            'designation_id' => Designation::factory(), 
            'phone' => $this->faker->phoneNumber(),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'joining_date' => $this->faker->date(),
            'address' => $this->faker->address(),
            'salary' => $this->faker->numberBetween(20000, 50000),
        ];
    }
}