<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Unit;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    /**
     * Display list of holidays.
     */
    public function index(Request $request)
    {
        $selectedUnitId = $request->input('unit_id', 'All');
        $search = $request->input('search');

        $query = Holiday::with(['unit', 'creator'])->orderBy('date', 'desc');

        if ($selectedUnitId !== 'All') {
            if ($selectedUnitId === 'Global') {
                $query->whereNull('unit_id');
            } else {
                $query->where('unit_id', $selectedUnitId);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $holidays = $query->paginate(15)->withQueryString();
        $units = Unit::all();

        return view('admin.holidays.index', compact('holidays', 'units', 'selectedUnitId', 'search'));
    }

    /**
     * Store new holiday.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_id' => 'nullable|exists:units,id',
            'is_active' => 'nullable|boolean',
        ], [
            'date.required' => 'Tanggal libur wajib diisi.',
            'name.required' => 'Nama hari libur wajib diisi.',
            'unit_id.exists' => 'Unit sekolah tidak valid.',
        ]);

        $holiday = Holiday::create([
            'date' => $validated['date'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'unit_id' => $validated['unit_id'] ?? null,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
            'created_by' => auth()->id(),
        ]);

        activity()
            ->performedOn($holiday)
            ->log("Superadmin menambahkan hari libur '{$holiday->name}' pada tanggal {$holiday->date}");

        return back()->with('success', 'Hari libur berhasil ditambahkan.');
    }

    /**
     * Update holiday.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_id' => 'nullable|exists:units,id',
            'is_active' => 'nullable|boolean',
        ]);

        $holiday->update([
            'date' => $validated['date'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'unit_id' => $validated['unit_id'] ?? null,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : false,
        ]);

        activity()
            ->performedOn($holiday)
            ->log("Superadmin memperbarui hari libur '{$holiday->name}'");

        return back()->with('success', 'Hari libur berhasil diperbarui.');
    }

    /**
     * Delete holiday.
     */
    public function destroy(Holiday $holiday)
    {
        $name = $holiday->name;
        $holiday->delete();

        activity()
            ->log("Superadmin menghapus hari libur '{$name}'");

        return back()->with('success', 'Hari libur berhasil dihapus.');
    }

    /**
     * Import holidays from Excel or CSV file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ], [
            'file.required' => 'Harap pilih berkas terlebih dahulu.',
            'file.mimes' => 'Format berkas tidak didukung. Harap gunakan berkas .xlsx, .xls, atau .csv',
            'file.max' => 'Ukuran berkas maksimal 5MB.',
        ]);

        try {
            $file = $request->file('file');
            $sheets = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass, $file);

            if (empty($sheets) || empty($sheets[0])) {
                return back()->with('error', 'Berkas kosong atau tidak dapat dibaca.');
            }

            $rows = $sheets[0];
            if (count($rows) < 2) {
                return back()->with('error', 'Berkas tidak memiliki baris data.');
            }

            $headers = array_map('trim', array_map('strtolower', $rows[0]));

            $colDate = $this->findHeaderIndex($headers, ['tanggal', 'date', 'tgl']);
            $colName = $this->findHeaderIndex($headers, ['nama', 'nama hari libur', 'name', 'title', 'keterangan libur']);
            $colDesc = $this->findHeaderIndex($headers, ['keterangan', 'description', 'catatan', 'desc']);
            $colUnit = $this->findHeaderIndex($headers, ['berlaku', 'berlaku untuk', 'unit', 'unit pendidikan', 'paket']);

            if ($colDate === -1 || $colName === -1) {
                return back()->with('error', 'Format kolom tidak sesuai. Kolom Tanggal dan Nama Hari Libur wajib ada di baris pertama/header.');
            }

            $units = Unit::all();
            $successCount = 0;
            $errors = [];

            foreach (array_slice($rows, 1) as $rowIndex => $row) {
                $rowNum = $rowIndex + 2;
                $rawDate = trim((string)($row[$colDate] ?? ''));
                $name = trim((string)($row[$colName] ?? ''));
                $desc = $colDesc !== -1 ? trim((string)($row[$colDesc] ?? '')) : null;
                $rawUnit = $colUnit !== -1 ? trim((string)($row[$colUnit] ?? '')) : '';

                if (empty($rawDate) && empty($name)) {
                    continue; // skip empty rows
                }

                if (empty($rawDate)) {
                    $errors[] = "Baris {$rowNum}: Tanggal kosong.";
                    continue;
                }

                if (empty($name)) {
                    $errors[] = "Baris {$rowNum}: Nama hari libur kosong.";
                    continue;
                }

                // Parse Date
                $parsedDate = $this->parseDateValue($rawDate);
                if (!$parsedDate) {
                    $errors[] = "Baris {$rowNum}: Format tanggal '{$rawDate}' tidak valid. Gunakan YYYY-MM-DD atau DD/MM/YYYY.";
                    continue;
                }

                // Resolve Unit
                $unitId = null;
                if (!empty($rawUnit) && strtolower($rawUnit) !== 'semua unit' && strtolower($rawUnit) !== 'global' && strtolower($rawUnit) !== 'semua') {
                    $matchedUnit = $units->first(function ($u) use ($rawUnit) {
                        return strcasecmp($u->name, $rawUnit) === 0 
                            || strcasecmp($u->package_type, $rawUnit) === 0 
                            || strcasecmp(str_replace('PAKET_', 'PAKET ', $u->package_type), $rawUnit) === 0
                            || strcasecmp('paket ' . $u->package_type, $rawUnit) === 0;
                    });
                    if ($matchedUnit) {
                        $unitId = $matchedUnit->id;
                    }
                }

                Holiday::updateOrCreate(
                    [
                        'date' => $parsedDate,
                        'unit_id' => $unitId,
                    ],
                    [
                        'name' => $name,
                        'description' => $desc ?: null,
                        'is_active' => true,
                        'created_by' => auth()->id(),
                    ]
                );

                $successCount++;
            }

            activity()->log("Superadmin mengimpor {$successCount} data hari libur dari berkas.");

            if (count($errors) > 0) {
                return back()->with('warning', "Impor selesai. {$successCount} hari libur berhasil diimpor. Catatan: " . implode(' | ', array_slice($errors, 0, 5)));
            }

            return back()->with('success', "Impor berhasil! {$successCount} hari libur telah ditambahkan/diperbarui.");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses berkas impor: ' . $e->getMessage());
        }
    }

    /**
     * Download CSV/Excel template for Holiday import.
     */
    public function downloadTemplate()
    {
        $csvHeader = "Tanggal,Nama Hari Libur,Keterangan,Berlaku Untuk\n";
        $csvRows = [
            "2026-08-17,Hari Kemerdekaan RI,Libur Nasional,Semua Unit\n",
            "2026-08-20,Kegiatan Internal Paket A,Khusus Paket A,Paket A\n",
            "2026-12-25,Hari Raya Natal,Libur Nasional,Semua Unit\n",
        ];

        $content = $csvHeader . implode('', $csvRows);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Template_Import_Hari_Libur.csv"',
        ]);
    }

    /**
     * Helper to find header index.
     */
    protected function findHeaderIndex(array $headers, array $candidates): int
    {
        foreach ($candidates as $cand) {
            $idx = array_search($cand, $headers);
            if ($idx !== false) {
                return $idx;
            }
        }
        return -1;
    }

    /**
     * Helper to parse date string or Excel serial date.
     */
    protected function parseDateValue($val): ?string
    {
        if (is_numeric($val) && (float)$val > 30000) {
            // Excel serial date
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('Y-m-d');
            } catch (\Exception $e) {
                // fallback
            }
        }

        $valStr = trim((string)$val);
        // Try common formats
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'j/n/Y', 'j-n-Y'] as $fmt) {
            try {
                $d = \Carbon\Carbon::createFromFormat($fmt, $valStr);
                if ($d) {
                    return $d->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // try next
            }
        }

        try {
            return \Carbon\Carbon::parse($valStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
