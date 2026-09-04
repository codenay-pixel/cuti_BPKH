<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOfficeEventRequest;
use App\Models\OfficeEvent;
use Illuminate\Support\Facades\Storage;

class OfficeEventController extends Controller
{
    public function store(StoreOfficeEventRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('surat-dinas', 'public');
        }

        $data['user_id'] = $request->user()->id;

        OfficeEvent::create($data);

        return redirect()
            ->route('calendar.index', [
                'bulan' => date('n', strtotime($data['tanggal_mulai'])),
                'tahun' => date('Y', strtotime($data['tanggal_mulai'])),
            ])
            ->with('success', 'Acara berhasil ditambahkan ke kalender kantor.');
    }

    public function destroy(OfficeEvent $officeEvent)
    {

        abort_unless(
            $officeEvent->user_id === auth()->id() || auth()->user()->isAdmin(),
            403,
            'Anda hanya dapat menghapus acara yang Anda buat sendiri.'
        );

        if ($officeEvent->lampiran) {
            Storage::disk('public')->delete($officeEvent->lampiran);
        }

        $officeEvent->delete();

        return back()->with('success', 'Acara berhasil dihapus.');
    }
}
