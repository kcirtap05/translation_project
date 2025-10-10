<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Locale>
 */
class LocaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $locales = ['en', 'fr', 'es', 'de', 'it', 'jp', 'cn'];

        return [
            'code' => $this->faker->unique()->randomElement($locales),
            'name' => strtoupper($this->faker->unique()->randomElement($locales)),
            'is_active' => true,
        ];
    }
}
