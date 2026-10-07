<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApprovePublicationRequest;
use App\Http\Requests\Admin\RejectPublicationRequest;
use App\Models\Category;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PublicationReviewController extends Controller
{
    /**
     * Display the review queue of publications for LPPM administrators.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', PublicationStatus::PENDING->value);

        $query = Publication::query()->with(['submitter', 'category', 'authors.user']);

        // Apply status filter
        if ($statusFilter !== 'all') {
            if (in_array($statusFilter, [
                PublicationStatus::PENDING->value,
                PublicationStatus::APPROVED->value,
                PublicationStatus::REJECTED->value,
            ], true)) {
                $query->where('status', $statusFilter);
            }
        }

        // Search term
        if ($search = $request->query('q')) {
            $query->search((string) $search);
        }

        // Category filter
        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', (int) $categoryId);
        }

        $publications = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $counts = [
            'all' => Publication::count(),
            'pending' => Publication::pending()->count(),
            'approved' => Publication::approved()->count(),
            'rejected' => Publication::rejected()->count(),
        ];

        $categories = Category::active()->orderBy('name')->get();

        return view('admin.review.index', [
            'publications' => $publications,
            'counts' => $counts,
            'categories' => $categories,
            'currentStatus' => $statusFilter,
            'currentSearch' => (string) $request->query('q', ''),
        ]);
    }

    /**
     * Approve a publication submission.
     */
    public function approve(ApprovePublicationRequest $request, Publication $publication): RedirectResponse
    {
        $publication->approve(
            Auth::user(),
            $request->validated('notes')
        );

        return redirect()->back()->with(
            'success',
            "Naskah [{$publication->title}] berhasil disetujui LPPM dan status berubah menjadi terpublikasi."
        );
    }

    /**
     * Reject a publication submission with mandatory reason.
     */
    public function reject(RejectPublicationRequest $request, Publication $publication): RedirectResponse
    {
        $publication->reject(
            Auth::user(),
            (string) $request->validated('reason')
        );

        return redirect()->back()->with(
            'success',
            "Naskah [{$publication->title}] telah ditolak dengan catatan evaluasi."
        );
    }

    /**
     * Get JSON audit trail logs for the offcanvas drawer.
     */
    public function audit(Publication $publication): JsonResponse
    {
        $logs = $publication->auditLogs()
            ->with('actor')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'actor_name' => $log->actor?->name ?? 'Sistem Otomatis',
                    'actor_role' => $log->actor?->role?->label() ?? 'Sistem',
                    'status_before' => $log->status_before,
                    'status_after' => $log->status_after,
                    'notes' => $log->notes,
                    'ip_address' => $log->ip_address,
                    'created_at_human' => $log->created_at?->diffForHumans() ?? '',
                    'created_at_formatted' => $log->created_at?->format('d M Y, H:i') ?? '',
                ];
            });

        return response()->json([
            'publication_id' => $publication->id,
            'registration_code' => $publication->registration_code,
            'title' => $publication->title,
            'current_status' => $publication->status->value,
            'logs' => $logs,
        ]);
    }
}
