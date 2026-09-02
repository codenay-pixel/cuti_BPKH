<?php

namespace App\Exports;

use App\Models\LeaveRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LeaveReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $tahun;
    protected $status;
    protected $nama;

    public function __construct($tahun, $status = null, $nama = null)
    {
        $this->tahun = $tahun;
        $this->status = $status;
        $this->nama = $nama;
    }

    public function collection()
    {
        $query = LeaveRequest::with(['user', 'leaveType'])
            ->whereYear('tanggal_mulai', $this->tahun)
            ->latest();

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->nama) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->nama . '%');
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['Nama Pegawai', 'NIP', 'Jenis Cuti', 'Tanggal Mulai', 'Tanggal Selesai', 'Jumlah Hari', 'Alasan', 'Status'];
    }

    public function map($leaveRequest): array
    {
        return [
            $leaveRequest->user->name,
            (string) $leaveRequest->user->nip,
            $leaveRequest->leaveType->nama_cuti,
            $leaveRequest->tanggal_mulai->format('d-m-Y'),
            $leaveRequest->tanggal_selesai->format('d-m-Y'),
            $leaveRequest->jumlah_hari,
            $leaveRequest->alasan,
            ucfirst(str_replace('_', ' ', $leaveRequest->status)),
        ];
    }
}
