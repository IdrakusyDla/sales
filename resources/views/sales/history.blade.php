@extends('layout')
@section('content')
    <div class="px-5 py-6">
        <h1 class="text-2xl font-bold mb-2">Riwayat Aktivitas</h1>
        <p class="text-sm text-gray-600 mb-6">Lihat semua absensi dan kunjungan Anda</p>

        {{-- FILTER TANGGAL --}}
        <form method="GET" action="{{ route('sales.history') }}" class="mb-6">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                        class="w-full border border-gray-300 rounded-xl p-3 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                        class="w-full border border-gray-300 rounded-xl p-3 text-sm">
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold text-sm mt-3">
                Filter
            </button>
        </form>

        {{-- LIST RIWAYAT --}}
        <div class="space-y-4">
            @forelse($dailyLogs as $log)
                <a href="{{ route('sales.history.detail', $log->id) }}" class="block">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                    {{-- HEADER TANGGAL --}}
                    <div class="mb-3 pb-3 border-b border-gray-100">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 text-lg">{{ \Carbon\Carbon::parse($log->date)->format('d M Y') }}</h3>
                                <p class="text-xs text-gray-500 mt-1">
                                    @if($log->hasStarted())
                                        Masuk: {{ \Carbon\Carbon::parse($log->start_time)->format('H:i') }}
                                    @endif
                                    @if($log->hasEnded())
                                        | Keluar: {{ \Carbon\Carbon::parse($log->end_time)->format('H:i') }}
                                    @endif
                                </p>
                            </div>
                            <div class="ml-3">
                                @if($log->hasEnded())
                                    <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1.5 rounded-full inline-block">Selesai</span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-1.5 rounded-full inline-block">Berlangsung</span>
                                @endif
                            </div>
                        </div>
                        {{-- INFO DEADLINE DI HEADER --}}
                        @if($log->hasEnded())
                            @php
                                $deadline = \App\Models\Expense::calculateDeadline($log->date);
                                $isDeadlinePassed = \Carbon\Carbon::today()->gt($deadline);
                                $fuelExpense = $log->expenses->where('type', 'fuel')->where('is_auto_calculated', true)->first();
                                $hasIncompleteFuelReceipt = $fuelExpense && !$fuelExpense->photo_receipt;
                                $hasIncompleteExpenses = $log->expenses->whereNull('photo_receipt')->count() > 0;
                            @endphp
                            <div class="mt-2 {{ $isDeadlinePassed ? 'bg-red-50 border border-red-200' : ($hasIncompleteFuelReceipt || $hasIncompleteExpenses ? 'bg-orange-50 border border-orange-200' : 'bg-yellow-50 border border-yellow-200') }} rounded-lg p-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">{{ $isDeadlinePassed ? '⚠️' : '⏰' }}</span>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold {{ $isDeadlinePassed ? 'text-red-700' : ($hasIncompleteFuelReceipt || $hasIncompleteExpenses ? 'text-orange-700' : 'text-yellow-700') }}">
                                            @if($isDeadlinePassed)
                                                Batas Waktu Sudah Lewat
                                            @elseif($hasIncompleteFuelReceipt || $hasIncompleteExpenses)
                                                Batas Melengkapi Berkas • Belum Lengkap
                                            @else
                                                Batas Melengkapi Berkas
                                            @endif
                                        </p>
                                        <p class="text-sm {{ $isDeadlinePassed ? 'text-red-600' : ($hasIncompleteFuelReceipt || $hasIncompleteExpenses ? 'text-orange-600' : 'text-yellow-600') }} font-bold mt-0.5">
                                            Sampai: {{ \Carbon\Carbon::parse($deadline)->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- INFO ODOMETER & KM --}}
                    @if($log->start_odo_value && $log->end_odo_value)
                        <div class="bg-blue-50 rounded-xl p-3 mb-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Odometer Awal:</span>
                                <span class="font-bold">{{ number_format($log->start_odo_value, 2) }} KM</span>
                            </div>
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-gray-600">Odometer Akhir:</span>
                                <span class="font-bold">{{ number_format($log->end_odo_value, 2) }} KM</span>
                            </div>
                            <div class="flex justify-between text-sm mt-2 pt-2 border-t border-blue-200">
                                <span class="text-gray-700 font-bold">Total KM:</span>
                                <span class="font-bold text-blue-600">{{ number_format($log->total_km, 2) }} KM</span>
                            </div>
                        </div>
                    @endif

                    {{-- LIST KUNJUNGAN --}}
                    <div class="space-y-2 mb-3">
                        @foreach($log->visits as $visit)
                            <div class="flex items-start gap-3 p-2 bg-gray-50 rounded-lg">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-sm
                                    {{ $visit->status == 'completed' ? 'bg-green-100' : ($visit->status == 'failed' ? 'bg-red-100' : 'bg-gray-200') }}">
                                    @if($visit->status == 'completed') ✅
                                    @elseif($visit->status == 'failed') ❌
                                    @else ⏳
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-sm">{{ $visit->client_name }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($visit->time)->format('H:i') }}
                                        @if(!$visit->is_planned) <span class="text-orange-600">(Dadakan)</span> @endif
                                    </p>
                                    @if($visit->notes)
                                        <p class="text-xs text-gray-600 mt-1">{{ $visit->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- REIMBURSE --}}
                    @if($log->expenses->count() > 0)
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <p class="text-xs font-bold text-gray-600 mb-2">Reimburse:</p>
                            <div class="space-y-1">
                                @foreach($log->expenses as $expense)
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">
                                            @if($expense->is_auto_calculated)
                                                ⛽ Bahan Bakar (Auto)
                                            @else
                                                {{ ucfirst($expense->type) }}
                                            @endif
                                            @if(!$expense->photo_receipt && $expense->isFuel())
                                                <span class="text-orange-600 text-[10px]">(Belum ada struk)</span>
                                            @endif
                                            @if($expense->status === 'needs_revision_sales')
                                                <span class="bg-orange-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full ml-1">REVISI</span>
                                            @endif
                                        </span>
                                        <span class="font-bold text-green-600">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                                <div class="flex justify-between text-sm mt-2 pt-2 border-t border-gray-200">
                                    <span class="font-bold">Total:</span>
                                    <span class="font-bold text-green-600">Rp {{ number_format($log->expenses->sum('amount'), 0, ',', '.') }}</span>
                                </div>
                            </div>
                            
                            {{-- TOMBOL TAMBAH REIMBURSE (Jika belum lewat deadline) --}}
                            @if(!$log->hasEnded() || \Carbon\Carbon::today()->lte(\App\Models\Expense::calculateDeadline($log->date)))
                                <a href="{{ route('sales.reimburse.form', $log->id) }}"
                                    class="block mt-3 text-center bg-blue-50 text-blue-600 py-2 rounded-lg text-xs font-bold">
                                    + Tambah Pengeluaran Lain
                                </a>
                            @endif
                        </div>
                    @elseif($log->hasEnded())
                        {{-- TOMBOL TAMBAH REIMBURSE (Jika belum lewat deadline) --}}
                        @if(\Carbon\Carbon::today()->lte(\App\Models\Expense::calculateDeadline($log->date)))
                            <a href="{{ route('sales.reimburse.form', $log->id) }}"
                                class="block mt-3 text-center bg-blue-50 text-blue-600 py-2 rounded-lg text-xs font-bold">
                                + Tambah Pengeluaran
                            </a>
                        @endif
                    @endif
                </div>
                </a>
            @empty
                <div class="text-center py-10">
                    <div class="text-4xl mb-2">📋</div>
                    <p class="text-sm text-gray-500">Belum ada riwayat aktivitas</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
