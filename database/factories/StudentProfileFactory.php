<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            // প্রতিটি স্টুডেন্টের জন্য অটোমেটিক নতুন ইউজার তৈরি হবে
            'user_id' => User::factory(),
            
            // ক্লাস ও সেকশন টেস্ট থেকে পাঠানো হবে, না হলে এখান থেকে ডিফল্ট তৈরি হবে
            'class_id' => SchoolClass::factory(), 
            'section_id' => Section::factory(),
            
            // ফেইক ডাটা জেনারেটর
            'admission_no' => $this->faker->unique()->bothify('ADM-####'),
            'roll_no' => $this->faker->numberBetween(1, 100),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'dob' => $this->faker->date(),
            'address' => $this->faker->address(),
        ];
    }
}