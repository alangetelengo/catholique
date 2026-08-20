<?php

namespace Database\Factories;

use App\Models\ExpenseType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ExpenseType>
 */
class ExpenseTypeFactory extends Factory
{
    protected $model = ExpenseType::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nom = fake()->unique()->words(2, true);

        return [
            'code' => Str::slug($nom, '_'),
            'nom' => ucfirst($nom),
            'description' => fake()->optional()->sentence(),
            'actif' => true,
            'ordre' => fake()->numberBetween(1, 50),
        ];
    }
}
