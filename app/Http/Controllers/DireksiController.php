<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DireksiController extends Controller
{
    public function index()
    {
        // 1. Ambil 5 Agenda Mendatang (dari hari ini ke depan)
        $upcomingAgendas = Agenda::with(['creator', 'ruangan'])
            ->where('tanggal_mulai', '>=', now()->format('Y-m-d'))
            ->orderBy('tanggal_mulai', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->take(5)
            ->get();

        // 2. Ambil 5 Pengumuman Terbaru yang Publish
        $latestAnnouncements = Pengumuman::where('status', 'publish')
            ->whereDate('tanggal_publish', '<=', now())
            ->where(function ($q) {
                $q->whereNull('tanggal_berakhir')
                  ->orWhereDate('tanggal_berakhir', '>=', now());
            })
            ->orderBy('tanggal_publish', 'desc')
            ->take(5)
            ->get();

        return view('direksi.dashboard', compact('upcomingAgendas', 'latestAnnouncements'));
    }
}