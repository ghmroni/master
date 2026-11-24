<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuotaService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    // public static function isExceeded(string $featureKey, int $currentTotal): bool
    public static function isExceeded(string $featureKey, callable|int $currentTotal): bool
    {
        // 1. Jika yang login SUPER ADMINISTRATOR, bebaskan dari semua batasan
        if (Auth::check() && Auth::user()->hasRole('SUPER ADMINISTRATOR')) {
            return false;
        }

        // 2. Ambil batas maksimal yang disetting SA
        $limit = DB::table('system_quotas')
            ->where('feature_key', $featureKey)
            ->value('limit_value');

        // Jika settingan tidak ditemukan, anggap batasnya 0 (terkunci)
        if ($limit === null) {
            return true;
        }

        // Jika SA mengatur -1, berarti Unlimited
        if ((int) $limit === -1) {
            return false;
        }

        // Eksekusi closure count() hanya jika memang dibutuhkan untuk pengecekan
        $total = is_callable($currentTotal) ? $currentTotal() : $currentTotal;

        // Bandingkan total data saat ini di database dengan batas maksimal
        return $total >= (int) $limit;
    }
}
