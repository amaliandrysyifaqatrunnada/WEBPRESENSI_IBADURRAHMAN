<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeacherExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $teachers;

    public function __construct($teachers)
    {
        $this->teachers = $teachers;
    }

    /**
     * Return the collection of data.
     */
    public function collection()
    {
        return $this->teachers;
    }

    /**
     * Define the headings.
     */
    public function headings(): array
    {
        return [
            'NIP',
            'Nama Lengkap',
            'Email',
            'Jabatan',
            'Telepon',
            'Status',
        ];
    }

    /**
     * Map each row of data.
     */
    public function map($row): array
    {
        return [
            $row->nip ?? '-',
            $row->name,
            $row->email,
            $row->position,
            $row->phone ?? '-',
            ucfirst($row->status),
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
