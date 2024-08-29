<?php

namespace Database\Factories;

use Core\Domain\Enum\Rating;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Video>
 */
class VideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // array de valores possíveis do type
        $typeValues = array_column(Rating::cases(), 'value');

        return [
            'id' => (string) Str::uuid(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(20),
            'year_launched' => $this->faker->year(),
            'duration' => $this->faker->randomNumber(2),
            'rating' => $typeValues[array_rand($typeValues)],
            'opened' => $this->faker->boolean(),
            'created_at' => (string) now(),
            'updated_at' => (string) now(),
        ];
    }
}
