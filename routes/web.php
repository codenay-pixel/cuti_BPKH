<?php

use App\Http\Controllers\KepalaBalai\ApprovalController as KepalaBalaiApprovalController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Atasan\ApprovalController as AtasanApprovalController;
use App\Http\Controllers\Pegawai\LeaveRequestController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LeaveBalanceController;
use App\Http\Controllers\Admin\LeaveTypeController;
use App\Http\Controllers\Admin\LeaveReportController;
use App\Http\Controllers\CalendarController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/kalender', [CalendarController::class, 'index'])->name('calendar.index');

    Route::prefix('cuti')->name('leave.')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])->name('index');
        Route::get('/ajukan', [LeaveRequestController::class, 'create'])->name('create');
        Route::post('/ajukan', [LeaveRequestController::class, 'store'])->name('store');
        Route::get('/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('show');
    });
});

Route::middleware(['auth', 'role:atasan_langsung'])->prefix('approval')->name('approval.')->group(function () {
    Route::get('/', [AtasanApprovalController::class, 'index'])->name('index');
    Route::post('/{leaveRequest}/setujui', [AtasanApprovalController::class, 'approve'])->name('approve');
    Route::post('/{leaveRequest}/tolak', [AtasanApprovalController::class, 'reject'])->name('reject');
});

Route::middleware(['auth', 'role:atasan'])->prefix('kepala-balai/approval')->name('kepala-balai.approval.')->group(function () {
    Route::get('/', [KepalaBalaiApprovalController::class, 'index'])->name('index');
    Route::post('/{leaveRequest}/setujui', [KepalaBalaiApprovalController::class, 'approve'])->name('approve');
    Route::post('/{leaveRequest}/tolak', [KepalaBalaiApprovalController::class, 'reject'])->name('reject');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('leave-types', LeaveTypeController::class);
    Route::resource('leave-balances', LeaveBalanceController::class)->except('show');
    Route::get('reports', [LeaveReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export-excel', [LeaveReportController::class, 'exportExcel'])->name('reports.export-excel');
    Route::get('reports/export-pdf', [LeaveReportController::class, 'exportPdf'])->name('reports.export-pdf');
});

require __DIR__.'/auth.php';