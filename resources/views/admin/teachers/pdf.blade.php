<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Tenaga Pendidik PKBM Ibadurrahman</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
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
            font-size: 18px;
            color: #2E7D32;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 11px;
            color: #666666;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .data-table th {
            background-color: #2E7D32;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            border: 1px solid #2E7D32;
        }
        .data-table td {
            padding: 8px 10px;
            border: 1px solid #dddddd;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .status-inactive {
            background-color: #ffebee;
            color: #c62828;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PKBM IBADURRAHMAN</h1>
        <p>Daftar Tenaga Pendidik / Guru</p>
        <p style="font-size: 9px; margin-top: 5px; color: #999;">Dicetak pada: {{ date('d-m-Y H:i:s') }} WIB</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 15%">NIP</th>
                <th style="width: 25%">Nama Lengkap</th>
                <th style="width: 25%">Email</th>
                <th style="width: 15%">Jabatan</th>
                <th style="width: 15%">Telepon</th>
                <th style="width: 10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($teachers as $index => $teacher)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $teacher->nip ?? '-' }}</td>
                    <td>{{ $teacher->name }}</td>
                    <td>{{ $teacher->email }}</td>
                    <td>{{ $teacher->position }}</td>
                    <td>{{ $teacher->phone ?? '-' }}</td>
                    <td>
                        <span class="status-badge {{ $teacher->status === 'active' ? 'status-active' : 'status-inactive' }}">
                            {{ $teacher->status }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
