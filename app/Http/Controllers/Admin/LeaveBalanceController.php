<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{
    public function index()
    {
        $balances = LeaveBalance::with(['user', 'leaveType'])
            ->where('tahun', now()->year)
            ->latest()
            ->paginate(15);

        return view('admin.leave-balances.index', compact('balances'));
    }

    public function create()
    {
        $users = User::where('role', 'pegawai')->get();
        $leaveTypes = LeaveType::all();

        return view('admin.leave-balances.create', compact('users', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'tahun' => ['required', 'integer', 'min:2020'],
            'jatah' => ['required', 'integer', 'min:0'],
        ]);

        LeaveBalance::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'leave_type_id' => $data['leave_type_id'],
                'tahun' => $data['tahun'],
            ],
            ['jatah' => $data['jatah']]
        );

        return redirect()->route('admin.leave-balances.index')->with('success', 'Saldo cuti berhasil disimpan.');
    }

    public function edit(LeaveBalance $leaveBalance)
    {
        return view('admin.leave-balances.edit', compact('leaveBalance'));
    }

    public function update(Request $request, LeaveBalance $leaveBalance)
    {
        $data = $request->validate([
            'jatah' => ['required', 'integer', 'min:0'],
            'terpakai' => ['required', 'integer', 'min:0'],
        ]);

        $leaveBalance->update($data);

        return redirect()->route('admin.leave-balances.index')->with('success', 'Saldo cuti berhasil diperbarui.');
    }
}