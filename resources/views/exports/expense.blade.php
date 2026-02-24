<table>
    {{-- HEADER LAPORAN --}}
    <tr>
        <td colspan="11"
            style="background-color: #2563eb; color: white; padding: 15px; font-size: 16px; font-weight: bold; text-align: center;">
            LAPORAN REIMBURSE SALES
        </td>
    </tr>
    <tr>
        <td colspan="11" style="background-color: #f3f4f6; padding: 10px; font-size: 12px;">
            <strong>Periode:</strong>
            {{ \Carbon\Carbon::parse($startDate ?? $expenses->first()->date ?? now())->format('d M Y') }} -
            {{ \Carbon\Carbon::parse($endDate ?? $expenses->last()->date ?? now())->format('d M Y') }}
            @if(isset($userName))
                | <strong>Sales:</strong> {{ $userName }}
            @else
                | <strong>Semua Sales</strong>
            @endif
            | <strong>Total Data:</strong> {{ $expenses->count() }} baris
        </td>
    </tr>
    <tr>
        <td colspan="11" style="height: 10px;"></td>
    </tr>

    {{-- HEADER TABEL --}}
    <thead>
        <tr style="background-color: #1f2937; color: white;">
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 8px;">
                No
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 12px;">
                Tanggal
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 20px;">
                Nama Sales
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 15px;">
                Kategori
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 15px;">
                Nominal
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 18px;">
                Catatan
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 12px;">
                KM (Fuel)
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 12px; background-color: #e0e7ff;">
                Estimasi (Sistem)
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 18px;">
                Deadline
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 25px;">
                Foto Struk
            </th>
            <th
                style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 25px;">
                Foto KM
            </th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
            $totalAmount = 0;
            $totalPerType = [];
        @endphp
        @forelse ($expenses as $ex)
            @php
                $totalAmount += $ex->amount;
                $typeKey = $ex->type ?? 'other';
                $totalPerType[$typeKey] = ($totalPerType[$typeKey] ?? 0) + $ex->amount;

                $labelType = match ($ex->type) {
                    'fuel', 'gas' => 'Bahan Bakar',
                    'toll' => 'E-Toll',
                    'transport' => 'Transport',
                    'parking' => 'Parkir',
                    'hotel' => 'Hotel',
                    'other' => 'Lainnya',
                    default => ucfirst($ex->type ?? 'Lainnya')
                };

                $deadline = $ex->deadline_date ?? null;
                $isOverdue = $deadline ? \Carbon\Carbon::today()->gt(\Carbon\Carbon::parse($deadline)) : false;
                $isFuel = ($ex->type == 'fuel' || $ex->type == 'gas') && ($ex->is_auto_calculated ?? false);
            @endphp
            <tr>
                <td style="vertical-align: top; border: 1px solid black; text-align: center; padding: 8px;">{{ $no++ }}</td>
                <td style="vertical-align: top; border: 1px solid black; text-align: center; padding: 8px;">
                    {{ \Carbon\Carbon::parse($ex->date)->format('d/m/Y') }}<br>
                    <small
                        style="color: #6b7280;">{{ \Carbon\Carbon::parse($ex->date)->locale('id')->isoFormat('dddd') }}</small>
                </td>
                <td style="vertical-align: top; border: 1px solid black; padding: 8px; font-weight: 500;">
                    {{ $ex->sales_name }}</td>
                <td style="vertical-align: top; border: 1px solid black; padding: 8px; text-align: center;">{{ $labelType }}
                </td>
                <td
                    style="vertical-align: top; border: 1px solid black; padding: 8px; text-align: right; font-weight: 600;">
                    Rp {{ number_format($ex->amount, 0, ',', '.') }}</td>
                <td style="vertical-align: top; border: 1px solid black; padding: 8px;">{{ $ex->note ?? '-' }}</td>
                <td style="vertical-align: top; border: 1px solid black; padding: 8px; text-align: center;">
                    @if($isFuel && $ex->km_total)
                        {{ number_format($ex->km_total, 2) }} KM
                    @else
                        <span style="color: #9ca3af;">-</span>
                    @endif
                </td>
                <td
                    style="vertical-align: top; border: 1px solid black; padding: 8px; text-align: center; background-color: #e0e7ff;">
                    {{ $ex->system_calculated_distance ? number_format($ex->system_calculated_distance, 2) : '-' }}
                </td>
                <td style="vertical-align: top; border: 1px solid black; padding: 8px; text-align: center;">
                    @if($deadline)
                        <span style="color: {{ $isOverdue ? '#ef4444' : '#16a34a' }}; font-weight: 600;">
                            {{ \Carbon\Carbon::parse($deadline)->format('d/m/Y') }}
                        </span>
                    @else
                        <span style="color: #9ca3af;">-</span>
                    @endif
                </td>
                {{-- Foto Struk (akan diembed via WithDrawings) --}}
                <td
                    style="vertical-align: top; border: 1px solid black; padding: 8px; text-align: center; min-height: 100px; height: 100px;">
                    @if($ex->photo_receipt)
                        {{-- Gambar akan diembed via WithDrawings trait --}}
                        <span style="color: #16a34a; font-size: 10px;">Struk tersedia</span>
                    @else
                        <span style="color: #ef4444; font-size: 10px;">Belum ada struk</span>
                    @endif
                </td>
                {{-- Foto KM (akan diembed via WithDrawings) --}}
                <td
                    style="vertical-align: top; border: 1px solid black; padding: 8px; text-align: center; min-height: 100px; height: 100px;">
                    @if(isset($ex->photo_km) && $ex->photo_km)
                        {{-- Gambar akan diembed via WithDrawings trait --}}
                        <span style="color: #16a34a; font-size: 10px;">KM tersedia</span>
                    @else
                        <span style="color: #ef4444; font-size: 10px;">Belum ada KM</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="11" style="border: 1px solid #000; padding: 20px; text-align: center; color: #9ca3af;">
                    Tidak ada data untuk periode yang dipilih
                </td>
            </tr>
        @endforelse

        {{-- RINGKASAN --}}
        <tr>
            <td colspan="11" style="height: 15px; background-color: #f9fafb;"></td>
        </tr>
        <tr style="background-color: #f3f4f6;">
            <td colspan="6" style="border: 1px solid #000; padding: 12px; font-weight: bold; text-align: right;">
                RINGKASAN:
            </td>
            <td colspan="5" style="border: 1px solid #000; padding: 12px; font-weight: bold;">
                Total Reimburse: <span style="color: #2563eb;">Rp
                    {{ number_format($totalAmount, 0, ',', '.') }}</span><br>
                @if(count($totalPerType) > 0)
                    @foreach($totalPerType as $k => $v)
                        @php
                            $label = match ($k) {
                                'fuel', 'gas' => 'Bahan Bakar',
                                'toll' => 'E-Toll',
                                'transport' => 'Transport',
                                'parking' => 'Parkir',
                                'hotel' => 'Hotel',
                                'other' => 'Lainnya',
                                default => ucfirst($k ?? 'Lainnya')
                            };
                        @endphp
                        {{ $label }}: Rp {{ number_format($v, 0, ',', '.') }}@if(!$loop->last)<br>@endif
                    @endforeach
                @endif
            </td>
        </tr>
    </tbody>
</table>