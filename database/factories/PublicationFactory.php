<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PublicationStatus;
use App\Models\Category;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publication>
 */
class PublicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Publication>
     */
    protected $model = Publication::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->numberBetween(2018, 2026);
        $doiPrefix = '10.'.fake()->numberBetween(1000, 9999);
        $doiSuffix = fake()->regexify('[a-z0-9]{4,8}\.[0-9]{4}');
        $doi = "{$doiPrefix}/{$doiSuffix}";

        return [
            'submitter_id' => User::factory()->dosen(),
            'category_id' => Category::factory(),
            'doi' => $doi,
            'title' => fake()->sentence(8),
            'abstract' => fake()->paragraphs(3, true),
            'journal_name' => 'IEEE Transactions on '.fake()->words(3, true),
            'publication_year' => $year,
            'volume' => (string) fake()->numberBetween(1, 30),
            'issue' => (string) fake()->numberBetween(1, 4),
            'pages' => fake()->numberBetween(1, 100).'-'.fake()->numberBetween(101, 200),
            'status' => PublicationStatus::PENDING,
            'file_path' => 'publications/'.fake()->uuid().'.pdf',
            'original_filename' => fake()->slug().'.pdf',
            'file_size_bytes' => fake()->numberBetween(500000, 5000000), // 500KB - 5MB
            'metadata_api' => [
                'DOI' => $doi,
                'source' => 'Crossref',
                'publisher' => 'IEEE',
                'indexed_at' => now()->toISOString(),
            ],
            'citation_count' => fake()->numberBetween(0, 150),
            'is_public' => true,
            'published_at' => null,
            'rejection_reason' => null,
        ];
    }

    /**
     * Approved / Published state.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::APPROVED,
            'published_at' => now()->subDays(fake()->numberBetween(1, 180)),
        ]);
    }

    /**
     * Pending state.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::PENDING,
            'published_at' => null,
        ]);
    }

    /**
     * Rejected state.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublicationStatus::REJECTED,
            'rejection_reason' => 'Format file dokumen tidak sesuai dengan template resmi LPPM atau data DOI tidak valid.',
            'published_at' => null,
        ]);
    }
}
