<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\AuthorRole;
use App\Models\Publication;
use App\Models\PublicationAuthor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationAuthorSchemaAndRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_publication_author_can_be_internal_user(): void
    {
        $dosen = User::factory()->dosen()->create([
            'name' => 'Dr. Hendra Wijaya',
            'email' => 'hendra.w@univ.ac.id',
            'faculty' => 'Fakultas Teknik',
        ]);
        $pub = Publication::factory()->create();

        $author = PublicationAuthor::create([
            'publication_id' => $pub->id,
            'user_id' => $dosen->id,
            'author_name' => $dosen->name,
            'author_email' => $dosen->email,
            'affiliation' => $dosen->faculty,
            'author_role' => AuthorRole::FIRST_AUTHOR,
            'author_order' => 1,
            'is_corresponding' => true,
            'is_external' => false,
        ]);

        $this->assertDatabaseHas('publication_authors', [
            'id' => $author->id,
            'user_id' => $dosen->id,
            'is_external' => false,
            'author_role' => 'penulis_pertama',
        ]);

        $this->assertInstanceOf(AuthorRole::class, $author->author_role);
        $this->assertEquals($dosen->id, $author->user->id);
        $this->assertTrue($author->author_role->isFirstAuthor());
    }

    public function test_publication_author_can_be_external_author(): void
    {
        $pub = Publication::factory()->create();

        $externalAuthor = PublicationAuthor::create([
            'publication_id' => $pub->id,
            'user_id' => null,
            'author_name' => 'Prof. Dr. David Miller, FRS',
            'author_email' => 'david.miller@cam.ac.uk',
            'affiliation' => 'University of Cambridge, United Kingdom',
            'author_role' => AuthorRole::CO_AUTHOR,
            'author_order' => 2,
            'is_corresponding' => false,
            'is_external' => true,
        ]);

        $this->assertDatabaseHas('publication_authors', [
            'id' => $externalAuthor->id,
            'user_id' => null,
            'is_external' => true,
            'affiliation' => 'University of Cambridge, United Kingdom',
        ]);

        $this->assertNull($externalAuthor->user);
        $this->assertTrue($externalAuthor->is_external);
    }

    public function test_author_scopes_for_external_internal_and_ordered(): void
    {
        $pub = Publication::factory()->create();

        PublicationAuthor::factory()->firstAuthor()->create([
            'publication_id' => $pub->id,
            'author_order' => 1,
            'is_corresponding' => true,
            'is_external' => false,
        ]);

        PublicationAuthor::factory()->external('MIT')->create([
            'publication_id' => $pub->id,
            'author_order' => 2,
            'is_corresponding' => false,
            'is_external' => true,
        ]);

        $this->assertEquals(1, PublicationAuthor::external()->count());
        $this->assertEquals(1, PublicationAuthor::internal()->count());
        $this->assertEquals(1, PublicationAuthor::corresponding()->count());

        $ordered = PublicationAuthor::ordered()->get();
        $this->assertEquals(1, $ordered->first()->author_order);
        $this->assertEquals(2, $ordered->last()->author_order);
    }
}
