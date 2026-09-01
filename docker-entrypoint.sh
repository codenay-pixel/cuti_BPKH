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

# Seeder TIDAK dijalankan otomatis setiap start -- ini yang tadinya bikin
# container crash-loop (duplicate key) dan berisiko menimpa ulang data yang
# sudah diedit admin lewat UI. Set RUN_SEED=true di environment variable
# Render lalu redeploy HANYA kalau memang sengaja mau seed (misal setup awal
# database baru / fresh). Setelah itu HAPUS/matikan lagi variable ini dan
# redeploy sekali lagi.
if [ "$RUN_SEED" = "true" ]; then
  echo "!! RUN_SEED aktif -- menjalankan seeder !!"
  php artisan db:seed --force
fi

# Snapshot data ke tabel system_backups (dibatasi maks sekali per ~20 jam
# oleh command-nya sendiri). Dipasang di sini -- bukan cron sungguhan --
# karena container ini tidak menjalankan proses scheduler terus-menerus.
# Sengaja tidak menggagalkan startup kalau backup ini error (|| true).
echo "Running backup snapshot..."
php artisan backup:snapshot || true

echo "Starting server..."
exec /start.sh
