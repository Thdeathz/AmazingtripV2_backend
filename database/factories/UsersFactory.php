<?php

namespace Database\Factories;

use App\Enums\UserRoleEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users>
 */
class UsersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name'=>$this->faker->name(),
            'address'=>$this->faker->address(),
            'email'=>$this->faker->unique()->email(),
            'phone'=>$this->faker->unique()->e164PhoneNumber(),
            'dob'=>$this->faker->dateTimeBetween('-60 years', '-10 years'),
            'password'=>Hash::make('abcd1234'),
            'nickname'=>$this->faker->name(),
            'role'  =>$this->faker->randomElement(UserRoleEnum::asArray()),
        ];
    }
}
