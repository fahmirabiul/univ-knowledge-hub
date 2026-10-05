<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\ScopusQuartile;
use App\Enums\SintaTier;
use App\Models\Category;
use App\Models\Publication;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySchemaAndRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_created_with_sinta_and_scopus_rankings(): void
    {
        $category = Category::factory()->create([
            'name' => 'Artificial Intelligence & Machine Learning',
            'slug' => 'ai-and-machine-learning',
            'sinta_tier' => SintaTier::S1,
            'scopus_quartile' => ScopusQuartile::Q1,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'slug' => 'ai-and-machine-learning',
            'sinta_tier' => 'S1',
            'scopus_quartile' => 'Q1',
        ]);

        $this->assertInstanceOf(SintaTier::class, $category->sinta_tier);
        $this->assertInstanceOf(ScopusQuartile::class, $category->scopus_quartile);
        $this->assertTrue($category->sinta_tier->isIndexed());
        $this->assertTrue($category->scopus_quartile->isIndexed());
    }

    public function test_category_slug_must_be_unique(): void
    {
        Category::factory()->create(['slug' => 'computer-science']);

        $this->expectException(QueryException::class);
        Category::factory()->create(['slug' => 'computer-science']);
    }

    public function test_category_has_many_publications(): void
    {
        $category = Category::factory()->create();
        $publication = Publication::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($category->publications->contains($publication));
        $this->assertEquals(1, $category->publications->count());
    }

    public function test_category_scopes_for_rankings_and_active_state(): void
    {
        Category::factory()->create([
            'name' => 'Q1 Journal Field',
            'slug' => 'q1-field',
            'scopus_quartile' => ScopusQuartile::Q1,
            'sinta_tier' => SintaTier::S1,
            'is_active' => true,
        ]);

        Category::factory()->create([
            'name' => 'Unindexed Field',
            'slug' => 'unindexed-field',
            'scopus_quartile' => ScopusQuartile::NON_SCOPUS,
            'sinta_tier' => SintaTier::NON_SINTA,
            'is_active' => false,
        ]);

        $this->assertEquals(1, Category::active()->count());
        $this->assertEquals(1, Category::scopusIndexed()->count());
        $this->assertEquals(1, Category::sintaIndexed()->count());
    }
}
