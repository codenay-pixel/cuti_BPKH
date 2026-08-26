#!/bin/sh
set -e

# Reset sekali-pakai: kalau database sempat kepakai deploy lama (skema/constraint
# lama tercatat "selesai" di tabel migrations padahal isi migration-nya sudah
# diperbaiki), set FORCE_FRESH_MIGRATE=true di environment variable Render lalu
# redeploy -- ini akan DROP semua tabel dan bangun ulang dari nol. Setelah itu
# HAPUS/matikan lagi variable ini dan redeploy sekali lagi, supaya restart
# berikutnya kembali pakai migrate biasa (tidak menghapus data).
if [ "$FORCE_FRESH_MIGRATE" = "true" ]; then
  echo "!! FORCE_FRESH_MIGRATE aktif -- reset total database lalu migrate dari nol !!"
  php artisan migrate:fresh --force
else
  echo "Running database migrations..."
  php artisan migrate --force
fi

# Idempotent: LeaveTypeSeeder & UserSeeder mencocokkan baris lama lewat
# kode/nip sebelum membuat baru, jadi aman dijalankan tiap kali container
# start (Render free tier tidak punya akses Shell untuk jalankan manual).
echo "Running database seeders..."
php artisan db:seed --force

# Snapshot data ke tabel system_backups (dibatasi maks sekali per ~20 jam
# oleh command-nya sendiri). Dipasang di sini -- bukan cron sungguhan --
# karena container ini tidak menjalankan proses scheduler terus-menerus.
# Sengaja tidak menggagalkan startup kalau backup ini error (|| true).
echo "Running backup snapshot..."
php artisan backup:snapshot || true

echo "Starting server..."
exec /start.sh
