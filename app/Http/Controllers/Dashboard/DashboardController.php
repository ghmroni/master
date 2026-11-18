<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Kontrak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $roleId = $user->roles->pluck('id')->first(); // ambil id role pertama

        $views = [
            1  => 'dashboard.dash-admin',
            7  => 'dashboard.dash-hr',
            8  => 'dashboard.dash-hr',
            9  => 'dashboard.dash-recruitment',
            10 => 'dashboard.dash-legal',
            11 => 'dashboard.dash-bpjs',
        ];

        $view = $views[$roleId] ?? 'dashboard.dash-guest';

        return view($view, ['judul' => 'Halaman Awal']);
    }

    public function cetak_pkwt(Request $request)
    {
        $akses = Auth::user()->getProjectAccess();
        ## Query Total Karyawan
        $total = Karyawan::whereNull('deleted_at')
            ->whereNull('tanggal_keluar')
            ->whereIn('project_detail', $akses)
            ->count();

        ## Query Belum Cetak
        $belumCetak = Kontrak::whereNull('deleted_at')
            ->whereNull('pkwt_id')
            ->where('status_kontrak', 'AKTIF')
            ->whereHas('karyawan', function ($q) use ($akses) {
                $q->whereNull('deleted_at')->whereNull('tanggal_keluar')->whereIn('project_detail', $akses);
            })
            ->count();

        # ini persentase yang belum cetak
        $persentaseSudah = $total > 0
            ? round(($belumCetak / $total) * 100)
            : 0;

        # persentase yang sudah cetak
        $persentase = $total > 0
            ? round((($total - $belumCetak) / $total) * 100)
            : 0; // jika belum ada data agar persentase 100%, (opsional bisa juga dikasih nilai 0)

        if ($request->ajax()) {
            return response()->json(['presentasi' => $persentase], 200);
        }
    }
}
