<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Admin;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password'), // 默认密码
            'remember_token' => $this->faker->lexify('??????????'),
        ];
    }
}
