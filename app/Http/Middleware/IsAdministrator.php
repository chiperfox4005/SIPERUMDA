<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdministrator
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->bagian || !$user->subBagian) {
            abort(403, 'Akses ditolak. Anda bukan Administrator.');
        }

        $namaBagian = strtolower(trim($user->bagian->nama_bagian));
        $namaSubBagian = strtolower(trim($user->subBagian->nama_sub_bagian));

        $isLitbang = str_contains($namaBagian, 'litbang');
        $isPTI = str_contains($namaSubBagian, 'pengembangan teknologi informatika') || str_contains($namaSubBagian, 'pti');

        if (!($isLitbang && $isPTI)) {
            abort(403, 'Akses ditolak. Hanya Bidang Litbang - Pengembangan Teknologi Informatika yang berhak.');
        }

        return $next($request);
    }
}