# Desktop Layout Sales Design Document

**Date:** 2025-06-19
**Project:** Web Absensi Sales
**Role:** Sales - Desktop Layout Optimization

---

## Problem Statement

Tampilan desktop pada role sales (absen masuk, absen keluar, dan laporan kunjungan) tidak konsisten dengan role supervisor. Layout saat ini hanya "mobile view yang dilebarkan" tanpa memanfaatkan ruang desktop secara optimal.

## Current State

### Role Supervisor (Dashboard)
- ✅ Proper desktop layout dengan grid 12 kolom (8:4 ratio)
- ✅ Background `bg-slate-50/50` dengan padding luas
- ✅ Card styling: `rounded-[2rem] shadow-sm border border-gray-100`
- ✅ Terpisah untuk mobile dan desktop view

### Role Sales
- ❌ **Absen Masuk**: Single column, hanya responsive padding
- ❌ **Absen Keluar**: Single column, hanya responsive padding
- ✅ **Laporan Kunjungan**: SUDAH proper (ada desktop_history.blade.php terpisah)

## Solution Design

### 1. Absen Masuk (absen_masuk.blade.php)

#### Desktop Layout (≥768px)
- **Background:** `bg-slate-50/50`, padding `px-8 py-8`
- **Grid System:** `grid grid-cols-12 gap-6`
- **Split Layout:** 75:25 ratio (Kiri 9/12, Kanan 3/12)

#### Kolom Kiri (9/12) - Main Form
Card dengan `rounded-[2rem] shadow-sm border border-gray-100 p-8`:
- Header: "Absen Masuk" dengan deskripsi
- Foto Selfie (camera preview dengan buttons)
- Foto Odometer (camera preview dengan buttons)
- Input Nilai Odometer (KM)
- Rencana Kunjungan (dynamic form)
- Tombol Submit & Reset

#### Kolom Kanan (3/12) - Information Cards
Stack cards dengan `rounded-[2rem] shadow-sm border border-gray-100 p-6`:

**Card 1: Tips Absen**
```blade
<div class="bg-blue-50 rounded-xl p-4 mb-4">
    <h4 class="font-bold text-sm text-blue-800 mb-2">💡 Tips Foto</h4>
    <ul class="text-xs text-blue-700 space-y-1">
        <li>• Pastikan wajah terlihat jelas</li>
        <li>• Hindari cahaya yang terlalu terang/gelap</li>
        <li>• Background harus rapi dan tidak mengganggu</li>
    </ul>
</div>
```

**Card 2: Info Deadline** (jika ada pending reimburse)
```blade
<div class="bg-orange-50 rounded-xl p-4 mb-4">
    <h4 class="font-bold text-sm text-orange-800 mb-2">⏰ Pengingat</h4>
    <p class="text-xs text-orange-700">Lengkapi reimburse sebelum deadline H+2</p>
</div>
```

**Card 3: Statistik Hari Ini**
```blade
<div class="bg-green-50 rounded-xl p-4">
    <h4 class="font-bold text-sm text-green-800 mb-2">📊 Hari Ini</h4>
    <div class="space-y-2">
        <div class="flex justify-between text-xs">
            <span class="text-green-600">Rencana Kunjungan:</span>
            <span class="font-bold text-green-700">{{ $plannedVisitsCount }} Toko</span>
        </div>
    </div>
</div>
```

---

### 2. Absen Keluar (absen_keluar.blade.php)

#### Desktop Layout (≥768px)
- Struktur sama seperti absen masuk
- Grid 12 kolom dengan split 75:25

#### Kolom Kiri (9/12) - Main Form
Card dengan styling sama:
- Header: "Absen Keluar"
- Jenis Absen Keluar (radio buttons: Pulang ke Rumah, Dari Toko Terakhir, Lokasi Lain)
- Catatan Lokasi (textarea, muncul jika pilih "Lokasi Lain")
- Foto Selfie
- Foto Odometer Akhir
- Input Nilai Odometer Akhir (dengan validasi ≥ odometer awal)
- Tombol Submit & Cancel

