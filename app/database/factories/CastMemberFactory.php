<?php

namespace Database\Factories;

use Core\Domain\Enum\CastMemberType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CastMember>
 */
class CastMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // array de valores possíveis do type
        $typeValues = array_column(CastMemberType::cases(), 'value');

        CastMemberType::cases();
        return [
            'id' => (string) Str::uuid(),
            'name' => $this->faker->name(),
            'type' => $typeValues[array_rand($typeValues)],
            'created_at' => (string) now(),
            'updated_at' => (string) now(),
        ];
    }
}
