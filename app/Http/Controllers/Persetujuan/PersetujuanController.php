<?php

namespace App\Http\Controllers\Persetujuan;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Kontrak;
use App\Models\Pelamar;
use App\Models\Project;
use App\Models\RecordOut;
use Carbon\Carbon;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;

use function Symfony\Component\Clock\now;

class PersetujuanController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('approve-karyawan-out'), only: ['index', 'store_out']),
        ];
    }

    public function index(Request $request)
    {

        if ($request->ajax()) {

            $akses = auth()->user()->getProjectAccess(); // ambil child project IDs
            $rootIds = Project::whereIn('id', $akses)
                ->get()
                ->map(fn($p) => $p->businessRoot()->id)
                ->unique()
                ->values();

            $query = RecordOut::whereNull('deleted_at')->where('status', 'WAITING')
                // ## HO dan TKO
                // ->when(auth()->user()->can('approve-karyawan-out-ho') && auth()->user()->can('approve-karyawan-out-tko'), function ($q) {
                //     $q->whereIn('project_detail', [1]) // HO
                //         ->orWhere('project_detail', '!=', 1); // TKO

                // })
                // ## HO
                // ->when(auth()->user()->can('approve-karyawan-out-ho') && !auth()->user()->can('approve-karyawan-out-tko'), function ($q) {
                //     $q->whereIn('project_detail', [1]);
                // })
                // ##TKO
                // ->when(!auth()->user()->can('approve-karyawan-out-ho') && auth()->user()->can('approve-karyawan-out-tko'), function ($q) {
                //     $q->whereNotIn('project_detail', [1]);
                // })
                ->when(auth()->user()->can('approve-karyawan-out'), function ($q) use ($rootIds) {
                    $q->whereIn('project_detail', $rootIds);
                })
                ->orderByRaw("CASE WHEN status = 'WAITING' THEN 1 ELSE 2 END")
                ->orderBy('id', 'DESC')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('lokasi', function ($row) {
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];
                    $lokasi = array_map(
                        fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                        $row['lokasi_pekerjaan'],
                        array_slice($color, 0, count($row['lokasi_pekerjaan']))
                    );
                    return implode(' ', $lokasi);
                })
                ->addColumn('catatan', function ($row) {
                    $catatan = $row['catatan_keluar'];
                    $shortText = Str::limit($catatan, 40, '...'); // Potong teks untuk tampilan awal

                    if ($row['status_out'] == "RESIGN") {
                        $color = 'primary';
                    } else if ($row['status_out'] == "DIPULANGKAN") {
                        $color = 'dark';
                    } else if ($row['status_out'] == "HABIS KONTRAK") {
                        $color = 'danger';
                    } else {
                        $color = 'warning';
                    }

                    return '
                        <span class="badge bg-label-' . $color . '">' . $row['status_out'] . '</span>
                        <span class="catatan-text d-flex mt-1">
                            ' . $shortText . '
                        </span>
                        ' . (strlen($catatan) > 40 ? '<button class="btn btn-label-success btn-xs read-more mt-1" data-bs-toggle="tooltip" 
                            data-bs-placement="top" 
                            data-bs-custom-class="tooltip-dark" 
                            title="' . e($catatan) . '" 
                            style="cursor: pointer;" data-full="' . e($catatan) . '">Hover me</button>' : '') . '
                    ';
                })
                ->addColumn('tglout', function ($row) {
                    return Carbon::parse($row['tanggal_keluar'])->format('d F Y');
                })
                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');
                    return ($row['status'] == "SUCCESS"
                        ? '<small class="badge text-muted">Disetujui pada :<br>' . Carbon::parse($row['updated_at'])->format('d-m-Y H:i:s') . '</small>'
                        : '
                        <div class="btn-group">
                            <button class="btn btn-primary btn-xs Approval" data-token=' . $token . '><i class="fas fa-user-times me-1"></i>Proses</button>
                            <button class="btn btn-danger btn-xs Hapus" data-token=' . $token . '><i class="ti ti-trash me-1"></i>Batal</button>
                        </div>
                        ');
                })
                ->rawColumns([
                    'catatan' => 'catatan',
                    'lokasi'  => 'lokasi',
                    'tglout'  => 'tglout',
                    'status'  => 'status',
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('persetujuan.approval-out', [
            'judul' => 'Approval Karyawan Keluar',
        ]);
    }

    public function store_out(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $data = RecordOut::with('karyawan.kontrakAktif.statuskontrak')
                ->whereNull('deleted_at')
                ->where('status', 'WAITING')
                ->find($id);

            if ($data) {
                DB::transaction(function () use ($data) {
                    $idkontrak = $data->karyawan->kontrakAktif->id;
                    $idkaryawan = $data->karyawan_id;
                    $idpelamar = $data->karyawan->pelamar_id;

                    $tgl = Carbon::parse($data->tanggal_keluar);
                    $month = $tgl->format('m');
                    $years = $tgl->year;

                    $romawi = [
                        '01' => 'I',
                        '02' => 'II',
                        '03' => 'III',
                        '04' => 'IV',
                        '05' => 'V',
                        '06' => 'VI',
                        '07' => 'VII',
                        '08' => 'VIII',
                        '09' => 'IX',
                        '10' => 'X',
                        '11' => 'XI',
                        '12' => 'XII',
                    ];

                    $count = RecordOut::where('status', 'COMPLETE')->whereNull('deleted_at')->count();
                    $nomorUrut = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

                    $dtproject = explode(',', $data->project_detail);
                    $project = Project::where('id', $dtproject[0])
                        ->select('id', 'inisial')
                        ->first();

                    # Update Record Out
                    $data->update([
                        'nomor_surat' => $nomorUrut . "/GHM/" . $project->inisial . "/" . $romawi[$month] . "/" . $years,
                        'status'      => 'SUCCESS',
                        'lokasi_ttd'  => ($data->karyawan->kontrakAktif->lokasi_kontrak ?? 'BEKASI'),
                        'user_id'     => auth()->user()->id,
                        'barcode'     => "Kontrak" . $data->karyawan->kontrakAktif->statuskontrak->name . ":" . $data->karyawan->kontrakAktif->nomor_kontrak . ";Paklaring:" . $nomorUrut . "/GHM/" . $project->inisial . "/" . $romawi[$month] . "/" . $years . ";ApprovalDate:" . now()->format('Y-m-d H:i:s') . ";isValid",
                        'updated_at'  => now(),
                    ]);

                    # Update Kontrak
                    Kontrak::where('id', $idkontrak)
                        ->update([
                            'status_kontrak' => 'TIDAK AKTIF',
                            'updated_at'     => now(),
                        ]);

                    # Update Karyawan
                    Karyawan::where('id', $idkaryawan)->delete();

                    # Update Pelamar
                    Pelamar::where('id_pelamar', $idpelamar)
                        ->update([
                            'handphone'    => (!str_starts_with($data->karyawan->handphone, '0') ? '0' . $data->karyawan->handphone : $data->karyawan->handphone),
                            'status_kawin' => $data->karyawan->statuskawin_id,
                            'status'       => 'WAITING',
                            'updated_at'   => now(),
                        ]);
                });

                return response()->json([
                    'message' => 'Permintaan Karyawan Keluar Berhasil Diproses.'
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Tidak ditemukan.'
                ], 403);
            }
        } catch (ExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi Anda telah habis atau token tidak valid.'
            ], 403); // 401 Unauthorized
        }
    }
}