#### Kolom Kanan (3/12) - Information Cards

**Card 1: Tips Absen Keluar**
```blade
<div class="bg-blue-50 rounded-xl p-4 mb-4">
    <h4 class="font-bold text-sm text-blue-800 mb-2">💡 Tips</h4>
    <ul class="text-xs text-blue-700 space-y-1">
        <li>• Pastikan odometer terbaca dengan jelas</li>
        <li>• Foto odometer harus fokus dan tidak blur</li>
        <li>• Pastikan lokasi sesuai dengan jenis absen keluar</li>
    </ul>
</div>
```

**Card 2: Info Odometer**
```blade
<div class="bg-gray-50 rounded-xl p-4 mb-4">
    <h4 class="font-bold text-sm text-gray-800 mb-2">🚗 Odometer Hari Ini</h4>
    <div class="space-y-2">
        <div class="flex justify-between text-xs">
            <span class="text-gray-600">Odometer Awal:</span>
            <span class="font-bold text-gray-700">{{ number_format($todayLog->start_odo_value, 2) }} KM</span>
        </div>
        <div class="flex justify-between text-xs">
            <span class="text-gray-600">Total Perjalanan:</span>
            <span class="font-bold text-blue-600" id="total-km-preview">-- KM</span>
        </div>
    </div>
</div>
```

**Card 3: Summary Hari Ini**
```blade
<div class="bg-green-50 rounded-xl p-4">
    <h4 class="font-bold text-sm text-green-800 mb-2">✅ Summary</h4>
    <div class="space-y-2">
        <div class="flex justify-between text-xs">
            <span class="text-green-600">Kunjungan Selesai:</span>
            <span class="font-bold text-green-700">{{ $completedVisits }} / {{ $totalVisits }}</span>
        </div>
    </div>
</div>
```

---

### 3. Laporan Kunjungan (history.blade.php)

#### Status: ✅ NO CHANGES NEEDED
File ini SUDAH memiliki layout desktop yang proper:
- Mobile view di `history.blade.php`
- Desktop view di `desktop_history.blade.php` (di-include)
- Grid 3 kolom untuk card riwayat
- Filter tanggal dalam row horizontal

#### Minor Adjustment (Optional)
Update card styling untuk konsistensi:
- Ubah `rounded-2xl` → `rounded-[2rem]` (sama dengan supervisor)

---

## Styling Guidelines

### Consistent with Supervisor Dashboard

#### Background & Layout
- **Page Background:** `bg-slate-50/50` (light gray with 50% opacity)
- **Page Padding:** `px-8 py-8` untuk desktop
- **Mobile Padding:** `px-5 py-6` (tetap dirawat)
- **Grid Gap:** `gap-6` antar columns/cards

#### Card Styling
- **Main Cards:** `bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8`
- **Info Cards:** `bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6`
- **Hover Effect:** `hover:shadow-md transition`

#### Typography
- **Page Headers:** `text-xl font-extrabold text-gray-800 tracking-tight mb-2`
- **Subheaders:** `text-sm text-gray-500 font-medium`
- **Card Headers:** `text-base font-bold text-gray-800 mb-4`
- **Labels:** `text-sm font-bold text-gray-700 mb-2`
- **Form Labels:** `text-sm font-bold text-gray-700 mb-2 uppercase tracking-wider`

#### Form Elements
- **Input Fields:** `w-full border-gray-200 bg-gray-50 text-gray-800 rounded-xl p-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow`
- **Radio Cards:** `flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-500`
- **Buttons:**
  - Primary: `bg-blue-600 hover:bg-blue-700 text-white px-10 py-4 rounded-xl font-bold transition-colors shadow-md shadow-blue-600/20 active:scale-95`
  - Secondary: `bg-gray-200 text-gray-700 py-4 rounded-xl font-bold`

