<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $setting = DB::table('system_settings')->first();

        $daysLeft = null;
        $alertMessage = null;
        $alertType = 'warning'; // Default warna kuning

        if ($setting && $setting->expired_at) {
            $expiredDate = Carbon::parse($setting->expired_at);
            $daysLeft = floor(Carbon::now()->diffInDays($expiredDate, false)); // false agar bisa minus jika sudah lewat

            if ($daysLeft <= 0) {
                $alertMessage = "Masa aktif aplikasi telah <b>berakhir</b>. Fitur operasional telah dikunci. Silakan hubungi Super Administrator.";
                $alertType = 'danger'; // Warna merah
            } elseif ($daysLeft <= 60) { // 60 hari = 2 bulan
                $alertMessage = "Masa aktif aplikasi Anda akan berakhir dalam <b>{$daysLeft} hari</b> lagi. Segera lakukan perpanjangan untuk menghindari pemblokiran sistem.";
            }
        }

        $user = Auth::user();
        $roleId = $user->roles->pluck('id')->first(); // ambil id role pertama

        $views = [
            1  => 'dashboard.dash-super',
            2  => 'dashboard.dash-admin',
            3  => 'dashboard.dash-recruitment',
            4 => 'dashboard.dash-bpjs',
        ];

        $view = $views[$roleId] ?? 'dashboard.dash-guest';

        return view($view, [
            'judul' => 'Halaman Awal'
        ], compact('alertMessage', 'alertType'));
    }

    public function activeSessions(Request $request)
    {
        if ($request->ajax()) {
            $activeUsers = DB::table('sessions')
                ->join('users', 'sessions.user_id', '=', 'users.id')
                ->leftJoin('model_has_roles', function ($join) {
                    $join->on('users.id', '=', 'model_has_roles.model_id')
                        ->where('model_has_roles.model_type', '=', 'App\Models\User');
                })
                ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->whereNotNull('sessions.user_id')
                ->when(!Auth::user()->hasRole('SUPER ADMINISTRATOR'), function ($query) {
                    $query->whereNotIn('users.id', function ($subQuery) {
                        $subQuery->select('model_id')
                            ->from('model_has_roles')
                            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                            ->where('roles.name', 'SUPER ADMINISTRATOR')
                            ->where('model_has_roles.model_type', 'App\Models\User');
                    });
                })
                ->where('sessions.last_activity', '>', now()->subMinutes(120)->getTimestamp())
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.image',
                    'sessions.ip_address',
                    'sessions.last_activity',
                    'sessions.login_at',
                    DB::raw('GROUP_CONCAT(DISTINCT roles.name SEPARATOR ", ") as role_name')
                )
                ->groupBy(
                    'users.id',
                    'users.name',
                    'users.email',
                    'sessions.ip_address',
                    'sessions.last_activity',
                    'users.image',
                    'sessions.login_at',
                )
                ->orderBy('users.name', 'asc')
                ->get()
                ->map(function ($user) {
                    $user->last_activity_human = $user->login_at
                        ? Carbon::parse($user->login_at)->diffForHumans()
                        : 'Baru saja';

                    return $user;
                });

            return response()->json($activeUsers);
        }
    }

    public function streamActiveSessions()
    {
        $response = new StreamedResponse(function () {
            while (true) {
                $activeUsers = DB::table('sessions')
                    ->join('users', 'sessions.user_id', '=', 'users.id')
                    ->leftJoin('model_has_roles', function ($join) {
                        $join->on('users.id', '=', 'model_has_roles.model_id')
                            ->where('model_has_roles.model_type', '=', 'App\Models\User');
                    })
                    ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->whereNotNull('sessions.user_id')
                    ->when(!Auth::user()->hasRole('SUPER ADMINISTRATOR'), function ($query) {
                        $query->whereNotIn('users.id', function ($subQuery) {
                            $subQuery->select('model_id')
                                ->from('model_has_roles')
                                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                                ->where('roles.name', 'SUPER ADMINISTRATOR')
                                ->where('model_has_roles.model_type', 'App\Models\User');
                        });
                    })
                    ->where('sessions.last_activity', '>', now()->subMinutes(120)->getTimestamp())
                    ->select(
                        'users.id',
                        'users.name',
                        'users.email',
                        'users.image',
                        'sessions.ip_address',
                        'sessions.last_activity',
                        'sessions.login_at',
                        DB::raw('GROUP_CONCAT(DISTINCT roles.name SEPARATOR ", ") as role_name')
                    )
                    ->groupBy(
                        'users.id',
                        'users.name',
                        'users.email',
                        'sessions.ip_address',
                        'sessions.last_activity',
                        'users.image',
                        'sessions.login_at',
                    )
                    ->orderBy('users.name', 'asc')
                    ->get()
                    ->map(function ($user) {
                        $user->last_activity_human = $user->login_at
                            ? Carbon::parse($user->login_at)->diffForHumans()
                            : 'Baru saja';

                        return $user;
                    });

                // Kirim data dengan format SSE
                echo "data: " . json_encode($activeUsers) . "\n\n";

                // Flush buffer agar data langsung terkirim
                ob_flush();
                flush();

                // Tunggu 5 detik sebelum mengirim data berikutnya
                sleep(5);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');

        return $response;
    }
}
