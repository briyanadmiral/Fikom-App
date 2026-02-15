<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Surat {{ $tahun }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header h2 {
            margin: 5px 0 0;
            font-size: 14px;
            color: #666;
            font-weight: normal;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background: #4b0082;
            color: white;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .stats-grid {
            display: table;
            width: 100%;
        }
        .stats-row {
            display: table-row;
        }
        .stats-cell {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .stats-value {
            font-size: 24px;
            font-weight: bold;
            color: #4b0082;
        }
        .stats-label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN SURAT</h1>
        <h2>Tahun {{ $tahun }}</h2>
    </div>

    <div class="section">
        <div class="section-title">RINGKASAN STATISTIK</div>
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stats-cell">
                    <div class="stats-value">{{ $stats['total_st'] }}</div>
                    <div class="stats-label">Total Surat Tugas</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-value">{{ $stats['total_sk'] }}</div>
                    <div class="stats-label">Total Surat Keputusan</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-value">{{ $stats['disetujui_st'] }}</div>
                    <div class="stats-label">ST Disetujui</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-value">{{ $stats['disetujui_sk'] }}</div>
                    <div class="stats-label">SK Disetujui</div>
                </div>
            </div>
            <div class="stats-row">
                <div class="stats-cell">
                    <div class="stats-value">{{ $stats['pending_st'] }}</div>
                    <div class="stats-label">ST Pending</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-value">{{ $stats['pending_sk'] }}</div>
                    <div class="stats-label">SK Pending</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-value">{{ $stats['total_st'] + $stats['total_sk'] }}</div>
                    <div class="stats-label">Total Semua Surat</div>
                </div>
                <div class="stats-cell">
                    <div class="stats-value">{{ $stats['disetujui_st'] + $stats['disetujui_sk'] }}</div>
                    <div class="stats-label">Total Disetujui</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">DATA BULANAN</div>
        <table>
            <thead>
                <tr>
                    <th>Bulan</th>
                    @foreach($monthlyData as $data)
                        <th>{{ $data['bulan'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Surat Tugas</strong></td>
                    @foreach($monthlyData as $data)
                        <td>{{ $data['st'] }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td><strong>Surat Keputusan</strong></td>
                    @foreach($monthlyData as $data)
                        <td>{{ $data['sk'] }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    @foreach($monthlyData as $data)
                        <td><strong>{{ $data['st'] + $data['sk'] }}</strong></td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        Dicetak pada: {{ now()->format('d M Y H:i:s') }} | Sistem FIKOM
    </div>
</body>
</html>