#### Information Card Colors
- **Tips (Blue):** `bg-blue-50` with `text-blue-800/700`
- **Warning (Orange):** `bg-orange-50` with `text-orange-800/700`
- **Success (Green):** `bg-green-50` with `text-green-800/700`
- **Info (Gray):** `bg-gray-50` with `text-gray-800/700`

---

## Implementation Approach

### File Changes

#### 1. absen_masuk.blade.php
- Add desktop view wrapper: `hidden md:block` (new section)
- Keep mobile view: `md:hidden` (existing section unchanged)
- Split form into 2 columns (9:3 ratio)
- Add 3 information cards on right column

#### 2. absen_keluar.blade.php
- Same approach as absen_masuk
- Add desktop view wrapper
- Split form + info cards

#### 3. history.blade.php + desktop_history.blade.php
- **NO CHANGES** - already proper
- Optional: Update `rounded-2xl` → `rounded-[2rem]` for consistency

### Responsive Behavior

#### Breakpoint: 768px (md:)
- **Below 768px:** Single column, mobile view
- **768px and above:** Desktop grid layout

#### Conditional Rendering
```blade
{{-- MOBILE VIEW (< 768px) --}}
<div class="md:hidden px-5 py-6">
    <!-- Existing mobile code unchanged -->
</div>

{{-- DESKTOP VIEW (≥ 768px) --}}
<div class="hidden md:block px-8 py-8 h-full bg-slate-50/50">
    <div class="grid grid-cols-12 gap-6">
        <!-- Left Column (9/12) -->
        <!-- Right Column (3/12) -->
    </div>
</div>
```

---

## Data Requirements

### Variables Needed for Information Cards

#### Absen Masuk
- `$plannedVisitsCount` - Jumlah rencana kunjungan hari ini
- `$pendingReimburseCount` - Jumlah reimburse yang pending (untuk deadline reminder)

#### Absen Keluar
- `$todayLog->start_odo_value` - Odometer awal (sudah ada)
- `$completedVisits` - Kunjungan yang sudah selesai
- `$totalVisits` - Total rencana kunjungan hari ini

**Note:** Jika variable belum ada, perlu ditambahkan di controller.

---

## Testing Checklist

### Visual Testing
- [ ] Desktop view properly shows 2-column layout (75:25)
- [ ] Information cards visible on right column
- [ ] Card styling consistent with supervisor
- [ ] Mobile view unchanged (single column)
- [ ] Background colors match design

### Functional Testing
- [ ] Form submission works on both mobile and desktop
- [ ] Camera functionality intact
- [ ] GPS location capture works
- [ ] Form validation works
- [ ] Dynamic fields (destinations, notes) functional

### Responsive Testing
- [ ] Test at 767px (should show mobile)
- [ ] Test at 768px (should show desktop)
- [ ] Test at 1024px, 1440px (desktop scaling)

### Browser Testing
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge

---

## Success Criteria

1. ✅ Desktop view shows proper 2-column layout (75:25 ratio)
2. ✅ Information cards displayed on right column
3. ✅ Styling consistent with supervisor dashboard
4. ✅ Mobile view unchanged (backward compatible)
5. ✅ Responsive breakpoint works correctly at 768px
6. ✅ All form functionality works on both mobile and desktop

---

## Future Enhancements (Out of Scope)

- Add loading states for camera initialization
- Add drag-and-drop for reordering destinations
- Add image preview after photo capture
- Add offline indicator for GPS/camera issues
- Add progress indicator for multi-step form

---

## References

- **Supervisor Dashboard:** `resources/views/supervisor/dashboard.blade.php`
- **Sales History (Mobile):** `resources/views/sales/history.blade.php`
- **Sales History (Desktop):** `resources/views/sales/desktop_history.blade.php`
- **Current Absen Masuk:** `resources/views/sales/absen_masuk.blade.php`
- **Current Absen Keluar:** `resources/views/sales/absen_keluar.blade.php`

---

**Document Version:** 1.0
**Last Updated:** 2025-06-19
**Status:** Ready for Implementation
