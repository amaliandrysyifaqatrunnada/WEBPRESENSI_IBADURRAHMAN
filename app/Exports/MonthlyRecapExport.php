<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MonthlyRecapExport implements WithMultipleSheets
{
    protected array $recapData;

    public function __construct(array $recapData)
    {
        $this->recapData = $recapData;
    }

    /**
     * Return array of 3 sheets:
     * 1. REKAP KALENDER BULANAN
     * 2. DETAIL PRESENSI
     * 3. REKAPITULASI
     */
    public function sheets(): array
    {
        return [
            new MonthlyCalendarMatrixSheet($this->recapData),
            new AttendanceDetailSheet($this->recapData),
            new MonthlyRecapSummarySheet($this->recapData),
        ];
    }

    public function collection()
    {
        return (new MonthlyCalendarMatrixSheet($this->recapData))->collection();
    }
}

/**
 * Sheet 1: REKAP KALENDER BULANAN
 */
class MonthlyCalendarMatrixSheet implements FromCollection, ShouldAutoSize, WithStyles, WithTitle
{
    protected array $recapData;

    public function __construct(array $recapData)
    {
        $this->recapData = $recapData;
    }

    public function title(): string
    {
        return 'REKAP KALENDER BULANAN';
    }

    public function collection()
    {
        $rows = [];

        // 1. Header Information Block
        $rows[] = ['REKAP PRESENSI TENAGA PENDIDIK'];
        $rows[] = ['PKBM IBADURRAHMAN SIDOARJO'];
        $rows[] = ['PERIODE:', strtoupper($this->recapData['period_label'])];
        $rows[] = ['UNIT:', strtoupper($this->recapData['unit_label'])];
        $rows[] = ['']; // Empty row

        // 2. Main Calendar Matrix Table Headings
        $matrixHeader = ['NO', 'NAMA TENAGA PENDIDIK', 'UNIT'];
        foreach ($this->recapData['dates'] as $dateMeta) {
            $matrixHeader[] = $dateMeta['header_label'];
        }
        $rows[] = $matrixHeader;

        // 3. Matrix Data Rows (1 Row Per Teacher with Scan Times)
        foreach ($this->recapData['matrix_rows'] as $idx => $row) {
            $teacherRow = [
                $idx + 1,
                $row['teacher_name'],
                $row['unit_name'],
            ];

            foreach ($this->recapData['dates'] as $dayNum => $dateMeta) {
                $dayData = $row['days'][$dayNum] ?? ['scan_display' => 'TP'];
                $teacherRow[] = $dayData['scan_display'] ?? $dayData['code'];
            }

            $rows[] = $teacherRow;
        }

        // 4. Separator
        $rows[] = [''];
        $rows[] = [''];

        // 5. Legenda Kode Status
        $rows[] = ['LEGENDA KODE STATUS PRESENSI'];
        $rows[] = ['KODE', 'KETERANGAN STATUS'];
        foreach ($this->recapData['legend'] as $code => $label) {
            $rows[] = [$code, $label];
        }

        // 6. Separator
        $rows[] = [''];
        $rows[] = [''];

        // 7. Rekapitulasi Per Tenaga Pendidik Table
        $rows[] = ['REKAPITULASI PER TENAGA PENDIDIK'];
        $rows[] = [
            'NO',
            'NAMA TENAGA PENDIDIK',
            'UNIT',
            'HADIR (H)',
            'TERLAMBAT (TL)',
            'PULANG AWAL (PPA)',
            'IZIN (I)',
            'SAKIT (S)',
            'TANPA KET (TK)',
            'LIBUR (L)',
            'TANPA PRESENSI (TP)'
        ];

        foreach ($this->recapData['teacher_summaries'] as $idx => $ts) {
            $rows[] = [
                $idx + 1,
                $ts['teacher_name'],
                $ts['unit_name'],
                $ts['hadir'],
                $ts['terlambat'],
                $ts['pulang_awal'],
                $ts['izin'],
                $ts['sakit'],
                $ts['tanpa_ket'],
                $ts['libur'],
                $ts['tanpa_presensi']
            ];
        }

        // 8. Rekapitulasi Per Unit Table (If Superadmin / Global view)
        if (!empty($this->recapData['unit_summaries']['per_unit'])) {
            $rows[] = [''];
            $rows[] = [''];
            $rows[] = ['REKAPITULASI PER UNIT'];
            $rows[] = [
                'NO',
                'NAMA UNIT',
                'JUMLAH GURU',
                'HADIR',
                'TERLAMBAT',
                'PULANG AWAL',
                'IZIN',
                'SAKIT',
                'TANPA KET',
                'LIBUR',
                'TANPA PRESENSI'
            ];

            foreach ($this->recapData['unit_summaries']['per_unit'] as $idx => $us) {
                $rows[] = [
                    $idx + 1,
                    $us['unit_name'],
                    $us['teacher_count'],
                    $us['hadir'],
                    $us['terlambat'],
                    $us['pulang_awal'],
                    $us['izin'],
                    $us['sakit'],
                    $us['tanpa_ket'],
                    $us['libur'],
                    $us['tanpa_presensi']
                ];
            }

            // Grand Total Row
            $gt = $this->recapData['unit_summaries']['grand_total'];
            $rows[] = [
                'TOTAL',
                $gt['unit_name'],
                $gt['teacher_count'],
                $gt['hadir'],
                $gt['terlambat'],
                $gt['pulang_awal'],
                $gt['izin'],
                $gt['sakit'],
                $gt['tanpa_ket'],
                $gt['libur'],
                $gt['tanpa_presensi']
            ];
        }

        return collect($rows);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3:A4')->getFont()->setBold(true);

        // Freeze Panes at Column D and Row 7
        $sheet->freezePane('D7');

        // Style Matrix Header Row (Row 6)
        $daysCount = count($this->recapData['dates']);
        $lastColumnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + $daysCount);
        $matrixHeaderRange = 'A6:' . $lastColumnLetter . '6';

