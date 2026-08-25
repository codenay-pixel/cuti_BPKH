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

echo "=== DIAGNOSTIK LOGIN ==="
php artisan tinker --execute='
$u = \App\Models\User::where("nip", "199003032010012003")->first();
echo "User ditemukan: " . ($u ? "YA" : "TIDAK") . PHP_EOL;
if ($u) {
    echo "NIP tersimpan : [" . $u->nip . "]" . PHP_EOL;
    echo "Role          : " . $u->role . PHP_EOL;
    echo "Password hash : " . $u->password . PHP_EOL;
    echo "Panjang hash  : " . strlen($u->password) . PHP_EOL;
    echo "Hash::check   : " . (\Illuminate\Support\Facades\Hash::check("password123", $u->password) ? "COCOK" : "TIDAK COCOK") . PHP_EOL;
}
echo "Total user di tabel: " . \App\Models\User::count() . PHP_EOL;
echo "Semua NIP: " . \App\Models\User::pluck("nip")->implode(", ") . PHP_EOL;
' 2>&1 || true
echo "=== DIAGNOSTIK LOGIN SELESAI ==="

echo "Starting server..."
exec /start.sh
