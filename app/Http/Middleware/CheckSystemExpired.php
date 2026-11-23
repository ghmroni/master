<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckSystemExpired
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        # Bebaskan SUPER ADMINISTRATOR
        if (Auth::check() && Auth::user()->hasRole('SUPER ADMINISTRATOR')) {
            return $next($request);
        }

        # Ambil pengaturan masa aktif dari database
        $setting = DB::table('system_settings')->first();

        # JIKA SETTING KOSONG: Blokir akses atau anggap expired
        if (!$setting) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Pengaturan sistem tidak ditemukan.'], 403);
            }
            return redirect()->route('system.expired');
        }

        # Cek apakah tanggal hari ini sudah melewati batas akhir
        $isExpired = $setting->expired_at && Carbon::now()->gt(Carbon::parse($setting->expired_at)->endOfDay());

        # Jika dikunci manual ATAU tanggalnya sudah lewat
        if ($setting->is_suspended || $isExpired) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Masa aktif penggunaan aplikasi telah berakhir. Silakan hubungi Super Administrator.'
                ], 403);
            }
            return redirect()->route('system.expired');
        }

        return $next($request);
    }
}
