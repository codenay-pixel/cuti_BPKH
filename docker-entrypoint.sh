#!/bin/sh
set -e

echo "Running database migrations..."
php artisan migrate --force

# Idempotent: LeaveTypeSeeder & UserSeeder mencocokkan baris lama lewat
# kode/nip sebelum membuat baru, jadi aman dijalankan tiap kali container
# start (Render free tier tidak punya akses Shell untuk jalankan manual).
echo "Running database seeders..."
php artisan db:seed --force

echo "Starting server..."
exec /start.sh
