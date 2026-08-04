<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\SubBagian;

// Route PUBLIK untuk Dependent Dropdown Sub Bagian
// PENTING: Jangan gunakan middleware auth untuk route ini
Route::get('/api/v1/sub-bagians', function (Request $request) {
    // Validasi sederhana
    if (!$request->has('bagian_id')) {
        return response()->json(['error' => 'bagian_id diperlukan'], 400);
    }
    
    $subBagians = SubBagian::where('bagian_id', $request->bagian_id)
        ->select('id', 'nama_sub_bagian')
        ->orderBy('nama_sub_bagian', 'asc')
        ->get();
    
    return response()->json($subBagians);
})->withoutMiddleware(['auth:api', 'auth:sanctum']);

// Route API lainnya dengan autentikasi
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('/logout', function () {
        auth()->guard('web')->logout();
        return response()->json(['message' => 'Logged out']);
    });
});