        $sheet->getStyle($matrixHeaderRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($matrixHeaderRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2E7D32');
        $sheet->getStyle($matrixHeaderRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Center Alignment & Wrap Text for Matrix Cells
        $totalTeachers = count($this->recapData['matrix_rows']);
        if ($totalTeachers > 0) {
            $matrixDataRange = 'D7:' . $lastColumnLetter . (6 + $totalTeachers);
            $sheet->getStyle($matrixDataRange)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
        }

        return [];
    }
}

/**
 * Sheet 2: DETAIL PRESENSI
 */
class AttendanceDetailSheet implements FromCollection, ShouldAutoSize, WithStyles, WithTitle
{
    protected array $recapData;

    public function __construct(array $recapData)
    {
        $this->recapData = $recapData;
    }

    public function title(): string
    {
        return 'DETAIL PRESENSI';
    }

    public function collection()
    {
        $rows = [];

        // Header Information
        $rows[] = ['DETAIL PRESENSI TENAGA PENDIDIK'];
        $rows[] = ['PKBM IBADURRAHMAN SIDOARJO'];
        $rows[] = ['PERIODE:', strtoupper($this->recapData['period_label'])];
        $rows[] = ['UNIT:', strtoupper($this->recapData['unit_label'])];
        $rows[] = [''];

        // Table Column Headers
        $rows[] = [
            'NO',
            'TANGGAL',
            'HARI',
            'NAMA TENAGA PENDIDIK',
            'UNIT',
            'STATUS',
            'JAM MASUK',
            'JAM PULANG',
            'METODE',
            'KETERANGAN'
        ];

        // Rows
        $detailList = $this->recapData['detail_presensi'] ?? [];
        foreach ($detailList as $idx => $item) {
            $rows[] = [
                $idx + 1,
                $item['date'],
                $item['day_name'],
                $item['teacher_name'],
                $item['unit_name'],
                $item['status'],
                $item['jam_masuk'],
                $item['jam_pulang'],
                $item['metode'],
                $item['keterangan']
            ];
        }

        return collect($rows);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3:A4')->getFont()->setBold(true);

        // Header row styling (Row 6)
        $sheet->getStyle('A6:J6')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A6:J6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2E7D32');
        $sheet->getStyle('A6:J6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}

/**
 * Sheet 3: REKAPITULASI
 */
class MonthlyRecapSummarySheet implements FromCollection, ShouldAutoSize, WithStyles, WithTitle
{
    protected array $recapData;

    public function __construct(array $recapData)
    {
        $this->recapData = $recapData;
    }

    public function title(): string
    {
        return 'REKAPITULASI';
    }

    public function collection()
    {
        $rows = [];

        // Header Information
        $rows[] = ['REKAPITULASI PRESENSI TENAGA PENDIDIK'];
        $rows[] = ['PKBM IBADURRAHMAN SIDOARJO'];
        $rows[] = ['PERIODE:', strtoupper($this->recapData['period_label'])];
        $rows[] = ['UNIT:', strtoupper($this->recapData['unit_label'])];
        $rows[] = [''];

        // Rekapitulasi Per Tenaga Pendidik Table
        $rows[] = ['REKAPITULASI PER TENAGA PENDIDIK'];
        $rows[] = [
            'NO',
            'NAMA TENAGA PENDIDIK',
            'UNIT',
            'HADIR (H)',
            'TERLAMBAT (TL)',
            'PULANG AWAL (PPA)',
            'IZIN (I)',
            'SAKIT (S)',
            'TANPA KET (TK)',
            'LIBUR (L)',
            'TANPA PRESENSI (TP)'
        ];

        foreach ($this->recapData['teacher_summaries'] as $idx => $ts) {
            $rows[] = [
                $idx + 1,
                $ts['teacher_name'],
                $ts['unit_name'],
                $ts['hadir'],
                $ts['terlambat'],
                $ts['pulang_awal'],
                $ts['izin'],
                $ts['sakit'],
                $ts['tanpa_ket'],
                $ts['libur'],
                $ts['tanpa_presensi']
            ];
        }

        // Rekapitulasi Per Unit Table (If Superadmin / Global View)
        if (!empty($this->recapData['unit_summaries']['per_unit'])) {
            $rows[] = [''];
            $rows[] = [''];
            $rows[] = ['REKAPITULASI PER UNIT'];
            $rows[] = [
                'NO',
                'NAMA UNIT',
                'JUMLAH GURU',
                'HADIR',
                'TERLAMBAT',
                'PULANG AWAL',
                'IZIN',
                'SAKIT',
                'TANPA KET',
                'LIBUR',
                'TANPA PRESENSI'
            ];

            foreach ($this->recapData['unit_summaries']['per_unit'] as $idx => $us) {
                $rows[] = [
                    $idx + 1,
                    $us['unit_name'],
                    $us['teacher_count'],
                    $us['hadir'],
                    $us['terlambat'],
                    $us['pulang_awal'],
                    $us['izin'],
                    $us['sakit'],
                    $us['tanpa_ket'],
                    $us['libur'],
                    $us['tanpa_presensi']
                ];
            }

            // Grand Total Row
            $gt = $this->recapData['unit_summaries']['grand_total'];
            $rows[] = [
                'TOTAL',
                $gt['unit_name'],
                $gt['teacher_count'],
                $gt['hadir'],
                $gt['terlambat'],
                $gt['pulang_awal'],
                $gt['izin'],
                $gt['sakit'],
                $gt['tanpa_ket'],
                $gt['libur'],
                $gt['tanpa_presensi']
            ];
        }

        return collect($rows);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3:A4')->getFont()->setBold(true);

        // Header row styling
        $sheet->getStyle('A7:K7')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A7:K7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2E7D32');
        $sheet->getStyle('A7:K7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
