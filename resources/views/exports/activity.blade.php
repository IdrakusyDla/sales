<table>
    {{-- HEADER LAPORAN --}}
    <tr>
        <td colspan="12" style="background-color: #2563eb; color: white; padding: 15px; font-size: 16px; font-weight: bold; text-align: center;">
            LAPORAN AKTIVITAS ABSENSI SALES
        </td>
    </tr>
    <tr>
        <td colspan="12" style="background-color: #f3f4f6; padding: 10px; font-size: 12px;">
            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate ?? $logs->first()->date ?? now())->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate ?? $logs->last()->date ?? now())->format('d M Y') }}
            @if(isset($userName))
                | <strong>Sales:</strong> {{ $userName }}
            @else
                | <strong>Semua Sales</strong>
            @endif
            | <strong>Total Data:</strong> {{ $logs->count() }} hari
        </td>
    </tr>
    <tr><td colspan="12" style="height: 10px;"></td></tr>

    {{-- HEADER TABEL --}}
    <thead>
        <tr style="background-color: #1f2937; color: white;">
            <th style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 12px;">
                No
            </th>
            <th style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 15px;">
                Tanggal
            </th>
            <th style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 20px;">
                Nama Sales
            </th>
            <th style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 12px;">
                Jam Masuk
            </th>
            <th style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 12px;">
                Jam Keluar
            </th>
            <th style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 15px;">
                Odometer Awal
            </th>
            <th style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 15px;">
                Odometer Akhir
            </th>
            <th style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 12px;">
                Total KM
            </th>
            <th style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 12px; background-color: #e0e7ff;">
                Estimasi (Sistem)
            </th>
            <th style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 25px;">
                Rencana Kunjungan
            </th>
            <th style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 40px;">
                Detail Kunjungan
            </th>
            <th style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; vertical-align: middle; width: 20px;">
                Lokasi (Link)
            </th>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
            $totalKm = 0;
            $totalVisits = 0;
            $completedVisits = 0;
            $failedVisits = 0;
        @endphp
        @forelse ($logs as $log)
            @php
                if ($log->end_odo_value && $log->start_odo_value) {
                    $totalKm += ($log->end_odo_value - $log->start_odo_value);
                }
                $visitCount = count($log->visits ?? []);
                $totalVisits += $visitCount;
                if (isset($log->visits)) {
                    foreach ($log->visits as $v) {
                        if ($v->status == 'completed') $completedVisits++;
                        if ($v->status == 'failed') $failedVisits++;
                    }
                }
                $rowspan = max(1, $visitCount);
            @endphp

            @if($visitCount === 0)
                <tr>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;">{{ $no++ }}</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;">
                        {{ \Carbon\Carbon::parse($log->date)->format('d/m/Y') }}<br>
                        <small style="color: #6b7280;">{{ \Carbon\Carbon::parse($log->date)->locale('id')->isoFormat('dddd') }}</small>
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; vertical-align: top; font-weight: 500;">{{ $log->sales_name }}</td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;">
                        @if($log->start_time)
                            {{ \Carbon\Carbon::parse($log->start_time)->format('H:i') }}
                        @else
                            <span style="color: #ef4444;">-</span>
                        @endif
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;">
                        @if($log->end_time)
                            {{ \Carbon\Carbon::parse($log->end_time)->format('H:i') }}
                        @else
                            <span style="color: #ef4444;">Belum Keluar</span>
                        @endif
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;">
                        @if($log->start_odo_value)
                            {{ number_format($log->start_odo_value, 2) }} KM
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;">
                        @if($log->end_odo_value)
                            {{ number_format($log->end_odo_value, 2) }} KM
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; font-weight: 600;">
                        @if($log->end_odo_value && $log->start_odo_value)
                            <span style="color: #2563eb;">{{ number_format($log->end_odo_value - $log->start_odo_value, 2) }} KM</span>
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; background-color: #e0e7ff;">
                        {{ $log->system_calculated_distance ? number_format($log->system_calculated_distance, 2) : '-' }}
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; vertical-align: top;">
                        @if($log->daily_plan)
                            {{ $log->daily_plan }}
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td style="border: 1px solid #000; padding: 8px; vertical-align: top; color: #9ca3af;">Tidak ada kunjungan</td>
                    <td style="border: 1px solid #000; padding: 8px; vertical-align: top; color: #9ca3af;">-</td>
                </tr>
            @else
                @php $first = true; @endphp
                @foreach ($log->visits as $v)
                    <tr>
                        @if($first)
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;" rowspan="{{ $rowspan }}">{{ $no++ }}</td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;" rowspan="{{ $rowspan }}">
                                {{ \Carbon\Carbon::parse($log->date)->format('d/m/Y') }}<br>
                                <small style="color: #6b7280;">{{ \Carbon\Carbon::parse($log->date)->locale('id')->isoFormat('dddd') }}</small>
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; vertical-align: top; font-weight: 500;" rowspan="{{ $rowspan }}">{{ $log->sales_name }}</td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;" rowspan="{{ $rowspan }}">
                                @if($log->start_time)
                                    {{ \Carbon\Carbon::parse($log->start_time)->format('H:i') }}
                                @else
                                    <span style="color: #ef4444;">-</span>
                                @endif
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;" rowspan="{{ $rowspan }}">
                                @if($log->end_time)
                                    {{ \Carbon\Carbon::parse($log->end_time)->format('H:i') }}
                                @else
                                    <span style="color: #ef4444;">Belum Keluar</span>
                                @endif
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;" rowspan="{{ $rowspan }}">
                                @if($log->start_odo_value)
                                    {{ number_format($log->start_odo_value, 2) }} KM
                                @else
                                    <span style="color: #9ca3af;">-</span>
                                @endif
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top;" rowspan="{{ $rowspan }}">
                                @if($log->end_odo_value)
                                    {{ number_format($log->end_odo_value, 2) }} KM
                                @else
                                    <span style="color: #9ca3af;">-</span>
                                @endif
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; font-weight: 600;" rowspan="{{ $rowspan }}">
                                @if($log->end_odo_value && $log->start_odo_value)
                                    <span style="color: #2563eb;">{{ number_format($log->end_odo_value - $log->start_odo_value, 2) }} KM</span>
                                @else
                                    <span style="color: #9ca3af;">-</span>
                                @endif
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; vertical-align: top; background-color: #e0e7ff;" rowspan="{{ $rowspan }}">
                                {{ $log->system_calculated_distance ? number_format($log->system_calculated_distance, 2) : '-' }}
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; vertical-align: top;" rowspan="{{ $rowspan }}">
                                @if($log->daily_plan)
                                    {{ $log->daily_plan }}
                                @else
                                    <span style="color: #9ca3af;">-</span>
                                @endif
                            </td>
                        @endif

                        <td style="border: 1px solid #000; padding: 8px; vertical-align: top; font-size: 11px;">
                            <strong>{{ $v->client_name }}</strong>
                            @if(isset($v->is_planned) && !$v->is_planned)
                                <span style="background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px; font-size: 9px; margin-left: 4px;">Dadakan</span>
                            @endif
                            <br>
                            <span style="color: #6b7280;">⏰ {{ \Carbon\Carbon::parse($v->time)->format('H:i') }}</span>
                            <span style="margin: 0 4px; color: #d1d5db;">|</span>
                            <span style="color: {{ $v->status == 'completed' ? '#10b981' : ($v->status == 'failed' ? '#ef4444' : '#f59e0b') }}; font-weight: 600;">
                                {{ strtoupper($v->status == 'completed' ? 'Berhasil' : ($v->status == 'failed' ? 'Gagal' : 'Pending')) }}
                            </span>
                            @if($v->status == 'failed' && $v->reason)
                                <br><small style="color: #ef4444;">❌ Alasan: {{ $v->reason }}</small>
                            @elseif($v->status == 'completed' && $v->notes)
                                <br><small style="color: #6b7280;">📝 {{ $v->notes }}</small>
                            @endif
                        </td>
                        <td style="border: 1px solid #000; padding: 8px; vertical-align: top;">
                            @if($v->lat && $v->long)
                                <a href="https://www.google.com/maps?q={{ $v->lat }},{{ $v->long }}"
                                   style="color: #2563eb; text-decoration: underline; font-size: 10px; font-weight: 600; display: inline-block;">
                                    Lihat Lokasi di Google Maps
                                </a>
                            @else
                                <span style="color: #9ca3af;">-</span>
                            @endif
                        </td>
                    </tr>
                    @php $first = false; @endphp
                @endforeach
            @endif
        @empty
            <tr>
                <td colspan="12" style="border: 1px solid #000; padding: 20px; text-align: center; color: #9ca3af;">
                    Tidak ada data untuk periode yang dipilih
                </td>
            </tr>
        @endforelse

        {{-- RINGKASAN --}}
        <tr><td colspan="12" style="height: 15px; background-color: #f9fafb;"></td></tr>
        <tr style="background-color: #f3f4f6;">
            <td colspan="10" style="border: 1px solid #000; padding: 12px; font-weight: bold; text-align: right;">
                RINGKASAN:
            </td>
            <td colspan="2" style="border: 1px solid #000; padding: 12px; font-weight: bold;">
                Total KM: <span style="color: #2563eb;">{{ number_format($totalKm, 2) }} KM</span><br>
                Total Kunjungan: {{ $totalVisits }} (Berhasil: {{ $completedVisits }}, Gagal: {{ $failedVisits }}, Pending: {{ $totalVisits - $completedVisits - $failedVisits }})
            </td>
        </tr>
    </tbody>
</table>
