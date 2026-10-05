<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AuthorRole;
use App\Enums\PublicationStatus;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\DownloadLog;
use App\Models\Publication;
use App\Models\PublicationAuthor;
use App\Models\User;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dosen1 = User::where('email', 'siti.rahmawati@univ.ac.id')->first();
        $dosen2 = User::where('email', 'budi.santoso@univ.ac.id')->first();
        $adminLppm = User::where('email', 'admin.lppm@univ.ac.id')->first();
        $mahasiswa1 = User::where('email', 'ahmad.fauzi@student.univ.ac.id')->first();

        $catAi = Category::where('slug', 'computer-science-artificial-intelligence')->first();
        $catBio = Category::where('slug', 'biomedical-engineering-health-informatics')->first();
        $catEnergy = Category::where('slug', 'renewable-energy-power-systems')->first();
        $catSecurity = Category::where('slug', 'cybersecurity-cryptography')->first();

        // 1. Publication 1: Approved AI Paper (with local dosen + international external author)
        $pub1 = Publication::updateOrCreate(
            ['doi' => '10.1109/TPAMI.2024.3389012'],
            [
                'submitter_id' => $dosen1->id,
                'category_id' => $catAi?->id,
                'title' => 'Self-Supervised Vision Transformers for Real-Time Multimodal Brain MRI Tumor Segmentation',
                'abstract' => 'In this paper, we propose a novel self-supervised vision transformer architecture designed for real-time segmentation of multimodal brain MRI scans. Our method achieves state-of-the-art Dice scores while reducing computational overhead by 37%. We evaluate performance against public benchmarks including BraTS2023.',
                'journal_name' => 'IEEE Transactions on Pattern Analysis and Machine Intelligence',
                'publication_year' => 2024,
                'volume' => '46',
                'issue' => '5',
                'pages' => '1024-1038',
                'status' => PublicationStatus::APPROVED,
                'file_path' => 'publications/sample_vision_transformer_brain_mri.pdf',
                'original_filename' => 'vision_transformer_mri_2024.pdf',
                'file_size_bytes' => 2458900,
                'metadata_api' => [
                    'DOI' => '10.1109/TPAMI.2024.3389012',
                    'title' => 'Self-Supervised Vision Transformers for Real-Time Multimodal Brain MRI Tumor Segmentation',
                    'publisher' => 'IEEE',
                    'ISSN' => '0162-8828',
                    'is_referenced_by_count' => 38,
                ],
                'citation_count' => 38,
                'is_public' => true,
                'published_at' => now()->subMonths(3),
            ]
        );

        // Authors for pub 1
        PublicationAuthor::firstOrCreate(
            ['publication_id' => $pub1->id, 'author_order' => 1],
            [
                'user_id' => $dosen1->id,
                'author_name' => $dosen1->name,
                'author_email' => $dosen1->email,
                'affiliation' => 'Fakultas Ilmu Komputer, Universitas Knowledge Hub',
                'author_role' => AuthorRole::FIRST_CORRESPONDING,
                'is_corresponding' => true,
                'is_external' => false,
            ]
        );

        PublicationAuthor::firstOrCreate(
            ['publication_id' => $pub1->id, 'author_order' => 2],
            [
                'user_id' => null,
                'author_name' => 'Prof. Elena Rostova, Ph.D.',
                'author_email' => 'elena.rostova@cs.ox.ac.uk',
                'affiliation' => 'Department of Computer Science, University of Oxford',
                'author_role' => AuthorRole::CO_AUTHOR,
                'is_corresponding' => false,
                'is_external' => true,
            ]
        );

        // Audit Log for Pub 1
        AuditLog::create([
            'publication_id' => $pub1->id,
            'actor_id' => $adminLppm?->id,
            'status_before' => PublicationStatus::PENDING->value,
            'status_after' => PublicationStatus::APPROVED->value,
            'notes' => 'Dokumen lengkap, DOI terverifikasi di Crossref, dan jurnal terindeks Scopus Q1.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'created_at' => now()->subMonths(3),
        ]);

        // Download Logs for Pub 1
        DownloadLog::create([
            'publication_id' => $pub1->id,
            'user_id' => $mahasiswa1?->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'downloaded_at' => now()->subDays(5),
        ]);

        DownloadLog::create([
            'publication_id' => $pub1->id,
            'user_id' => null, // Guest download
            'ip_address' => '203.0.113.195',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
            'downloaded_at' => now()->subDays(2),
        ]);

        // 2. Publication 2: Renewable Energy Paper (Approved)
        $pub2 = Publication::updateOrCreate(
            ['doi' => '10.1016/j.apenergy.2024.122450'],
            [
                'submitter_id' => $dosen2->id,
                'category_id' => $catEnergy?->id,
                'title' => 'Optimal Decentralized Energy Management for Microgrids with High Penetration of Photovoltaic and Battery Storage',
                'abstract' => 'Decentralized energy management frameworks are critical for stabilizing modern microgrids. This work introduces an adaptive multi-agent consensus algorithm to optimize power dispatch in real-time, achieving 14% lower operational costs and enhanced frequency stability.',
                'journal_name' => 'Applied Energy (Elsevier)',
                'publication_year' => 2024,
                'volume' => '358',
                'issue' => 'Part B',
                'pages' => '122450',
                'status' => PublicationStatus::APPROVED,
                'file_path' => 'publications/sample_microgrid_energy_management.pdf',
                'original_filename' => 'applied_energy_microgrid_2024.pdf',
                'file_size_bytes' => 1894000,
                'metadata_api' => [
                    'DOI' => '10.1016/j.apenergy.2024.122450',
                    'publisher' => 'Elsevier BV',
                ],
                'citation_count' => 19,
                'is_public' => true,
                'published_at' => now()->subMonths(1),
            ]
        );

        PublicationAuthor::firstOrCreate(
            ['publication_id' => $pub2->id, 'author_order' => 1],
            [
                'user_id' => $dosen2->id,
                'author_name' => $dosen2->name,
                'author_email' => $dosen2->email,
                'affiliation' => 'Fakultas Teknik, Universitas Knowledge Hub',
                'author_role' => AuthorRole::FIRST_AUTHOR,
                'is_corresponding' => false,
                'is_external' => false,
            ]
        );

        PublicationAuthor::firstOrCreate(
            ['publication_id' => $pub2->id, 'author_order' => 2],
            [
                'user_id' => $dosen1->id,
                'author_name' => $dosen1->name,
                'author_email' => $dosen1->email,
                'affiliation' => 'Fakultas Ilmu Komputer, Universitas Knowledge Hub',
                'author_role' => AuthorRole::CORRESPONDING,
                'is_corresponding' => true,
                'is_external' => false,
            ]
        );

        AuditLog::create([
            'publication_id' => $pub2->id,
            'actor_id' => $adminLppm?->id,
            'status_before' => PublicationStatus::PENDING->value,
            'status_after' => PublicationStatus::APPROVED->value,
            'notes' => 'Disetujui. Akreditasi Scopus Q1 dan data peer review sesuai.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'created_at' => now()->subMonths(1),
        ]);

        // 3. Publication 3: Pending Review Submission (Dosen 1 in Cybersecurity)
        $pub3 = Publication::updateOrCreate(
            ['doi' => '10.1145/3643834.3660721'],
            [
                'submitter_id' => $dosen1->id,
                'category_id' => $catSecurity?->id,
                'title' => 'Lattice-Based Zero-Knowledge Proofs for Privacy-Preserving Academic Credential Verification',
                'abstract' => 'We present a lightweight lattice-based zero-knowledge proof protocol optimized for distributed digital identities. Our implementation guarantees quantum-resistant security while maintaining millisecond-level verification on resource-constrained devices.',
                'journal_name' => 'ACM Transactions on Privacy and Security (TOPS)',
                'publication_year' => 2025,
                'volume' => '28',
                'issue' => '1',
                'pages' => '1-24',
                'status' => PublicationStatus::PENDING,
                'file_path' => 'publications/sample_lattice_zkp_credential.pdf',
                'original_filename' => 'zkp_credential_verification_2025.pdf',
                'file_size_bytes' => 3120000,
                'metadata_api' => [
                    'DOI' => '10.1145/3643834.3660721',
                    'publisher' => 'ACM',
                ],
                'citation_count' => 0,
                'is_public' => true,
                'published_at' => null,
            ]
        );

        PublicationAuthor::firstOrCreate(
            ['publication_id' => $pub3->id, 'author_order' => 1],
            [
                'user_id' => $dosen1->id,
                'author_name' => $dosen1->name,
                'author_email' => $dosen1->email,
                'affiliation' => 'Fakultas Ilmu Komputer, Universitas Knowledge Hub',
                'author_role' => AuthorRole::FIRST_CORRESPONDING,
                'is_corresponding' => true,
                'is_external' => false,
            ]
        );

        PublicationAuthor::firstOrCreate(
            ['publication_id' => $pub3->id, 'author_order' => 2],
            [
                'user_id' => null,
                'author_name' => 'Dr. Michael Chang',
                'author_email' => 'mchang@cs.stanford.edu',
                'affiliation' => 'Stanford University School of Engineering',
                'author_role' => AuthorRole::CO_AUTHOR,
                'is_corresponding' => false,
                'is_external' => true,
            ]
        );

        // 4. Publication 4: Rejected Submission (Dosen 2)
        $pub4 = Publication::updateOrCreate(
            ['doi' => '10.1016/j.bspc.2023.104882'],
            [
                'submitter_id' => $dosen2->id,
                'category_id' => $catBio?->id,
                'title' => 'Preliminary Analysis on Portable ECG Signal Quality Assessment',
                'abstract' => 'This study investigates preliminary noise reduction filtering on single-lead wearable ECG sensors.',
                'journal_name' => 'Biomedical Signal Processing and Control',
                'publication_year' => 2023,
                'volume' => '84',
                'issue' => '1',
                'pages' => '104882',
                'status' => PublicationStatus::REJECTED,
                'file_path' => 'publications/sample_preliminary_ecg.pdf',
                'original_filename' => 'preliminary_ecg_draft.pdf',
                'file_size_bytes' => 840000,
                'metadata_api' => null,
                'citation_count' => 2,
                'is_public' => false,
                'published_at' => null,
                'rejection_reason' => 'File naskah yang diunggah masih berupa draft preprint dan belum memuat DOI final terindeks.',
            ]
        );

        PublicationAuthor::firstOrCreate(
            ['publication_id' => $pub4->id, 'author_order' => 1],
            [
                'user_id' => $dosen2->id,
                'author_name' => $dosen2->name,
                'author_email' => $dosen2->email,
                'affiliation' => 'Fakultas Teknik, Universitas Knowledge Hub',
                'author_role' => AuthorRole::FIRST_AUTHOR,
                'is_corresponding' => false,
                'is_external' => false,
            ]
        );

        AuditLog::create([
            'publication_id' => $pub4->id,
            'actor_id' => $adminLppm?->id,
            'status_before' => PublicationStatus::PENDING->value,
            'status_after' => PublicationStatus::REJECTED->value,
            'notes' => 'File naskah yang diunggah masih berupa draft preprint dan belum memuat DOI final terindeks.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'created_at' => now()->subDays(10),
        ]);
    }
}
