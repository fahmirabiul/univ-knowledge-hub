@extends('layouts.app')

@section('title', 'Unggah Publikasi Baru')

@section('content')
<div class="container-fluid p-0">
    <!-- Breadcrumb & Title -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Unggah Publikasi Ilmiah Baru</h4>
            <p class="text-muted small mb-0">Ajukan naskah riset, artikel jurnal, atau prosiding konferensi ke repositori universitas.</p>
        </div>
        <a href="{{ route('publications.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
            <i class="icon-base ti tabler-arrow-left"></i>
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    <!-- Live DOI Autofill Card (Time Saver Feature for Dosen) -->
    <div class="card border-primary border-opacity-25 shadow-sm mb-4" style="background: linear-gradient(135deg, rgba(115, 103, 240, 0.04) 0%, rgba(0, 207, 232, 0.04) 100%);">
        <div class="card-body p-4">
            <div class="d-flex align-items-start gap-3">
                <div class="avatar-initials bg-primary text-white rounded-3 p-3 flex-shrink-0">
                    <i class="icon-base ti tabler-sparkles fs-3"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="fw-bold text-dark mb-1">Pengisian Cepat Berbasis Nomor DOI</h5>
                    <p class="text-muted small mb-3">
                        Punya nomor Digital Object Identifier (DOI)? Masukkan nomor DOI di bawah untuk menarik judul, nama jurnal penerbit, tahun publikasi, dan daftar penulis secara otomatis dari repositori Crossref internasional.
                    </p>

                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-8 col-lg-7">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="icon-base ti tabler-link"></i></span>
                                <input type="text" id="doi-fetch-input" class="form-control border-start-0" placeholder="Contoh: 10.1145/3372278.3390678 atau 10.1038/nature12373" aria-label="Nomor DOI publikasi">
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-3">
                            <button type="button" id="btn-fetch-doi" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                <span id="fetch-spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <i id="fetch-icon" class="icon-base ti tabler-cloud-download"></i>
                                <span id="fetch-text">Tarik Metadata</span>
                            </button>
                        </div>
                    </div>

                    <!-- Live DOI Fetch Status Feedback -->
                    <div id="doi-status-feedback" class="mt-3 d-none"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Submission Form -->
    <form action="{{ route('publications.store') }}" method="POST" enctype="multipart/form-data" id="form-publication">
        @csrf

        <div class="row g-4">
            <!-- Left Column: Publication Metadata -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="icon-base ti tabler-file-text text-primary"></i>
                            <span>Informasi Bibliografi Naskah</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- DOI & ISBN/ISSN Row -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="field-doi" class="form-label">Nomor DOI <span class="text-muted small">(Opsional)</span></label>
                                <input type="text" id="field-doi" name="doi" value="{{ old('doi') }}" class="form-control @error('doi') is-invalid @enderror" placeholder="10.xxxx/xxxxx">
                                @error('doi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="field-isbn" class="form-label">ISBN / ISSN <span class="text-muted small">(Opsional)</span></label>
                                <input type="text" id="field-isbn" name="isbn_issn" value="{{ old('isbn_issn') }}" class="form-control @error('isbn_issn') is-invalid @enderror" placeholder="Contoh: 0162-8828">
                                @error('isbn_issn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Title -->
                        <div class="mb-3">
                            <label for="field-title" class="form-label">Judul Lengkap Publikasi Ilmiah <span class="text-danger">*</span></label>
                            <input type="text" id="field-title" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" placeholder="Ketik judul artikel ilmiah atau buku..." required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category & Journal Name -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="field-category" class="form-label">Kategori Bidang Riset</label>
                                <select id="field-category" name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">Pilih Kategori Klasifikasi</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ (string)old('category_id') === (string)$category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="field-journal" class="form-label">Nama Jurnal / Konferensi Penerbit <span class="text-danger">*</span></label>
                                <input type="text" id="field-journal" name="journal_name" value="{{ old('journal_name') }}" class="form-control @error('journal_name') is-invalid @enderror" placeholder="Nama jurnal atau proceeding..." required>
                                @error('journal_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Year, Volume, Issue, Pages -->
                        <div class="row g-3 mb-3">
                            <div class="col-6 col-md-3">
                                <label for="field-year" class="form-label">Tahun Terbit <span class="text-danger">*</span></label>
                                <input type="number" id="field-year" name="publication_year" value="{{ old('publication_year', date('Y')) }}" class="form-control @error('publication_year') is-invalid @enderror" min="1900" max="{{ date('Y') + 1 }}" required>
                                @error('publication_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="field-volume" class="form-label">Volume</label>
                                <input type="text" id="field-volume" name="volume" value="{{ old('volume') }}" class="form-control @error('volume') is-invalid @enderror" placeholder="Vol. 12">
                                @error('volume')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="field-issue" class="form-label">Nomor / Issue</label>
                                <input type="text" id="field-issue" name="issue" value="{{ old('issue') }}" class="form-control @error('issue') is-invalid @enderror" placeholder="No. 3">
                                @error('issue')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="field-pages" class="form-label">Halaman</label>
                                <input type="text" id="field-pages" name="pages" value="{{ old('pages') }}" class="form-control @error('pages') is-invalid @enderror" placeholder="10-25">
                                @error('pages')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Abstract -->
                        <div class="mb-0">
                            <label for="field-abstract" class="form-label">Abstrak Publikasi <span class="text-danger">*</span></label>
                            <textarea id="field-abstract" name="abstract" rows="6" class="form-control @error('abstract') is-invalid @enderror" placeholder="Tuliskan ringkasan / abstrak dari karya ilmiah..." required>{{ old('abstract') }}</textarea>
                            @error('abstract')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Author Collaboration Repeater Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="icon-base ti tabler-users text-primary"></i>
                            <span>Daftar Penulis & Kolaborator</span>
                        </h5>
                        <button type="button" id="btn-add-author" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1">
                            <i class="icon-base ti tabler-user-plus"></i>
                            <span>Tambah Penulis</span>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="text-muted small mb-3">
                            Urutkan nama penulis sesuai susunan resmi pada naskah. Tentukan penulis utama dan korespondensi.
                        </div>

                        <div id="author-repeater-container" class="d-flex flex-column gap-3 w-100">
                            <!-- Default Author 1: Logged-in User -->
                            <div class="author-row p-3 border rounded bg-light position-relative w-100" data-index="0">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-primary-subtle text-primary fw-semibold author-order-label">Penulis #1 (Pengusul)</span>
                                </div>
                                <div class="row g-2">
                                    <input type="hidden" name="authors[0][user_id]" value="{{ Auth::id() }}">
                                    <input type="hidden" name="authors[0][author_order]" value="1" class="input-author-order">
                                    <input type="hidden" name="authors[0][is_external]" value="0">

                                    <div class="col-12 col-md-5">
                                        <label class="form-label small mb-1">Nama Lengkap Penulis</label>
                                        <input type="text" name="authors[0][author_name]" value="{{ old('authors.0.author_name', Auth::user()->name) }}" class="form-control form-control-sm input-author-name" required>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="form-label small mb-1">Afiliasi / Institusi</label>
                                        <input type="text" name="authors[0][affiliation]" value="{{ old('authors.0.affiliation', config('app.institution_name', 'Universitas')) }}" class="form-control form-control-sm input-author-affiliation" placeholder="Nama universitas / institusi...">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label small mb-1">Peran Penulis</label>
                                        <select name="authors[0][author_role]" class="form-select form-select-sm select-author-role">
                                            <option value="pertama_korespondensi" selected>Penulis Pertama & Korespondensi</option>
                                            <option value="penulis_pertama">Penulis Pertama</option>
                                            <option value="korespondensi">Penulis Korespondensi</option>
                                            <option value="pendamping">Penulis Pendamping</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Document File Upload & Submission Summary -->
            <div class="col-12 col-lg-4">
                <!-- PDF Upload Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="icon-base ti tabler-file-upload text-primary"></i>
                            <span>File Dokumen Naskah</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="field-file" class="form-label">Pilih Dokumen PDF <span class="text-danger">*</span></label>
                            <input type="file" id="field-file" name="file" accept="application/pdf" class="form-control @error('file') is-invalid @enderror" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text mt-2" style="font-size: 0.78rem;">
                                <i class="icon-base ti tabler-shield-check text-success me-1"></i>
                                Format dokumen harus PDF. Maksimal 10MB. Dokumen disimpan secara terenkripsi di private storage repositori.
                            </div>
                        </div>

                        <!-- Selected File Preview Badge -->
                        <div id="file-preview-card" class="p-3 bg-light rounded border d-none">
                            <div class="d-flex align-items-center gap-2">
                                <i class="icon-base ti tabler-file-type-pdf text-danger fs-2"></i>
                                <div class="overflow-hidden flex-grow-1">
                                    <div id="file-name-preview" class="fw-semibold text-dark text-truncate small">filename.pdf</div>
                                    <div id="file-size-preview" class="text-muted" style="font-size: 0.72rem;">0 KB</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submission Actions Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-2 mb-3">
                            <i class="icon-base ti tabler-info-circle text-primary fs-4 mt-1"></i>
                            <div class="text-muted small">
                                Naskah yang diajukan akan berstatus <strong>Menunggu Verifikasi (Pending)</strong> hingga ditinjau oleh Admin LPPM.
                            </div>
                        </div>

                        <button type="submit" id="btn-submit-publication" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                            <i class="icon-base ti tabler-send"></i>
                            <span class="fw-semibold">Ajukan Naskah ke LPPM</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Live DOI Autofill Handler
    const btnFetchDoi = document.getElementById('btn-fetch-doi');
    const inputDoiFetch = document.getElementById('doi-fetch-input');
    const spinner = document.getElementById('fetch-spinner');
    const fetchIcon = document.getElementById('fetch-icon');
    const fetchText = document.getElementById('fetch-text');
    const feedbackBox = document.getElementById('doi-status-feedback');

    btnFetchDoi.addEventListener('click', async function() {
        const rawDoi = inputDoiFetch.value.trim();
        if (!rawDoi) {
            showFeedback('danger', 'Silakan masukkan nomor DOI terlebih dahulu.');
            inputDoiFetch.focus();
            return;
        }

        // Set Loading State
        spinner.classList.remove('d-none');
        fetchIcon.classList.add('d-none');
        fetchText.textContent = 'Memuat...';
        btnFetchDoi.disabled = true;
        feedbackBox.classList.add('d-none');

        try {
            const url = `{{ route('publications.doi.fetch') }}?doi=${encodeURIComponent(rawDoi)}`;
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (response.ok && result.status === 'success' && result.data) {
                const data = result.data;

                // Autofill form inputs
                document.getElementById('field-doi').value = data.doi || rawDoi;
                if (data.title) document.getElementById('field-title').value = data.title;
                if (data.journal_name) document.getElementById('field-journal').value = data.journal_name;
                if (data.publication_year) document.getElementById('field-year').value = data.publication_year;
                if (data.volume) document.getElementById('field-volume').value = data.volume;
                if (data.issue) document.getElementById('field-issue').value = data.issue;
                if (data.pages) document.getElementById('field-pages').value = data.pages;
                if (data.isbn_issn) document.getElementById('field-isbn').value = data.isbn_issn;
                if (data.abstract) document.getElementById('field-abstract').value = data.abstract;

                // Autofill Authors if available
                if (Array.isArray(data.authors) && data.authors.length > 0) {
                    populateFetchedAuthors(data.authors);
                }

                showFeedback('success', `Metadata berhasil ditarik: "${data.title.substring(0, 60)}..."`);
            } else {
                showFeedback('warning', result.message || 'Metadata untuk nomor DOI ini tidak ditemukan di basis data Crossref.');
            }
        } catch (error) {
            showFeedback('danger', 'Gagal menghubungi server. Periksa koneksi internet Anda atau coba isi form secara manual.');
        } finally {
            spinner.classList.add('d-none');
            fetchIcon.classList.remove('d-none');
            fetchText.textContent = 'Tarik Metadata';
            btnFetchDoi.disabled = false;
        }
    });

    function showFeedback(type, message) {
        feedbackBox.className = `alert alert-${type} d-flex align-items-center gap-2 mb-0 py-2`;
        const iconClass = type === 'success' ? 'tabler-circle-check' : (type === 'warning' ? 'tabler-alert-triangle' : 'tabler-alert-circle');
        feedbackBox.innerHTML = `
            <i class="icon-base ti ${iconClass} fs-5"></i>
            <div class="small flex-grow-1">${message}</div>
        `;
        feedbackBox.classList.remove('d-none');
    }

    // 2. Author Repeater Logic
    const repeaterContainer = document.getElementById('author-repeater-container');
    const btnAddAuthor = document.getElementById('btn-add-author');
    let authorCount = 1;

    btnAddAuthor.addEventListener('click', function() {
        addAuthorRow('', '', 'pendamping', true);
    });

    function addAuthorRow(name = '', affiliation = '', role = 'pendamping', isExternal = true) {
        const index = authorCount++;
        const row = document.createElement('div');
        row.className = 'author-row p-3 border rounded bg-white position-relative w-100';
        row.dataset.index = index;

        row.innerHTML = `
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-secondary-subtle text-secondary fw-semibold author-order-label">Penulis #${index + 1}</span>
                <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-remove-author" title="Hapus Penulis" aria-label="Hapus Penulis">
                    <i class="icon-base ti tabler-trash"></i>
                </button>
            </div>
            <div class="row g-2">
                <input type="hidden" name="authors[${index}][user_id]" value="">
                <input type="hidden" name="authors[${index}][author_order]" value="${index + 1}" class="input-author-order">
                <input type="hidden" name="authors[${index}][is_external]" value="${isExternal ? 1 : 0}">

                <div class="col-12 col-md-5">
                    <label class="form-label small mb-1">Nama Lengkap Penulis <span class="text-danger">*</span></label>
                    <input type="text" name="authors[${index}][author_name]" value="${name}" class="form-control form-control-sm input-author-name" placeholder="Nama lengkap penulis..." required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small mb-1">Afiliasi / Institusi</label>
                    <input type="text" name="authors[${index}][affiliation]" value="${affiliation}" class="form-control form-control-sm input-author-affiliation" placeholder="Institusi atau universitas...">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small mb-1">Peran Penulis</label>
                    <select name="authors[${index}][author_role]" class="form-select form-select-sm select-author-role">
                        <option value="pendamping" ${role === 'pendamping' ? 'selected' : ''}>Penulis Pendamping</option>
                        <option value="korespondensi" ${role === 'korespondensi' ? 'selected' : ''}>Penulis Korespondensi</option>
                        <option value="penulis_pertama" ${role === 'penulis_pertama' ? 'selected' : ''}>Penulis Pertama</option>
                    </select>
                </div>
            </div>
        `;

        repeaterContainer.appendChild(row);

        row.querySelector('.btn-remove-author').addEventListener('click', function() {
            row.remove();
            reindexAuthors();
        });
    }

    function reindexAuthors() {
        const rows = repeaterContainer.querySelectorAll('.author-row');
        rows.forEach((row, idx) => {
            const orderLabel = row.querySelector('.author-order-label');
            if (idx === 0) {
                orderLabel.textContent = 'Penulis #1 (Pengusul)';
            } else {
                orderLabel.textContent = `Penulis #${idx + 1}`;
            }
            const orderInput = row.querySelector('.input-author-order');
            if (orderInput) orderInput.value = idx + 1;
        });
    }

    function populateFetchedAuthors(fetchedAuthors) {
        // Keep author 1 as submitter, add subsequent authors from fetched data
        if (fetchedAuthors.length > 0) {
            // Update row 1 name if empty
            const firstRowName = repeaterContainer.querySelector('.author-row[data-index="0"] .input-author-name');
            if (firstRowName && !firstRowName.value.trim()) {
                firstRowName.value = fetchedAuthors[0].author_name || '';
            }

            // Append additional authors
            for (let i = 1; i < fetchedAuthors.length; i++) {
                addAuthorRow(
                    fetchedAuthors[i].author_name || '',
                    fetchedAuthors[i].affiliation || '',
                    'pendamping',
                    true
                );
            }
        }
    }

    // 3. PDF File Preview & Size Check (Max 10MB)
    const fileInput = document.getElementById('field-file');
    const previewCard = document.getElementById('file-preview-card');
    const fileNamePreview = document.getElementById('file-name-preview');
    const fileSizePreview = document.getElementById('file-size-preview');

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.type !== 'application/pdf') {
                alert('File yang dipilih harus berformat PDF (.pdf).');
                fileInput.value = '';
                previewCard.classList.add('d-none');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                alert('Ukuran file dokumen melebihi batas maksimum 10MB.');
                fileInput.value = '';
                previewCard.classList.add('d-none');
                return;
            }

            fileNamePreview.textContent = file.name;
            fileSizePreview.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            previewCard.classList.remove('d-none');
        } else {
            previewCard.classList.add('d-none');
        }
    });
});
</script>
@endpush
