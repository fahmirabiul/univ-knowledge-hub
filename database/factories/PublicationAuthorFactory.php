<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AuthorRole;
use App\Models\Publication;
use App\Models\PublicationAuthor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PublicationAuthor>
 */
class PublicationAuthorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<PublicationAuthor>
     */
    protected $model = PublicationAuthor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isExternal = fake()->boolean(30);

        return [
            'publication_id' => Publication::factory(),
            'user_id' => $isExternal ? null : User::factory()->dosen(),
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
            'affiliation' => $isExternal ? fake()->company().' University' : 'Universitas Knowledge Hub',
            'author_role' => fake()->randomElement(AuthorRole::cases()),
            'author_order' => 1,
            'is_corresponding' => fake()->boolean(25),
            'is_external' => $isExternal,
        ];
    }

    /**
     * First author state.
     */
    public function firstAuthor(): static
    {
        return $this->state(fn (array $attributes) => [
            'author_role' => AuthorRole::FIRST_AUTHOR,
            'author_order' => 1,
        ]);
    }

    /**
     * Corresponding author state.
     */
    public function corresponding(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_corresponding' => true,
        ]);
    }

    /**
     * External author state.
     */
    public function external(string $affiliation = 'Massachusetts Institute of Technology'): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'is_external' => true,
            'affiliation' => $affiliation,
        ]);
    }

    /**
     * Internal user author state.
     */
    public function internal(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'author_name' => $user->name,
            'author_email' => $user->email,
            'affiliation' => $user->faculty ?? 'Universitas Knowledge Hub',
            'is_external' => false,
        ]);
    }
}
