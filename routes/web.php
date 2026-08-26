<?php

use App\Http\Controllers\Admin\LeaveBalanceController;
use App\Http\Controllers\Admin\LeaveReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Atasan\ApprovalController as AtasanApprovalController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KepalaBalai\ApprovalController as KepalaBalaiApprovalController;
use App\Http\Controllers\OfficeEventController;
use App\Http\Controllers\Pegawai\LeaveRequestController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Kalender kantor: semua peran boleh melihat dan menambah acara
    Route::get('/kalender', [CalendarController::class, 'index'])->name('calendar.index');
    Route::post('/kalender/acara', [OfficeEventController::class, 'store'])->name('events.store');
    Route::delete('/kalender/acara/{officeEvent}', [OfficeEventController::class, 'destroy'])->name('events.destroy');

    // Pengajuan cuti: semua peran boleh mengajukan cuti untuk dirinya sendiri
    Route::prefix('cuti')->name('leave.')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
        Route::get('/ajukan', [LeaveRequestController::class, 'create'])->name('create');
        Route::post('/ajukan', [LeaveRequestController::class, 'store'])->name('store');
        Route::get('/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('show');
        Route::get('/{leaveRequest}/ubah', [LeaveRequestController::class, 'edit'])->name('edit');
        Route::put('/{leaveRequest}', [LeaveRequestController::class, 'update'])->name('update');
        Route::get('/{leaveRequest}/cetak', [LeaveRequestController::class, 'cetak'])->name('cetak');
        Route::delete('/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('destroy');
    });
});

// Persetujuan tingkat 1: Atasan Langsung
Route::middleware(['auth', 'role:atasan_langsung'])->prefix('approval')->name('approval.')->group(function () {
    Route::get('/', [AtasanApprovalController::class, 'index'])->name('index');
    Route::post('/{leaveRequest}/setujui', [AtasanApprovalController::class, 'approve'])->name('approve');
    Route::post('/{leaveRequest}/tolak', [AtasanApprovalController::class, 'reject'])->name('reject');
});

// Persetujuan tingkat 2: Kepala Balai (pejabat pemberi cuti)
Route::middleware(['auth', 'role:atasan'])->prefix('kepala-balai/approval')->name('kepala-balai.approval.')->group(function () {
    Route::get('/', [KepalaBalaiApprovalController::class, 'index'])->name('index');
    Route::post('/{leaveRequest}/setujui', [KepalaBalaiApprovalController::class, 'approve'])->name('approve');
    Route::post('/{leaveRequest}/tolak', [KepalaBalaiApprovalController::class, 'reject'])->name('reject');
});

// Admin kepegawaian
// Catatan: menu "Jenis Cuti" dihapus. 6 jenis cuti dikunci lewat LeaveTypeSeeder.
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except('show');
    // Saldo cuti: satu pegawai satu baris, tiga tahun diatur sekaligus
    Route::get('saldo-cuti', [LeaveBalanceController::class, 'index'])->name('leave-balances.index');
    Route::get('saldo-cuti/{user}', [LeaveBalanceController::class, 'edit'])->name('leave-balances.edit');
    Route::put('saldo-cuti/{user}', [LeaveBalanceController::class, 'update'])->name('leave-balances.update');
    Route::get('reports', [LeaveReportController::class, 'index'])->name('reports.index');
    Route::delete('reports/{leaveRequest}', [LeaveReportController::class, 'destroy'])->name('reports.destroy');
    Route::get('reports/export-excel', [LeaveReportController::class, 'exportExcel'])->name('reports.export-excel');
    Route::get('reports/export-pdf', [LeaveReportController::class, 'exportPdf'])->name('reports.export-pdf');
});

require __DIR__ . '/auth.php';

// Unduh snapshot backup data terakhir (lihat App\Console\Commands\BackupSnapshot).
// Sengaja di luar middleware 'auth' -- diamankan lewat token acak di
// BACKUP_TOKEN, supaya bisa diunduh manual kapan saja tanpa perlu login.
Route::get('/system/backup/{token}', function (string $token) {
    $expected = (string) config('app.backup_token');

    if ($expected === '' || ! hash_equals($expected, $token)) {
        abort(404);
    }

    $backup = \Illuminate\Support\Facades\DB::table('system_backups')
        ->orderByDesc('created_at')
        ->first();

    if (! $backup) {
        abort(404, 'Belum ada snapshot backup.');
    }

    $filename = 'backup-cuti-bpkh-' . \Illuminate\Support\Str::of($backup->created_at)->replace([' ', ':'], '-') . '.json';

    return response($backup->payload, 200, [
        'Content-Type' => 'application/json',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
})->name('system.backup.latest');
