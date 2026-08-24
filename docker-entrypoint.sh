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

echo "Starting server..."
exec /start.sh
