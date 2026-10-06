<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publication;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Publication\StorePublicationRequest;
use App\Http\Requests\Publication\UpdatePublicationRequest;
use App\Models\Category;
use App\Models\Publication;
use App\Services\Publication\PublicationService;
use App\Services\Publication\PublicationStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PublicationController extends Controller
{
    public function __construct(
        protected PublicationService $publicationService,
        protected PublicationStorageService $storageService
    ) {}

    /**
     * Display a listing of the user's publications.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = $user->isAdminLPPM()
            ? Publication::query()->with(['submitter', 'category', 'authors.user'])
            : $user->publications()->with(['category', 'authors.user']);

        // Search filter
        if ($search = $request->query('q')) {
            $query->search((string) $search);
        }

        // Status filter
        if ($status = $request->query('status')) {
            if (in_array($status, [PublicationStatus::PENDING->value, PublicationStatus::APPROVED->value, PublicationStatus::REJECTED->value], true)) {
                $query->where('status', $status);
            }
        }

        // Category filter
        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', (int) $categoryId);
        }

        // Year filter
        if ($year = $request->query('year')) {
            $query->where('publication_year', (int) $year);
        }

        $publications = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        // Calculate status counters
        $counts = [
            'all' => $user->isAdminLPPM() ? Publication::count() : $user->publications()->count(),
            'pending' => $user->isAdminLPPM() ? Publication::pending()->count() : $user->publications()->pending()->count(),
            'approved' => $user->isAdminLPPM() ? Publication::approved()->count() : $user->publications()->approved()->count(),
            'rejected' => $user->isAdminLPPM() ? Publication::rejected()->count() : $user->publications()->rejected()->count(),
        ];

        $categories = Category::active()->orderBy('name')->get();

        return view('publications.index', [
            'publications' => $publications,
            'counts' => $counts,
            'categories' => $categories,
            'currentStatus' => $request->query('status', 'all'),
            'currentSearch' => $request->query('q', ''),
        ]);
    }

    /**
     * Show the form for creating a new publication.
     */
    public function create(): View
    {
        $categories = Category::active()->orderBy('name')->get();

        return view('publications.create', [
            'categories' => $categories,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Store a newly created publication in storage.
     */
    public function store(StorePublicationRequest $request): RedirectResponse
    {
        $publication = $this->publicationService->createPublication(
            Auth::user(),
            $request->validated(),
            $request->file('file')
        );

        return redirect()->route('publications.index')->with(
            'success',
            "Pengajuan naskah [{$publication->title}] dengan kode registrasi [{$publication->registration_code}] berhasil disimpan dan menunggu verifikasi LPPM."
        );
    }

    /**
     * Display the specified publication details.
     */
    public function show(Publication $publication): View
    {
        Gate::authorize('view', $publication);

        $publication->load(['submitter', 'category', 'authors.user', 'auditLogs.actor']);

        return view('publications.show', [
            'publication' => $publication,
        ]);
    }

    /**
     * Show the form for editing the specified publication.
     */
    public function edit(Publication $publication): View|RedirectResponse
    {
        Gate::authorize('update', $publication);

        if ($publication->isApproved() && ! Auth::user()->isAdminLPPM()) {
            return redirect()->route('publications.index')->with(
                'error',
                'Publikasi yang telah disetujui LPPM tidak dapat diubah kembali.'
            );
        }

        $categories = Category::active()->orderBy('name')->get();
        $publication->load(['category', 'authors.user']);

        return view('publications.edit', [
            'publication' => $publication,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified publication in storage.
     */
    public function update(UpdatePublicationRequest $request, Publication $publication): RedirectResponse
    {
        Gate::authorize('update', $publication);

        $this->publicationService->updatePublication(
            $publication,
            $request->validated(),
            $request->file('file')
        );

        return redirect()->route('publications.index')->with(
            'success',
            "Publikasi [{$publication->title}] berhasil diperbarui."
        );
    }

    /**
     * Remove the specified publication from storage.
     */
    public function destroy(Publication $publication): RedirectResponse
    {
        Gate::authorize('delete', $publication);

        $this->storageService->deleteFile($publication->file_path);
        $publication->delete();

        return redirect()->route('publications.index')->with(
            'info',
            'Publikasi berhasil dihapus dari repositori.'
        );
    }
}
