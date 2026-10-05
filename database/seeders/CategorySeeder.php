<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ScopusQuartile;
use App\Enums\SintaTier;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Computer Science & Artificial Intelligence',
                'description' => 'Penelitian di bidang kecerdasan buatan, machine learning, pemrosesan bahasa alami, dan visi komputer.',
                'sinta_tier' => SintaTier::S1,
                'scopus_quartile' => ScopusQuartile::Q1,
                'is_active' => true,
            ],
            [
                'name' => 'Biomedical Engineering & Health Informatics',
                'description' => 'Pengembangan teknologi medis, informatika kesehatan, bioinformatika, dan instrumentasi biomedis.',
                'sinta_tier' => SintaTier::S1,
                'scopus_quartile' => ScopusQuartile::Q1,
                'is_active' => true,
            ],
            [
                'name' => 'Renewable Energy & Power Systems',
                'description' => 'Studi inovasi energi baru terbarukan, smart grid, teknologi baterai, dan efisiensi tenaga listrik.',
                'sinta_tier' => SintaTier::S2,
                'scopus_quartile' => ScopusQuartile::Q2,
                'is_active' => true,
            ],
            [
                'name' => 'Cybersecurity & Cryptography',
                'description' => 'Keamanan jaringan, kriptografi pasca-kuantum, keamanan sistem operasi, dan forensik digital.',
                'sinta_tier' => SintaTier::S2,
                'scopus_quartile' => ScopusQuartile::Q2,
                'is_active' => true,
            ],
            [
                'name' => 'Data Science & Big Data Analytics',
                'description' => 'Analisis data skala besar, visualisasi data interaktif, dan rekayasa data prediktif untuk industri.',
                'sinta_tier' => SintaTier::S2,
                'scopus_quartile' => ScopusQuartile::Q2,
                'is_active' => true,
            ],
            [
                'name' => 'Materials Science & Nanotechnology',
                'description' => 'Karakterisasi nanomaterial, polimer komposit, dan material mutakhir untuk semikonduktor.',
                'sinta_tier' => SintaTier::S1,
                'scopus_quartile' => ScopusQuartile::Q1,
                'is_active' => true,
            ],
            [
                'name' => 'Civil & Environmental Engineering',
                'description' => 'Rekayasa struktur tahan gempa, mitigasi bencana lingkungan, dan manajemen sumber daya air.',
                'sinta_tier' => SintaTier::S3,
                'scopus_quartile' => ScopusQuartile::Q3,
                'is_active' => true,
            ],
            [
                'name' => 'Economic Policy & Applied Finance',
                'description' => 'Analisis makroekonomi terapan, ekonomi sirkular, dan transformasi sistem keuangan digital.',
                'sinta_tier' => SintaTier::S2,
                'scopus_quartile' => ScopusQuartile::Q3,
                'is_active' => true,
            ],
            [
                'name' => 'Educational Technology & Pedagogy',
                'description' => 'Metodologi pembelajaran adaptif, teknologi edukasi berbasis kecerdasan buatan, dan evaluasi kurikulum.',
                'sinta_tier' => SintaTier::S3,
                'scopus_quartile' => ScopusQuartile::Q4,
                'is_active' => true,
            ],
            [
                'name' => 'Public Health & Epidemiology',
                'description' => 'Surveilans penyakit menular, kebijakan kesehatan masyarakat, dan analisis kesehatan global.',
                'sinta_tier' => SintaTier::S2,
                'scopus_quartile' => ScopusQuartile::Q2,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'sinta_tier' => $data['sinta_tier'],
                    'scopus_quartile' => $data['scopus_quartile'],
                    'is_active' => $data['is_active'],
                ]
            );
        }
    }
}
