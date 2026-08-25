#!/bin/sh
set -e

echo "Running database migrations..."
php artisan migrate --force

# Idempotent: LeaveTypeSeeder & UserSeeder mencocokkan baris lama lewat
# kode/nip sebelum membuat baru, jadi aman dijalankan tiap kali container
# start (Render free tier tidak punya akses Shell untuk jalankan manual).
echo "Running database seeders..."
php artisan db:seed --force

# --- DIAGNOSTIK SEMENTARA: hapus blok ini setelah masalah login ketemu ---
echo "=== DIAGNOSTIK KONEKSI DATABASE ==="
php artisan db:show --counts 2>&1 || true
echo "=== DIAGNOSTIK KONEKSI DATABASE SELESAI ==="

echo "=== DIAGNOSTIK LOGIN (SEMUA AKUN) ==="
php artisan tinker --execute='
$nips = ["198001012000011001", "197505052001121001", "198202022005011002", "199003032010012003"];
foreach ($nips as $nip) {
    $u = \App\Models\User::where("nip", $nip)->first();
    if (! $u) {
        echo "[$nip] TIDAK DITEMUKAN" . PHP_EOL;
        continue;
    }
    $cocok = \Illuminate\Support\Facades\Hash::check("password123", $u->password) ? "COCOK" : "TIDAK COCOK";
    echo "[$nip] role=" . $u->role . " id=" . $u->id . " atasan_id=" . ($u->atasan_id ?? "null") . " hash_len=" . strlen($u->password) . " cek=" . $cocok . PHP_EOL;
}
' 2>&1 || true
echo "=== DIAGNOSTIK LOGIN SELESAI ==="

echo "Starting server..."
exec /start.sh
