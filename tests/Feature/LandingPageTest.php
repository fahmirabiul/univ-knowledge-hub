<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Publication;
use App\Models\PublicationAuthor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_access_landing_page_and_sees_approved_publications_only(): void
    {
        $approved = Publication::factory()->approved()->create(['title' => 'Sistem Rekomendasi Terbuka']);
        $pending = Publication::factory()->pending()->create(['title' => 'Analisis Algoritma Belum Diverifikasi']);
        $rejected = Publication::factory()->rejected()->create(['title' => 'Naskah Ditolak Reviewer']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertViewIs('welcome');
        $response->assertSee('Sistem Rekomendasi Terbuka');
        $response->assertDontSee('Analisis Algoritma Belum Diverifikasi');
        $response->assertDontSee('Naskah Ditolak Reviewer');
        $response->assertSee('Masuk via SSO Kampus');
    }

    public function test_authenticated_admin_lppm_sees_lppm_verification_cta(): void
    {
        $admin = User::factory()->adminLppm()->create(['name' => 'Budi Santoso, S.Kom.']);

        $response = $this->actingAs($admin)->get(route('home'));

        $response->assertOk();
        $response->assertSee('Antrean Verifikasi LPPM');
        $response->assertSee(route('admin.review.index'));
        $response->assertDontSee('Masuk via SSO Kampus');
    }

    public function test_authenticated_dosen_sees_dosen_workspace_cta(): void
    {
        $dosen = User::factory()->dosen()->create(['name' => 'Dr. Hendra Gunawan']);

        $response = $this->actingAs($dosen)->get(route('home'));

        $response->assertOk();
        $response->assertSee('Workspace Naskah');
        $response->assertSee(route('publications.index'));
    }

    public function test_landing_page_search_by_keyword_and_category_filtering(): void
    {
        $categoryA = Category::factory()->create(['name' => 'Kecerdasan Buatan']);
        $categoryB = Category::factory()->create(['name' => 'Jaringan Komputer']);

        $pubA = Publication::factory()->approved()->create([
            'title' => 'Implementasi Deep Learning Pada Citra Medis',
            'category_id' => $categoryA->id,
        ]);

        $pubB = Publication::factory()->approved()->create([
            'title' => 'Optimalisasi Protokol Routing Jaringan',
            'category_id' => $categoryB->id,
        ]);

        // Search by title keyword
        $responseSearch = $this->get(route('home', ['q' => 'Deep Learning']));
        $responseSearch->assertOk();
        $responseSearch->assertSee('Implementasi Deep Learning Pada Citra Medis');
        $responseSearch->assertDontSee('Optimalisasi Protokol Routing Jaringan');

        // Filter by category
        $responseCat = $this->get(route('home', ['category_id' => $categoryB->id]));
        $responseCat->assertOk();
        $responseCat->assertSee('Optimalisasi Protokol Routing Jaringan');
        $responseCat->assertDontSee('Implementasi Deep Learning Pada Citra Medis');
    }
}
