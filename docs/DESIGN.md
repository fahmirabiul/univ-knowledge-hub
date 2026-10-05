# DESIGN GUIDELINES & SPECIFICATION (DESIGN.md)

## 1. Product Identity & Direction
- **Product Name:** Knowledge Hub (University Academic Publication & Research Repository)
- **Role in Ecosystem:** Operational Consumer Application & Research Archive for university sivitas akademika (Dosen, Mahasiswa, Admin LPPM).
- **Design Philosophy:** Authoritative, scholarly, clean, and intuitive academic repository.
- **Base UI Framework:** Vuexy (Bootstrap 5 HTML/CSS/JS Assets).
- **Antislop Dials:**
  - **ENERGY 2 (Balanced):** Academic and professional tone with crisp visual hierarchy for research papers.
  - **RHYTHM 2 (Structured):** Organized multi-column publication grids and clear review queue workflows.
  - **MOTION 1 (Subtle):** Micro-interactions on DOI fetching states, button clicks, and modal transitions; no distracting animations.

---

## 2. Color Palette & Design Tokens

All colors must meet WCAG AA contrast standards (minimum 4.5:1 for normal text against its background).

| Token Name | Hex Code | Purpose / Application | WCAG Contrast |
| :--- | :--- | :--- | :--- |
| **Primary (Royal Indigo)** | `#7367F0` | Main action buttons, active navigation, brand elements | 4.6:1 on White |
| **Primary Hover / Focus** | `#5E50EE` | Hover and keyboard focus indicator state | 5.8:1 on White |
| **Accent (Scholarly Teal)** | `#00CFE8` | DOI tags, research category pills, download highlights | 4.5:1 on Dark / White |
| **Background (Body)** | `#F8F7FA` | Global page canvas background | Neutral light |
| **Surface (Card / Topbar)**| `#FFFFFF` | Publication cards, review drawers, forms, navbar | Neutral surface |
| **Heading Text** | `#4B465C` | H1-H6, journal titles, section headers | 9.2:1 (AAA Pass) |
| **Body Text** | `#6F6B7D` | Abstract summaries, metadata, form labels | 4.8:1 (AA Pass) |
| **Border & Divider** | `#DBDADE` | Card borders, table dividers, input borders | Structural |
| **Status: Pending** | `#FF9F43` | Badge for publications awaiting LPPM verification | Solid & subtle pill |
| **Status: Approved** | `#28C76F` | Badge for verified & published journals | Solid & subtle pill |
| **Status: Rejected** | `#EA5455` | Badge for rejected publications with review notes | Solid & subtle pill |

---

## 3. Typography & Iconography

* **Font Family:** `Public Sans, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`
* **Font Weights:** `400` (Regular), `500` (Medium), `600` (Semi-bold for Titles), `700` (Bold for Brand & Headings).
* **Icons:** Tabler Icons (provided via Vuexy `public/assets/fonts/iconify-icons.css` using class `icon-base ti tabler-[icon-name]`).

---

## 4. Component & Screen Specifications

### A. Landing Page & Public Repository Catalog (`/` atau `/catalog`)
* **Top Navigation Bar:**
  * Left: Brand logo & "Knowledge Hub - Repositori Karya Ilmiah".
  * Right (Guest): Tombol **"Masuk via SSO Kampus"** (`btn btn-primary`) dengan ikon kunci `tabler-key`.
  * Right (Authenticated): Demographic pill (Nama, Peran, Fakultas) + Avatar dropdown with Logout.
* **Hero Search Bar:**
  * Prominent search input with auto-filter by Judul, Nama Dosen/Penulis, DOI, or Program Studi.
* **Publication Cards Grid:**
  * Article Card Elements:
    * Category / Faculty badge.
    * Publication Title (with link to Detail Modal / Abstract View).
    * Author list with role indicator (Penulis Utama / Korespondensi).
    * Journal Name & DOI chip.
    * Action CTA: Tombol **"Unduh Dokumen (PDF)"** yang memicu *Temporary Signed URL* (15 menit).
* **Empty State:** State informatif saat pencarian tidak menemukan artikel ilmiah terkait.

### B. Dosen Workspace: Pengajuan Karya Ilmiah (`/publications` & `/publications/create`)
* **Interactive DOI Metadata Fetcher:**
  * Input field untuk DOI (contoh: `10.1145/3372278.3390678`).
  * Tombol **"Tarik Metadata Otomatis"** dengan *loading spinner*.
  * Begitu ditarik via Service API (Crossref / Mock), judul, nama jurnal, dan metadata otomatis terisi di form.
* **Secure PDF Uploader:**
  * Area drag-and-drop / file browser khusus PDF (Maksimal 10MB).
  * Keterangan keamanan bahwa file disimpan di *private storage* (`storage/app/private/publications`).
* **Author Collaboration Repeater:**
  * Menambahkan dosen pendamping / korespondensi dengan peran penulis yang jelas.
* **My Publications Tracker:**
  * Tabel status pengajuan publikasi milik dosen (`pending`, `approved`, `rejected`).
  * Jika berstatus `rejected`, tombol info menampilkan catatan penolakan dari Admin LPPM.

### C. Admin LPPM Verification Queue (`/admin/review`)
* **Review Queue Table:**
  * Daftar publikasi berstatus `pending`.
  * Tombol preview metadata & dokumen PDF.
* **Approval & Rejection Modal:**
  * Modal konfirmasi dengan tombol **"Setujui Publikasi"** (Hijau) dan **"Tolak Pengajuan"** (Merah).
  * Input textarea wajib untuk **Alasan Penolakan / Catatan Perbaikan** jika ditolak.
* **Audit Trail Timeline Drawer (`/publications/{id}/audit`):**
  * Visualisasi riwayat perubahan status: Siapa yang mengubah, kapan, status sebelum/sesudah, dan catatan verifikasi.

---

## 5. Anti-Slop Strict Quality Gate (R-01 to R-38 Compliance)

1. **No Em Dashes (`—`):** Never use em dash in copy. Use commas, colons, or parentheses.
2. **No Dead Links / Fake Buttons:** Every button must trigger a working action, signed download, or modal.
3. **No Fake AI Claims:** Focus purely on real scholarly workflow, DOI fetching, and audit trail integrity.
4. **Mobile Responsiveness:** Tables must scroll horizontally or collapse on mobile; tap targets >= 44px.
5. **Accessible Forms:** All inputs have explicit `<label>` tags and high contrast states.
