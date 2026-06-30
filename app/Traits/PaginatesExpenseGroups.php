<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Mengelompokkan reimburse berdasarkan daily_log_id (1 putaran absen = 1 kartu),
 * dengan paginasi tingkat grup (bukan per item).
 *
 * Dipakai oleh ReimburseApprovalController (SPV/HRD) & FinanceController.
 */
trait PaginatesExpenseGroups
{
    /**
     * Bangun query ter-filter dari base query + request (filter tanggal/user/tipe).
     *
     * @param Builder $base   Query yang sudah berisi constraint role & status (TANPA with()).
     * @param Request $request
     * @return Builder
     */
    protected function applyExpenseFilters(Builder $base, Request $request): Builder
    {
        $query = clone $base;

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return $query;
    }

    /**
     * Paginasi berdasarkan grup daily_log_id.
     *
     * @param Builder $base    Query base (constraint role & status, TANPA with()).
     * @param Request $request
     * @param array   $with    Relasi yang di-eager-load saat mengambil item.
     * @param int     $perPage Jumlah grup per halaman.
     * @return array{0: Collection<int, Collection<int, mixed>>, 1: LengthAwarePaginator}
     *     - [0] Kumpulan grup (tiap grup = Collection of Expense) urut sesuai halaman.
     *     - [1] Paginator berisi daftar daily_log_id per halaman.
     */
    protected function getGroupedExpensePaginator(Builder $base, Request $request, array $with = [], int $perPage = 10): array
    {
        $filtered = $this->applyExpenseFilters($base, $request);

        // Ambil daftar daily_log_id (group key) urut tanggal terbaru.
        $groupIds = (clone $filtered)
            ->select('daily_log_id')
            ->whereNotNull('daily_log_id')
            ->groupBy('daily_log_id')
            ->orderByRaw('MAX(date) DESC, MAX(created_at) DESC')
            ->pluck('daily_log_id');

        $page = LengthAwarePaginator::resolveCurrentPage();
        $pageIds = $groupIds->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $pageIds,
            $groupIds->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
                'query' => request()->query(),
            ]
        );

        if ($pageIds->isEmpty()) {
            return [collect(), $paginator];
        }

        // Load semua expense milik grup di halaman ini (re-apply filter & constraint).
        $items = $this->applyExpenseFilters($base, $request)
            ->with($with)
            ->whereIn('daily_log_id', $pageIds->all())
            ->orderBy('type')
            ->orderBy('id')
            ->get();

        // Kelompokkan sambil mempertahankan urutan groupIds halaman ini.
        $grouped = $pageIds->mapWithKeys(fn ($id) => [$id => collect()]);
        foreach ($items as $expense) {
            if (isset($grouped[$expense->daily_log_id])) {
                $grouped[$expense->daily_log_id]->push($expense);
            }
        }

        // Buang grup kosong (mis. ter-filter tipe) & reset index.
        $groups = $grouped->filter(fn (Collection $items) => $items->isNotEmpty())->values();

        return [$groups, $paginator];
    }
}
