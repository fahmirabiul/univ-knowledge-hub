<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ScopusQuartile;
use App\Enums\SintaTier;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Category>
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Computer Science & Artificial Intelligence',
            'Biomedical Engineering & Health Informatics',
            'Renewable Energy & Power Systems',
            'Cybersecurity & Cryptography',
            'Data Science & Big Data Analytics',
            'Materials Science & Nanotechnology',
            'Civil & Environmental Engineering',
            'Economic Policy & Applied Finance',
            'Educational Technology & Pedagogy',
            'Public Health & Epidemiology',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'sinta_tier' => fake()->randomElement(SintaTier::cases()),
            'scopus_quartile' => fake()->randomElement(ScopusQuartile::cases()),
            'is_active' => true,
        ];
    }

    /**
     * Scopus Q1 state.
     */
    public function scopusQ1(): static
    {
        return $this->state(fn (array $attributes) => [
            'scopus_quartile' => ScopusQuartile::Q1,
        ]);
    }

    /**
     * SINTA 1 state.
     */
    public function sintaS1(): static
    {
        return $this->state(fn (array $attributes) => [
            'sinta_tier' => SintaTier::S1,
        ]);
    }
}
