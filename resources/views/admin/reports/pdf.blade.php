<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Rekap Presensi Bulanan - PKBM Ibadurrahman</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 8mm 8mm 8mm;
        }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 8px;
            color: #333333;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #2E7D32;
            padding-bottom: 4px;
        }
        .header h1 {
            font-size: 13px;
            color: #2E7D32;
            margin: 0 0 2px 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 8px;
            color: #666666;
        }
        .filter-info {
            margin-bottom: 8px;
            font-size: 8px;
            background-color: #f9f9f9;
            padding: 5px;
            border: 1px solid #e3e3e3;
            border-radius: 3px;
        }
        .section-title {
            font-size: 9px;
            font-weight: bold;
            color: #2E7D32;
            margin-top: 10px;
            margin-bottom: 4px;
            text-transform: uppercase;
            border-bottom: 1px solid #2E7D32;
            padding-bottom: 2px;
        }
        .matrix-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 8px;
            table-layout: fixed;
        }
        .matrix-table th {
            background-color: #2E7D32;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 3px 1px;
            border: 1px solid #1b5e20;
            font-size: 6.5px;
            word-wrap: break-word;
        }
        .matrix-table td {
            padding: 2px 1px;
            border: 1px solid #dddddd;
            font-size: 6.5px;
            text-align: center;
        }
        .matrix-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .cell-libur {
            background-color: #FFFDE7 !important;
        }
        .badge {
            display: inline-block;
            padding: 1px 2px;
            border-radius: 2px;
            font-weight: bold;
            font-size: 6.5px;
        }
        .badge-h { background-color: #E8F5E9; color: #2E7D32; }
        .badge-tl { background-color: #FFF8E1; color: #F57F17; }
        .badge-ppa { background-color: #FFF3E0; color: #E65100; }
        .badge-i { background-color: #E8EAF6; color: #3F51B5; }
        .badge-s { background-color: #F3E5F5; color: #9C27B0; }
        .badge-tk { background-color: #FFEBEE; color: #D32F2F; }
        .badge-l { background-color: #FFF9C4; color: #F57F17; }
        .badge-tp { background-color: #ECEFF1; color: #546E7A; }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 8px;
        }
        .summary-table th {
            background-color: #2E7D32;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 4px 5px;
            border: 1px solid #2E7D32;
            font-size: 7.5px;
        }
        .summary-table td {
            padding: 3px 5px;
            border: 1px solid #dddddd;
            font-size: 7.5px;
        }
        .legend-container {
            margin-top: 6px;
            margin-bottom: 8px;
            background-color: #fcfcfc;
            padding: 5px;
            border: 1px solid #e0e0e0;
            border-radius: 3px;
        }
        .legend-title {
            font-weight: bold;
            font-size: 7.5px;
            color: #2E7D32;
            margin-bottom: 3px;
        }
        .legend-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .legend-grid td {
            padding: 2px 3px;
            font-size: 7px;
            border: none;
        }
        .footer {
            margin-top: 10px;
            text-align: right;
            font-size: 7px;
            color: #777777;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>PKBM IBADURRAHMAN SIDOARJO</h1>
        <p style="margin-top: 2px; font-weight: bold; color: #2E7D32; font-size: 10px;">REKAP PRESENSI TENAGA PENDIDIK (BULANAN KALENDER)</p>
    </div>

    @if(isset($recapData))
        <!-- Filter Meta Information -->
        <div class="filter-info">
            <strong>Periode Rekap:</strong> {{ $recapData['period_label'] }} | 
            <strong>Unit:</strong> {{ $recapData['unit_label'] }} | 
            <strong>Total Pendidik:</strong> {{ count($recapData['matrix_rows']) }} Orang
        </div>

        <!-- Calendar Matrix Table -->
        <div class="section-title">Matriks Presensi Harian Per Tenaga Pendidik</div>
        <table class="matrix-table">
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 16%; text-align: left; padding-left: 4px;">Nama Tenaga Pendidik</th>
                    <th style="width: 7%;">Unit</th>
                    @foreach($recapData['dates'] as $dateMeta)
                        <th>
                            {{ $dateMeta['day_num'] }}<br>
                            <span style="font-size: 5.5px; font-weight: normal;">{{ $dateMeta['day_short'] }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($recapData['matrix_rows'] as $idx => $row)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td style="text-align: left; padding-left: 4px;">
                            <strong>{{ $row['teacher_name'] }}</strong>
                        </td>
                        <td>{{ $row['unit_name'] }}</td>
                        @foreach($recapData['dates'] as $dayNum => $dateMeta)
                            @php
                                $dayData = $row['days'][$dayNum] ?? ['code' => 'TP', 'scan_display' => 'TP', 'jam_masuk' => '-', 'jam_pulang' => '-'];
                                $code = $dayData['code'];
                                $badgeClass = 'badge-' . strtolower($code);
                            @endphp
                            <td class="{{ $code === 'L' ? 'cell-libur' : '' }}" style="vertical-align: middle; padding: 2px 0;">
                                @if(in_array($code, ['H', 'TL', 'PPA']))
                                    <div style="font-weight: bold; font-size: 6px; color: {{ $code === 'H' ? '#2E7D32' : ($code === 'TL' ? '#D84315' : '#E65100') }};">{{ $dayData['jam_masuk'] }}</div>
                                    <div style="font-size: 6px; color: #444444; margin-top: 1px;">{{ $dayData['jam_pulang'] }}</div>
                                @elseif($code === 'L')
                                    <span class="badge badge-l" style="font-size: 5.5px;">LIBUR</span>
                                @elseif($code === 'I')
                                    <span class="badge badge-i" style="font-size: 5.5px;">Izin</span>
                                @elseif($code === 'S')
                                    <span class="badge badge-s" style="font-size: 5.5px;">Sakit</span>
                                @elseif($code === 'TK')
                                    <span class="badge badge-tk" style="font-size: 5.5px;">TK</span>
                                @else
                                    <span class="badge badge-tp" style="font-size: 5.5px;">TP</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 3 + count($recapData['dates']) }}" style="text-align: center;">Tidak ada data presensi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Legenda Kode Status -->
        <div class="legend-container">
            <div class="legend-title">LEGENDA KODE STATUS PRESENSI:</div>
            <table class="legend-grid">
                <tr>
                    <td><span class="badge badge-h">H</span> = Hadir (Tepat Waktu)</td>
                    <td><span class="badge badge-tl">TL</span> = Terlambat</td>
                    <td><span class="badge badge-ppa">PPA</span> = Pulang Lebih Awal</td>
                    <td><span class="badge badge-i">I</span> = Izin</td>
                    <td><span class="badge badge-s">S</span> = Sakit</td>
                    <td><span class="badge badge-tk">TK</span> = Tanpa Keterangan</td>
                    <td><span class="badge badge-l">L</span> = Libur</td>
                    <td><span class="badge badge-tp">TP</span> = Tanpa Presensi</td>
                </tr>
            </table>
        </div>

        <!-- Detail Presensi Harian Table -->
        <div style="page-break-before: always;"></div>
        <div class="header">
            <h1>PKBM IBADURRAHMAN SIDOARJO</h1>
            <p style="margin-top: 2px; font-weight: bold; color: #2E7D32; font-size: 10px;">DETAIL PRESENSI HARIAN TENAGA PENDIDIK</p>
        </div>
        <div class="section-title">Rincian Log Presensi Harian (Periode {{ $recapData['period_label'] }})</div>
        <table class="summary-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 10%;">Tanggal</th>
                    <th style="width: 8%;">Hari</th>
                    <th style="width: 22%;">Nama Tenaga Pendidik</th>
                    <th style="width: 10%;">Unit</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 8%; text-align: center;">Jam Masuk</th>
                    <th style="width: 8%; text-align: center;">Jam Pulang</th>
                    <th style="width: 8%; text-align: center;">Metode</th>
                    <th style="width: 10%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recapData['detail_presensi'] as $idx => $dp)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $dp['date'] }}</td>
                        <td>{{ $dp['day_name'] }}</td>
                        <td><strong>{{ $dp['teacher_name'] }}</strong></td>
                        <td>{{ $dp['unit_name'] }}</td>
                        <td>{{ $dp['status'] }}</td>
                        <td style="text-align: center;">{{ $dp['jam_masuk'] }}</td>
                        <td style="text-align: center;">{{ $dp['jam_pulang'] }}</td>
                        <td style="text-align: center;">{{ $dp['metode'] }}</td>
                        <td>{{ $dp['keterangan'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center;">Tidak ada rincian presensi harian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Rekapitulasi Per Tenaga Pendidik Table -->
        <div class="section-title">Rekapitulasi Total Per Tenaga Pendidik</div>
        <table class="summary-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 25%;">Nama Tenaga Pendidik</th>
                    <th style="width: 15%;">Unit</th>
                    <th style="width: 7%; text-align: center;">Hadir (H)</th>
                    <th style="width: 8%; text-align: center;">Terlambat (TL)</th>
                    <th style="width: 9%; text-align: center;">Pulang Awal (PPA)</th>
                    <th style="width: 7%; text-align: center;">Izin (I)</th>
                    <th style="width: 7%; text-align: center;">Sakit (S)</th>
                    <th style="width: 8%; text-align: center;">Tanpa Ket (TK)</th>
                    <th style="width: 7%; text-align: center;">Libur (L)</th>
                    <th style="width: 8%; text-align: center;">Tanpa Absen (TP)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recapData['teacher_summaries'] as $idx => $ts)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><strong>{{ $ts['teacher_name'] }}</strong></td>
                        <td>{{ $ts['unit_name'] }}</td>
                        <td style="text-align: center; color: #2E7D32; font-weight: bold;">{{ $ts['hadir'] }}</td>
                        <td style="text-align: center; color: #F57F17; font-weight: bold;">{{ $ts['terlambat'] }}</td>
                        <td style="text-align: center; color: #E65100; font-weight: bold;">{{ $ts['pulang_awal'] }}</td>
                        <td style="text-align: center; color: #3F51B5; font-weight: bold;">{{ $ts['izin'] }}</td>
                        <td style="text-align: center; color: #9C27B0; font-weight: bold;">{{ $ts['sakit'] }}</td>
                        <td style="text-align: center; color: #D32F2F; font-weight: bold;">{{ $ts['tanpa_ket'] }}</td>
                        <td style="text-align: center; color: #F57F17; font-weight: bold;">{{ $ts['libur'] }}</td>
                        <td style="text-align: center; color: #546E7A; font-weight: bold;">{{ $ts['tanpa_presensi'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Rekapitulasi Per Unit Table (For Superadmin / Global View) -->
        @if(!empty($recapData['unit_summaries']['per_unit']))
            <div class="section-title">Rekapitulasi Totals Per Unit</div>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th style="width: 4%;">No</th>
                        <th style="width: 25%;">Nama Unit</th>
                        <th style="width: 10%; text-align: center;">Jumlah Guru</th>
                        <th style="width: 7%; text-align: center;">Hadir</th>
                        <th style="width: 8%; text-align: center;">Terlambat</th>
                        <th style="width: 9%; text-align: center;">Pulang Awal</th>
                        <th style="width: 7%; text-align: center;">Izin</th>
                        <th style="width: 7%; text-align: center;">Sakit</th>
                        <th style="width: 8%; text-align: center;">Tanpa Ket</th>
                        <th style="width: 7%; text-align: center;">Libur</th>
                        <th style="width: 8%; text-align: center;">Tanpa Absen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recapData['unit_summaries']['per_unit'] as $idx => $us)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><strong>{{ $us['unit_name'] }}</strong></td>
                            <td style="text-align: center;">{{ $us['teacher_count'] }}</td>
                            <td style="text-align: center; color: #2E7D32; font-weight: bold;">{{ $us['hadir'] }}</td>
                            <td style="text-align: center; color: #F57F17; font-weight: bold;">{{ $us['terlambat'] }}</td>
                            <td style="text-align: center; color: #E65100; font-weight: bold;">{{ $us['pulang_awal'] }}</td>
                            <td style="text-align: center; color: #3F51B5; font-weight: bold;">{{ $us['izin'] }}</td>
                            <td style="text-align: center; color: #9C27B0; font-weight: bold;">{{ $us['sakit'] }}</td>
                            <td style="text-align: center; color: #D32F2F; font-weight: bold;">{{ $us['tanpa_ket'] }}</td>
                            <td style="text-align: center; color: #F57F17; font-weight: bold;">{{ $us['libur'] }}</td>
                            <td style="text-align: center; color: #546E7A; font-weight: bold;">{{ $us['tanpa_presensi'] }}</td>
                        </tr>
                    @endforeach
                    @php $gt = $recapData['unit_summaries']['grand_total']; @endphp
                    <tr style="background-color: #E8F5E9; font-weight: bold;">
                        <td colspan="2">TOTAL KESELURUHAN</td>
                        <td style="text-align: center;">{{ $gt['teacher_count'] }}</td>
                        <td style="text-align: center; color: #2E7D32;">{{ $gt['hadir'] }}</td>
                        <td style="text-align: center; color: #F57F17;">{{ $gt['terlambat'] }}</td>
                        <td style="text-align: center; color: #E65100;">{{ $gt['pulang_awal'] }}</td>
                        <td style="text-align: center; color: #3F51B5;">{{ $gt['izin'] }}</td>
                        <td style="text-align: center; color: #9C27B0;">{{ $gt['sakit'] }}</td>
                        <td style="text-align: center; color: #D32F2F;">{{ $gt['tanpa_ket'] }}</td>
                        <td style="text-align: center; color: #F57F17;">{{ $gt['libur'] }}</td>
                        <td style="text-align: center; color: #546E7A;">{{ $gt['tanpa_presensi'] }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

    @endif

    <div class="footer">
        Dicetak pada: {{ date('d-m-Y H:i:s') }} WIB | Sistem Kepegawaian PKBM Ibadurrahman Sidoarjo
    </div>

</body>
</html>
