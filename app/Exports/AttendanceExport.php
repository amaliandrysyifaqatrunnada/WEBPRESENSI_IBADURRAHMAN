<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $attendances;

    public function __construct($attendances)
    {
        $this->attendances = $attendances;
    }

    /**
     * Return the collection of data.
     */
    public function collection()
    {
        return $this->attendances;
    }

    /**
     * Define the headings.
     */
    public function headings(): array
    {
        return [
            'ID Rekomendasi',
            'Tanggal',
            'NIP',
            'Nama Tenaga Pendidik',
            'Jabatan',
            'Jam Masuk',
            'Jam Pulang',
            'Status Kehadiran',
            'Status Pulang',
            'Metode Presensi',
        ];
    }

    /**
     * Map each row of data.
     */
    public function map($row): array
    {
        return [
            $row->teacher->display_id,
            $row->date,
            $row->teacher->nip ?? '-',
            $row->teacher->name,
            $row->teacher->position,
            $row->clock_in ?? '-',
            $row->clock_out ?? '-',
            ucfirst($row->status),
            $row->status_pulang ?? 'Normal',
            $row->check_in_method,
        ];
    }

    /**
     * Apply styles to sheet.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2E7D32']]],
        ];
    }
}
