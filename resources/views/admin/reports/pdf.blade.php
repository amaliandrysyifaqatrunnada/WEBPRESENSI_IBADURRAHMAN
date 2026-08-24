<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Kehadiran Guru PKBM Ibadurrahman</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 10px;
            color: #333333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #2E7D32;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            color: #2E7D32;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 10px;
            color: #666666;
            margin-bottom: 3px;
        }
        .filter-info {
            margin-bottom: 15px;
            font-size: 9px;
            background-color: #f9f9f9;
            padding: 8px;
            border: 1px solid #e3e3e3;
            border-radius: 4px;
        }
        .summary-container {
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 6px 12px;
            border: 1px solid #dddddd;
            background-color: #fcfcfc;
            font-size: 9px;
        }
        .summary-title {
            font-weight: bold;
            color: #555555;
        }
        .summary-value {
            font-weight: bold;
            font-size: 11px;
            color: #2E7D32;
            text-align: right;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #2E7D32;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 8px 6px;
            border: 1px solid #2E7D32;
            text-transform: uppercase;
            font-size: 9px;
        }
        .data-table td {
            padding: 6px 6px;
            border: 1px solid #dddddd;
            font-size: 9px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }
        .badge-hadir {
            background-color: #E8F5E9;
            color: #2E7D32;
        }
        .badge-terlambat {
            background-color: #FFF8E1;
            color: #F57F17;
        }
        .badge-izin {
            background-color: #E8EAF6;
            color: #3F51B5;
        }
        .badge-sakit {
            background-color: #F3E5F5;
            color: #9C27B0;
        }
        .badge-alpa {
            background-color: #FFEBEE;
            color: #D32F2F;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9px;
            color: #777777;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $unit ? $unit->name : 'PKBM Ibadurrahman' }}</h1>
        <p>{{ $unit ? $unit->address : 'Alamat belum diatur' }}</p>
        <p>
            Paket: {{ $unit ? $unit->package_type : '-' }} | 
            GPS Koordinat: {{ $unit ? ($unit->latitude . ', ' . $unit->longitude) : '-' }} | 
            Radius GPS: {{ $unit ? $unit->gps_radius : '-' }} meter
        </p>
        <p style="margin-top: 5px; font-weight: bold; color: #2E7D32;">Laporan Presensi Tenaga Pendidik</p>
    </div>

    <!-- Filter Meta Information -->
    <div class="filter-info">
        <strong>Filter Laporan:</strong> Periode {{ ucfirst($filters['type']) }} 
        @if ($filters['type'] === 'harian')
            ({{ $filters['date'] }})
        @elseif ($filters['type'] === 'mingguan')
            ({{ $filters['start_date'] }} s.d {{ $filters['end_date'] }})
        @elseif ($filters['type'] === 'bulanan')
            (Bulan {{ $filters['month'] }}, Tahun {{ $filters['year'] }})
        @elseif ($filters['type'] === 'tahunan')
            (Tahun {{ $filters['year'] }})
        @endif
        | Status: {{ $filters['status'] }} | Guru: {{ $filters['teacher_id'] === 'All Teachers' ? 'Semua Guru' : 'ID ' . $filters['teacher_id'] }}
    </div>

    <!-- Statistics Summary -->
    <div class="summary-container">
        <table class="summary-table">
            <tr>
                <td class="summary-title">Total Hadir</td>
                <td class="summary-value" style="color: #2E7D32;">{{ $stats['present'] }}</td>
                <td class="summary-title">Total Terlambat</td>
                <td class="summary-value" style="color: #F57F17;">{{ $stats['late'] }}</td>
                <td class="summary-title">Total Reward</td>
                <td class="summary-value" style="color: #2E7D32;">{{ $stats['reward'] ?? 0 }}</td>
                <td class="summary-title">Pulang Lebih Awal</td>
                <td class="summary-value" style="color: #F57F17;">{{ $stats['pulang_awal'] ?? 0 }} kali</td>
            </tr>
        </table>
    </div>

    <!-- Detailed Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 10%">Tanggal</th>
                <th style="width: 20%">Nama Guru</th>
                <th style="width: 10%">Masuk</th>
                <th style="width: 12%">Status Masuk</th>
                <th style="width: 10%">Reward</th>
                <th style="width: 10%">Pulang</th>
                <th style="width: 13%">Status Pulang</th>
                <th style="width: 10%">Metode</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $att)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($att->date)->format('d/m/Y') }}</td>
                    <td>
                        <strong>{{ $att->teacher->name }}</strong><br>
                        <span style="font-size: 7px; color: #888888;">TCH-{{ str_pad($att->teacher->id, 3, '0', STR_PAD_LEFT) }}</span>
                    </td>
                    <td>{{ $att->clock_in ? substr($att->clock_in, 0, 5) : '-' }}</td>
                    <td>
                        @if($att->clock_in)
                            <span class="badge {{ $att->status_masuk === 'Terlambat' ? 'badge-alpa' : 'badge-hadir' }}">
                                {{ $att->status_masuk ?: 'Tepat Waktu' }}
                            </span>
                        @elseif(in_array($att->status, ['izin', 'sakit', 'alpa']))
                            <span class="badge badge-alpa">{{ ucfirst($att->status) }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($att->reward)
                            <span class="badge badge-hadir">🏆 Reward</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $att->clock_out ? substr($att->clock_out, 0, 5) : '-' }}</td>
                    <td>
                        @if($att->clock_out)
                            <span class="badge {{ in_array($att->status_pulang, ['Pulang Awal', 'Pulang Lebih Awal']) ? 'badge-terlambat' : 'badge-hadir' }}">
                                {{ $att->status_pulang ?: 'Normal' }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        {{ $att->check_in_method }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">Tidak ada data rekaman presensi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada tanggal: {{ date('d-m-Y H:i:s') }} WIB<br>
        Sistem Kepegawaian PKBM Ibadurrahman
    </div>

</body>
</html>
