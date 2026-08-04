<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KalenderLiburController extends Controller
{
    public function events(Request $request)
    {
        $year = $request->get('year', now()->year);
        $url = "https://libur.deno.dev/api?year={$year}";
        
        try {
            $response = Http::timeout(10)->withoutVerifying()->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                $events = [];
                
                if (is_array($data)) {
                    foreach ($data as $libur) {
                        // Filter hanya hari libur nasional (berdasarkan JSON yang Anda kirim)
                        $isNationalHoliday = $libur['is_national_holiday'] ?? true;
                        
                        if ($isNationalHoliday && isset($libur['date']) && isset($libur['name'])) {
                            $events[] = [
                                'title' => $libur['name'],
                                'start' => $libur['date'],
                                'allDay' => true,
                                'backgroundColor' => '#dc2626', // MERAH untuk libur
                                'borderColor' => '#dc2626',
                                'textColor' => '#ffffff',
                                'extendedProps' => [
                                    'description' => $libur['name'],
                                    'type' => 'libur_nasional'
                                ]
                            ];
                        }
                    }
                }
                
                return response()->json($events);
            } else {
                // Jika API mengembalikan error (misal 404 atau 500), kita catat di log
                Log::error('API Libur Gagal: ' . $response->status() . ' - ' . $response->body());
                return response()->json(['error' => 'API mengembalikan status: ' . $response->status()], 500);
            }
            
        } catch (\Exception $e) {
            // Jika terjadi exception (misal tidak ada internet), catat di log
            Log::error('Exception API Libur: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menghubungi API: ' . $e->getMessage()], 500);
        }
    }
}