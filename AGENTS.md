# AGENTS.md — Panduan untuk AI/Developer di project ini

Panduan singkat agar konsisten dan tidak mengulang kesalahan yang pernah terjadi.

## Tech Stack
- **Backend:** Laravel (Blade, Eloquent). PHP 8+.
- **Frontend:** Tailwind CSS v3 (via PostCSS, BUKAN v4 vite plugin) + Alpine.js + Vite.
- **DB:** MySQL.
- **Auth/RBAC:** role `sales`, `supervisor`, `hrd`, `finance`, `it`. Middleware `role:...` di `routes/web.php`.

## ⚠️ ATURAN KRITIS — Tailwind CSS

**Setiap kali menambah/mengubah class Tailwind BARU di file `.blade.php`, WAJIB rebuild assets:**

```bash
npm run build
```

**Kenapa?** Project ini memakai **CSS yang di-build statis** (`public/build/assets/app-[hash].css`). Class Tailwind baru yang belum pernah dipakai sebelumnya **tidak akan ada** di CSS hasil build yang lama → elemen jadi rusak/narrow/tanpa style di production, walau `npm run dev` tampak aman. Bug nyata: `md:col-span-8`/`md:col-span-4` hilang → layout desktop collapse.

**Cek apakah class sudah ada di build (sebelum push):**
```bash
# Ganti nama file CSS sesuai hash terbaru di public/build/assets/
$css = Get-Content "public/build/assets/app-*.css" -Raw
([regex]::Matches($css, 'nama-class-anda')).Count   # harus > 0
```

**Workflow yang aman:**
- Saat develop: jalankan `npm run dev` (auto-scan & rebuild CSS saat file berubah).
- Sebelum commit/deploy: **selalu** `npm run build` untuk regenerate asset produksi.

## Perintah Umum
```bash
npm run dev        # Vite dev server + hot reload (Tailwind auto-rescan)
npm run build      # Build asset produksi (WAJIB setelah edit class Tailwind)
php artisan serve  # Jalankan aplikasi
php artisan migrate              # Jalankan migration baru
php artisan migrate:rollback     # Undo migration terakhir
php artisan view:clear           # Bersihkan cache Blade (saat debug)
php artisan view:cache           # Pre-compile semua view (cek syntax error)
php artisan route:list           # Cek route terdaftar
php -l app/Http/Controllers/X.php # Cek syntax PHP file tunggal
```

## Arsitektur & Konvensi

### Layout & Responsif
- Layout utama: `resources/views/layout.blade.php`. Punya `<main>` (`max-w-[480px] md:max-w-none`) + bottom nav mobile + sidebar desktop + CSS global `.desktop-main` (di `<style>`).
- **Satu markup responsif** dengan prefix `md:` — JANGAN duplikasi blok `md:hidden` + `hidden md:block` untuk hal yang sama (rawan inkonsisten). Sudah dirapikan untuk halaman absen & dashboard.
- Komponen Blade anonymous ada di `resources/views/components/`. Contoh: `<x-camera-capture>` (kamera selfie/foto reusable berbasis Alpine).

### Alur Absensi (sales & supervisor)
2-model kunjungan "check-in/check-out per toko":
- **Absen Masuk** → buat `DailyLog` + `Visit` pending.
- **Check-in** (foto sampai): `Visits.status` → `in_progress`, isi `arrival_*`.
- **Check-out** (foto pulang + hasil): `status` → `completed`/`failed`, isi `departure_*`.
- **Absen Keluar**: tutup `DailyLog`, blokir jika masih ada `pending`/`in_progress`.

**Backward compatibility tabel `visits`:** kolom legacy `time`, `lat`, `long`, `photo_path` **disinkronkan** saat check-out (time=departure_time, photo_path=departure_photo) agar view lama (export, feed HRD/SPV, dst) tetap jalan tanpa ubahan.

Route foto visit: `files/visit/{id}/{kind?}` dengan `kind` = `arrival` | `departure` (default fallback departure/legacy).

### Foto & File
- Foto disimpan via `SalesController::saveBase64Image()` ke `storage/app/public/{folder}/...`.
- Disajikan lewat `FileController` (bukan asset langsung) untuk otorisasi per role.

### Kompatibilitas
- `MobileController` adalah **legacy/dead code** (tidak ter-route). Tetap referensi skema lama — jangan diandalkan, tapi jangan rusak strukturnya saat refactor.

## Verifikasi Sebelum Selesai
- [ ] `php artisan view:cache` berhasil (tidak ada Blade syntax error).
- [ ] Jika menambah class Tailwind baru → `npm run build` sudah dijalankan.
- [ ] Jika menyentuh DB → migration idempoten (`Schema::hasColumn` guard) & sudah test `migrate`.
- [ ] Render view dengan user nyata via tinker/script untuk tangkap runtime error (`$errors` di-share middleware, tidak ada di CLI — perlu `view()->share('errors', ...)` saat test di luar HTTP).
