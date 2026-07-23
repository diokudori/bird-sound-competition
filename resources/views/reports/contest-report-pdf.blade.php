<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hasil Akhir Kontes Kicau Burung</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #cbd5e1;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }
        .logo-title {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #0f172a;
            text-transform: uppercase;
            margin: 0;
        }
        .subtitle {
            font-size: 11px;
            color: #64748b;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 4px 0 0 0;
        }
        .contest-details {
            margin-bottom: 25px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 15px;
        }
        .contest-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .contest-details td {
            padding: 4px 0;
            vertical-align: top;
        }
        .contest-details td.label {
            font-weight: bold;
            color: #475569;
            width: 130px;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .main-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 10px 12px;
            font-size: 11px;
            text-transform: uppercase;
        }
        .main-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .main-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        /* Top 3 rank highlighting styles */
        .rank-1 {
            background-color: #fef08a !important; /* Gold light */
            font-weight: bold;
        }
        .rank-2 {
            background-color: #f1f5f9 !important; /* Silver light */
            font-weight: bold;
        }
        .rank-3 {
            background-color: #ffedd5 !important; /* Bronze light */
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 4px;
            text-align: center;
        }
        .badge-gold { background-color: #ca8a04; color: white; }
        .badge-silver { background-color: #475569; color: white; }
        .badge-bronze { background-color: #c2410c; color: white; }
        .badge-normal { background-color: #e2e8f0; color: #475569; }
        
        .score-col {
            font-family: monospace;
            font-weight: bold;
            font-size: 14px;
            text-align: right;
        }
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .footer table {
            width: 100%;
        }
        .timestamp-box {
            font-size: 10px;
            color: #64748b;
            font-family: monospace;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            margin-top: 40px;
        }
        .signature-area {
            text-align: right;
            padding-right: 20px;
        }
        .signature-space {
            height: 70px;
        }
        .signature-line {
            border-top: 1px solid #475569;
            width: 200px;
            display: inline-block;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header">
        <h1 class="logo-title">Laporan Hasil Akhir Penilaian</h1>
        <p class="subtitle">Sistem Juri Otomatis Kontes Kicau Burung</p>
    </div>

    <!-- Contest details meta information -->
    <div class="contest-details">
        <table>
            <tr>
                <td class="label">Nama Kontes</td>
                <td>: {{ $contest->name }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Pelaksanaan</td>
                <td>: {{ \Carbon\Carbon::parse($contest->date)->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td class="label">Status Penjurian</td>
                <td>: <span style="color: #10b981; font-weight: bold; text-transform: uppercase;">SELESAI (FINAL)</span></td>
            </tr>
        </table>
    </div>

    <!-- Leaderboard Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 80px; text-align: center;">Juara / Rank</th>
                <th style="width: 80px; text-align: center;">Gantangan</th>
                <th>Nama Burung</th>
                <th>Jenis Burung</th>
                <th>Nomor Cincin</th>
                <th>Pemilik</th>
                <th style="width: 100px; text-align: right;">Total Nilai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($participants as $index => $p)
                @php
                    $rankClass = '';
                    $badgeClass = 'badge-normal';
                    $rankText = $index + 1;
                    
                    if ($index == 0) {
                        $rankClass = 'rank-1';
                        $badgeClass = 'badge-gold';
                        $rankText = 'Juara 1';
                    } elseif ($index == 1) {
                        $rankClass = 'rank-2';
                        $badgeClass = 'badge-silver';
                        $rankText = 'Juara 2';
                    } elseif ($index == 2) {
                        $rankClass = 'rank-3';
                        $badgeClass = 'badge-bronze';
                        $rankText = 'Juara 3';
                    }
                @endphp
                <tr class="{{ $rankClass }}">
                    <td style="text-align: center; vertical-align: middle;">
                        <span class="badge {{ $badgeClass }}">{{ $rankText }}</span>
                    </td>
                    <td style="text-align: center; font-family: monospace;">#{{ $p['gantangan'] }}</td>
                    <td>{{ $p['name'] }}</td>
                    <td>{{ $p['bird_type'] }}</td>
                    <td style="font-family: monospace; color: #475569;">{{ $p['ring_number'] }}</td>
                    <td>{{ $p['owner_name'] }}</td>
                    <td class="score-col">{{ $p['total_score'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Signature and Timestamp Footer -->
    <div class="footer">
        <table>
            <tr>
                <td style="vertical-align: bottom;">
                    <!-- Digital Timestamp for security/authenticity -->
                    <div class="timestamp-box">
                        <strong>BUKTI DIGITAL SAH:</strong><br>
                        Dicetak oleh server lokal pada:<br>
                        {{ $timestamp }}
                    </div>
                </td>
                <td class="signature-area">
                    <p>Panitia Pelaksana,</p>
                    <div class="signature-space"></div>
                    <span class="signature-line"></span>
                    <p style="font-size: 11px; margin-top: 4px; color: #475569;">Ketua Penyelenggara Kontes</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
