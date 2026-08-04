<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// PENTING: Baris ini WAJIB ada agar controller mengenali method bawaan Laravel
class KalenderController extends Controller
{
    /**
     * Menampilkan halaman kalender
     */
    public function index()
    {
        // Pastikan file resources/views/kalender/index.blade.php ada, 
        // atau ganti dengan return view('dashboard') untuk sementara
        return view('kalender.index'); 
    }

    /**
     * Endpoint events (dikosongkan karena kita pakai API langsung di dashboard)
     */
    public function events(Request $request)
    {
        return response()->json([]);
    }
}