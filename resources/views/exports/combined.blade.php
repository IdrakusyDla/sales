<table>
    <tr>
        <td colspan="16"
            style="background-color: #2563eb; color: white; padding: 15px; font-size: 16px; font-weight: bold; text-align: center;">
            BIAYA KLAIM OPERATIONAL
        </td>
    </tr>
    <tr>
        <td colspan="16" style="background-color: #f3f4f6; padding: 10px; font-size: 12px;">
            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate ?? now())->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($endDate ?? now())->format('d M Y') }}
            @if(isset($userName))
                | <strong>Sales:</strong> {{ $userName }}
            @else
                | <strong>Semua Sales</strong>
            @endif
            | <strong>Total Hari:</strong> {{ $logs->count() }} hari
        </td>
    </tr>
    <tr>
        <td colspan="16" style="height: 10px;"></td>
    </tr>

    {{-- HEADER TABEL --}}
    <thead>
        <tr style="background-color: #1f2937; color: white;">
            {{-- KOLOM ABSENSI --}}
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 8px;">
                No
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 12px;">
                Tanggal
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 18px;">
                Nama Sales
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 10px;">
                Jam Masuk
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 10px;">
                Jam Keluar
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 12px;">
                Odo Awal
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 20px;">
                Foto Odo Awal
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 12px;">
                Odo Akhir
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 20px;">
                Foto Odo Akhir
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 10px;">
                Total KM
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 12px; background-color: #e0e7ff;">
                Estimasi (Sistem)
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 20px;">
                Detail Kunjungan
            </th>
            {{-- KOLOM REIMBURSE --}}
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 12px;">
                Kategori
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 12px;">
                Nominal
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 15px;">
                Catatan
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 20px;">
                Foto Struk
            </th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
            $totalKm = 0;
            $totalReimburse = 0;
            $totalVisits = 0;

            // Totals per category
            $categoryTotals = [
                'fuel' => 0,
                'parking' => 0,
                'toll' => 0,
                'hotel' => 0,
                'transport' => 0,
                'other' => 0,
            ];
        @endphp
        @forelse ($logs as $log)
            @php
                $visitCount = count($log->visits ?? []);
                $expenseCount = count($log->expenses ?? []);
                $rowsNeeded = max(1, max($visitCount, $expenseCount));

                if ($log->end_odo_value && $log->start_odo_value) {
                    $totalKm += ($log->end_odo_value - $log->start_odo_value);
                }
                $totalVisits += $visitCount;

                // Hitung total reimburse untuk daily log ini
                $logReimburse = 0;
                if (isset($log->expenses)) {
                    foreach ($log->expenses as $exp) {
                        $logReimburse += $exp->amount;

                        // Track by category
                        $type = $exp->type ?? 'other';
                        if ($type === 'gas')
                            $type = 'fuel'; // Normalize
                        if (isset($categoryTotals[$type])) {
                            $categoryTotals[$type] += $exp->amount;
                        } else {
                            $categoryTotals['other'] += $exp->amount;
                        }
                    }
                }
                $totalReimburse += $logReimburse;
            @endphp

            @if($rowsNeeded == 1 && $expenseCount == 0 && $visitCount == 0)
                {{-- Tidak ada visit dan expense --}}
                <tr>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;">{{ $no++ }}</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;">
                        {{ \Carbon\Carbon::parse($log->date)->format('d/m/Y') }}<br>
                        {{ \Carbon\Carbon::parse($log->date)->locale('id')->isoFormat('dddd') }}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; vertical-align: top; font-weight: 500;">
                        {{ $log->sales_name }}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;">
                        @if($log->start_time)
                            {{ \Carbon\Carbon::parse($log->start_time)->format('H:i') }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;">
                        @if($log->end_time)
                            {{ \Carbon\Carbon::parse($log->end_time)->format('H:i') }}
                        @else
                            Belum
                        @endif
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;">
                        @if($log->start_odo_value)
                            {{ number_format($log->start_odo_value, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td
                        style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; min-height: 100px; height: 100px;">
                        @if(!$log->start_odo_photo)
                            <span style="color: #9ca3af; font-size: 10px;">-</span>
                        @endif
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;">
                        @if($log->end_odo_value)
                            {{ number_format($log->end_odo_value, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td
                        style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; min-height: 100px; height: 100px;">
                        @if(!$log->end_odo_photo)
                            <span style="color: #9ca3af; font-size: 10px;">-</span>
                        @endif
                    </td>
                    <td
                        style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; font-weight: 600;">
                        @if($log->end_odo_value && $log->start_odo_value)
                            {{ number_format($log->end_odo_value - $log->start_odo_value, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td
                        style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; background-color: #e0e7ff;">
                        {{ $log->system_calculated_distance ? number_format($log->system_calculated_distance, 2) : '-' }}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; vertical-align: top;">Tidak ada kunjungan</td>
                    <td style="border: 1px solid #000; padding: 8px; vertical-align: top;" colspan="4">Tidak ada reimburse</td>
                </tr>
            @else
                @php $firstRow = true; @endphp
                @for($i = 0; $i < $rowsNeeded; $i++)
                    <tr>
                        @if($firstRow)
                            {{-- Kolom Absensi (rowspan) --}}
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;"
                                rowspan="{{ $rowsNeeded }}">{{ $no++ }}</td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;"
                                rowspan="{{ $rowsNeeded }}">
                                {{ \Carbon\Carbon::parse($log->date)->format('d/m/Y') }}<br>
                                {{ \Carbon\Carbon::parse($log->date)->locale('id')->isoFormat('dddd') }}
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; vertical-align: top; font-weight: 500;"
                                rowspan="{{ $rowsNeeded }}">{{ $log->sales_name }}</td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;"
                                rowspan="{{ $rowsNeeded }}">
                                @if($log->start_time)
                                    {{ \Carbon\Carbon::parse($log->start_time)->format('H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;"
                                rowspan="{{ $rowsNeeded }}">
                                @if($log->end_time)
                                    {{ \Carbon\Carbon::parse($log->end_time)->format('H:i') }}
                                @else
                                    Belum
                                @endif
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;"
                                rowspan="{{ $rowsNeeded }}">
                                @if($log->start_odo_value)
                                    {{ number_format($log->start_odo_value, 2) }}
                                @else
                                    <span style="color: #9ca3af;">-</span>
                                @endif
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; min-height: 100px; height: 100px;"
                                rowspan="{{ $rowsNeeded }}">
                                @if(!$log->start_odo_photo)
                                    <span style="color: #9ca3af; font-size: 10px;">-</span>
                                @endif
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;"
                                rowspan="{{ $rowsNeeded }}">
                                @if($log->end_odo_value)
                                    {{ number_format($log->end_odo_value, 2) }}
                                @else
                                    <span style="color: #9ca3af;">-</span>
                                @endif
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; min-height: 100px; height: 100px;"
                                rowspan="{{ $rowsNeeded }}">
                                @if(!$log->end_odo_photo)
                                    <span style="color: #9ca3af; font-size: 10px;">-</span>
                                @endif
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; font-weight: 600;"
                                rowspan="{{ $rowsNeeded }}">
                                @if($log->end_odo_value && $log->start_odo_value)
                                    <span style="color: #2563eb;">{{ number_format($log->end_odo_value - $log->start_odo_value, 2) }}</span>
                                @else
                                    <span style="color: #9ca3af;">-</span>
                                @endif
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; background-color: #e0e7ff;"
                                rowspan="{{ $rowsNeeded }}">
                                {{ $log->system_calculated_distance ? number_format($log->system_calculated_distance, 2) : '-' }}
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; vertical-align: top; font-size: 11px;"
                                rowspan="{{ $rowsNeeded }}">
                                @if(isset($log->visits) && count($log->visits) > 0)
                                    @foreach($log->visits as $v)
                                        {{ $v->client_name }}
                                        @if(isset($v->is_planned) && !$v->is_planned)
                                            [Dadakan]
                                        @endif
                                        - {{ \Carbon\Carbon::parse($v->time)->format('H:i') }}
                                        -
                                        {{ strtoupper($v->status == 'completed' ? 'Berhasil' : ($v->status == 'failed' ? 'Gagal' : 'Pending')) }}
                                        @if(!$loop->last)
                                            <br>
                                        @endif
                                    @endforeach
                                @else
                                    Tidak ada kunjungan
                                @endif
                            </td>
                        @endif

                        {{-- Kolom Reimburse --}}
                        @if(isset($log->expenses) && isset($log->expenses[$i]))
                            @php
                                $exp = $log->expenses[$i];
                                $labelType = match ($exp->type) {
                                    'fuel', 'gas' => 'Bahan Bakar',
                                    'toll' => 'E-Toll',
                                    'transport' => 'Transport',
                                    'parking' => 'Parkir',
                                    'hotel' => 'Hotel',
                                    'other' => 'Lainnya',
                                    default => ucfirst($exp->type ?? 'Lainnya')
                                };
                            @endphp
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;">{{ $labelType }}
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: right; vertical-align: top; font-weight: 600;">
                                Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                            <td style="border: 1px solid #000; padding: 8px; vertical-align: top; font-size: 10px;">
                                {{ $exp->note ?? '-' }}
                            </td>
                            <td
                                style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; min-height: 100px; height: 100px;">
                                @if(!$exp->photo_receipt)
                                    <span style="color: #9ca3af; font-size: 10px;">Belum ada struk</span>
                                @endif
                            </td>
                        @else
                            <td style="border: 1px solid #000; padding: 8px; vertical-align: top;" colspan="4">
                                @if($i == 0 && $expenseCount == 0)
                                    Tidak ada reimburse
                                @endif
                            </td>
                        @endif


                    </tr>
                    @php $firstRow = false; @endphp
                @endfor
            @endif
        @empty
            <tr>
                <td colspan="16" style="border: 1px solid #000; padding: 20px; text-align: center; color: #9ca3af;">
                    Tidak ada data untuk periode yang dipilih
                </td>
            </tr>
        @endforelse

        {{-- RINGKASAN --}}
        <tr>
            <td colspan="16" style="height: 15px; background-color: #f9fafb;"></td>
        </tr>
        <tr style="background-color: #f3f4f6;">
            <td colspan="10" style="border: 1px solid #000; padding: 12px; font-weight: bold; text-align: right;">
                RINGKASAN:
            </td>
            <td colspan="6" style="border: 1px solid #000; padding: 12px; font-weight: bold;">
                Total KM: {{ number_format($totalKm, 2) }} KM<br>
                Total Kunjungan: {{ $totalVisits }}
            </td>
        </tr>
        
        {{-- RINCIAN REIMBURSE PER KATEGORI --}}
        <tr>
            <td colspan="16" style="height: 10px;"></td>
        </tr>
        <tr style="background-color: #dbeafe;">
            <td colspan="16" style="border: 1px solid #000; padding: 10px; font-weight: bold; text-align: center;">
                RINCIAN REIMBURSE PER KATEGORI
            </td>
        </tr>
        <tr style="background-color: #eff6ff;">
            <td colspan="8" style="border: 1px solid #000; padding: 8px; text-align: right;">Bahan Bakar (BBM):</td>
            <td colspan="8" style="border: 1px solid #000; padding: 8px; font-weight: 600;">Rp {{ number_format($categoryTotals['fuel'], 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #f0fdf4;">
            <td colspan="8" style="border: 1px solid #000; padding: 8px; text-align: right;">Parkir:</td>
            <td colspan="8" style="border: 1px solid #000; padding: 8px; font-weight: 600;">Rp {{ number_format($categoryTotals['parking'], 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #fefce8;">
            <td colspan="8" style="border: 1px solid #000; padding: 8px; text-align: right;">E-Toll:</td>
            <td colspan="8" style="border: 1px solid #000; padding: 8px; font-weight: 600;">Rp {{ number_format($categoryTotals['toll'], 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #fdf4ff;">
            <td colspan="8" style="border: 1px solid #000; padding: 8px; text-align: right;">Hotel:</td>
            <td colspan="8" style="border: 1px solid #000; padding: 8px; font-weight: 600;">Rp {{ number_format($categoryTotals['hotel'], 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #fff7ed;">
            <td colspan="8" style="border: 1px solid #000; padding: 8px; text-align: right;">Transport:</td>
            <td colspan="8" style="border: 1px solid #000; padding: 8px; font-weight: 600;">Rp {{ number_format($categoryTotals['transport'], 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #f5f5f5;">
            <td colspan="8" style="border: 1px solid #000; padding: 8px; text-align: right;">Lainnya:</td>
            <td colspan="8" style="border: 1px solid #000; padding: 8px; font-weight: 600;">Rp {{ number_format($categoryTotals['other'], 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #22c55e; color: white;">
            <td colspan="8" style="border: 1px solid #000; padding: 12px; text-align: right; font-weight: bold; font-size: 14px;">GRAND TOTAL REIMBURSE:</td>
            <td colspan="8" style="border: 1px solid #000; padding: 12px; font-weight: bold; font-size: 14px;">Rp {{ number_format($totalReimburse, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>