<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Cuti</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #1B4D3E; color: white; }
        h2 { text-align: center; margin-bottom: 2px; }
        p.sub { text-align: center; margin-top: 0; color: #555; }
    </style>
</head>
<body>
    <h2>Rekap Pengajuan Cuti Pegawai</h2>
    <p class="sub">{{ $tahunIniBerjalan ? 'Tahun ' . $tahun : 'Arsip Tahun ' . $tahun }}</p>
    <p>Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Nama Pegawai</th>
                <th>NIP</th>
                <th>Jenis Cuti</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
                <th>Jumlah Hari</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($riwayat as $item)
                <tr>
                    <td>{{ $item->user->name }}</td>
                    <td>{{ $item->user->nip }}</td>
                    <td>{{ $item->leaveType->nama_cuti }}</td>
                    <td>{{ $item->tanggal_mulai->format('d-m-Y') }}</td>
                    <td>{{ $item->tanggal_selesai->format('d-m-Y') }}</td>
                    <td>{{ $item->jumlah_hari }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $item->status)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
