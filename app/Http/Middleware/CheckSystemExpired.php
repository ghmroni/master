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
        # Bebaskan SUPER ADMINISTRATOR agar tetap bisa login & memperpanjang masa aktif
        if (Auth::check() && Auth::user()->hasRole('SUPER ADMINISTRATOR')) {
            return $next($request);
        }

        # Ambil pengaturan masa aktif dari database
        $setting = DB::table('system_settings')->first();

        if ($setting) {
            # Cek apakah tanggal hari ini sudah melewati batas akhir (pukul 23:59:59 pada tanggal expired_at)
            $isExpired = $setting->expired_at && Carbon::now()->gt(Carbon::parse($setting->expired_at)->endOfDay());

            # Jika dikunci manual oleh SA ATAU tanggalnya sudah lewat
            if ($setting->is_suspended || $isExpired) {

                # Jika request berasal dari AJAX / jQuery
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Masa aktif penggunaan aplikasi telah berakhir. Silakan hubungi Super Administrator.'
                    ], 403);
                }

                # Jika akses halaman web biasa, arahkan ke halaman informasi expired
                return redirect()->route('system.expired');
            }
        }
        return $next($request);
    }
}
