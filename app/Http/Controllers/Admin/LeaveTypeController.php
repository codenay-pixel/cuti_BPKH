<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::latest()->get();

        return view('admin.leave-types.index', compact('leaveTypes'));
    }

    public function create()
    {
        return view('admin.leave-types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_cuti' => ['required', 'string', 'max:255'],
            'jatah_hari_default' => ['required', 'integer', 'min:0'],
            'perlu_lampiran' => ['nullable', 'boolean'],
            'mengurangi_saldo' => ['nullable', 'boolean'],
        ]);

        $data['perlu_lampiran'] = $request->boolean('perlu_lampiran');
        $data['mengurangi_saldo'] = $request->boolean('mengurangi_saldo');

        LeaveType::create($data);

        return redirect()->route('admin.leave-types.index')->with('success', 'Jenis cuti berhasil ditambahkan.');
    }

    public function edit(LeaveType $leaveType)
    {
        return view('admin.leave-types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $data = $request->validate([
            'nama_cuti' => ['required', 'string', 'max:255'],
            'jatah_hari_default' => ['required', 'integer', 'min:0'],
            'perlu_lampiran' => ['nullable', 'boolean'],
            'mengurangi_saldo' => ['nullable', 'boolean'],
        ]);

        $data['perlu_lampiran'] = $request->boolean('perlu_lampiran');
        $data['mengurangi_saldo'] = $request->boolean('mengurangi_saldo');

        $leaveType->update($data);

        return redirect()->route('admin.leave-types.index')->with('success', 'Jenis cuti berhasil diperbarui.');
    }

    public function destroy(LeaveType $leaveType)
    {
        $leaveType->delete();

        return redirect()->route('admin.leave-types.index')->with('success', 'Jenis cuti berhasil dihapus.');
    }
}