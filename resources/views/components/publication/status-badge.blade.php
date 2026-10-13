@props(['status'])

@php
    $statusValue = $status instanceof \App\Enums\PublicationStatus ? $status->value : (string) $status;
@endphp

@if ($statusValue === \App\Enums\PublicationStatus::PENDING->value)
    <span {{ $attributes->merge(['class' => 'badge badge-status-pending px-2 py-1']) }}>
        <i class="icon-base ti tabler-clock me-1"></i> Menunggu Verifikasi
    </span>
@elseif ($statusValue === \App\Enums\PublicationStatus::APPROVED->value)
    <span {{ $attributes->merge(['class' => 'badge badge-status-approved px-2 py-1']) }}>
        <i class="icon-base ti tabler-circle-check me-1"></i> Disetujui
    </span>
@elseif ($statusValue === \App\Enums\PublicationStatus::REJECTED->value)
    <span {{ $attributes->merge(['class' => 'badge badge-status-rejected px-2 py-1']) }} title="Perlu perbaikan sesuai catatan LPPM">
        <i class="icon-base ti tabler-alert-circle me-1"></i> Perlu Revisi
    </span>
@else
    <span {{ $attributes->merge(['class' => 'badge bg-light text-secondary border px-2 py-1']) }}>
        {{ ucfirst($statusValue) }}
    </span>
@endif
