<?php

namespace App\Http\Controllers\Permintaan;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\RecordOut;
use Carbon\Carbon;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;
use function Symfony\Component\Clock\now;

class PermintaanController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('reqout-karyawan'), only: ['index']),
        ];
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('id')->toArray();

        if ($request->ajax()) {
            $query = RecordOut::whereNull('deleted_at')
                ->when(auth()->user()->can('reqout-karyawan'), function ($q) use ($roles) {
                    if (in_array(7, $roles)) {
                        ## HR PUSAT
                        $q->whereIn('project_detail', [1]);
                    } else {
                        $q->whereHas('karyawan', function ($q) {
                            $q->whereHas('aksesProject', function ($q) {
                                $q->where('user_id', auth()->id());
                            });
                        });
                    }
                })
                ->where('status', 'WAITING')
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
                    $shortText = Str::limit($catatan, 35, '...'); // Potong teks untuk tampilan awal

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
                        ' . (strlen($catatan) > 35 ? '<button class="btn btn-label-success btn-xs read-more mt-1" data-bs-toggle="tooltip" 
                            data-bs-placement="top" 
                            data-bs-custom-class="tooltip-dark" 
                            title="' . e($catatan) . '" 
                            style="cursor: pointer;" data-full="' . e($catatan) . '">Hover me</button>' : '') . '
                    ';
                })
                ->addColumn('tglout', function ($row) {
                    return Carbon::parse($row['tanggal_keluar'])->format('d F Y');
                })
                ->addColumn('status', function ($row) {
                    return ($row['status'] == "SUCCESS"
                        ? '<span class="badge bg-success">Selesai</span>'
                        : '<span class="badge bg-secondary text-black">Menunggu Persetujuan</span>');
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
                            <button class="btn btn-warning btn-xs Edit" data-token=' . $token . '><i class="ti ti-pencil me-1"></i>Edit</button>
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

        return view('permintaan.req-out', [
            'judul' => 'Request Out Karyawan'
        ]);
    }

    public function store_out(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'karyawanid' => 'required',
                'statusout'  => 'required',
                'tglkeluar'  => 'required',
                'catatan'    => 'required',
            ],
            [
                'karyawanid.required' => 'Karyawan masih kosong',
                'statusout.required'  => 'Alasan Keluar masih kosong',
                'tglkeluar.required'  => 'Tanggal Keluar masih kosong',
                'catatan.required'    => 'Catatan masih kosong',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $idreq = $request->idreq;
            if (empty($idreq)) {
                $exists = RecordOut::where('karyawan_id', $request->karyawanid)
                    ->where('status', 'WAITING') // Pastikan tulisan WAITING benar (di kode Anda tadi 'WATIING')
                    ->whereNull('deleted_at')
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'errors' => 'Karyawan ini sudah pernah dilakukan pengajuan dengan status menunggu persetujuan.',
                    ], 405);
                }
            } else {
                // Cari data berdasarkan ID dan pastikan belum dihapus (Soft Delete)
                $existingData = RecordOut::where('id', $idreq)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$existingData) {
                    return response()->json([
                        'errors' => 'Data tidak ditemukan atau sudah dihapus.',
                    ], 404);
                }

                // Cek jika statusnya sudah bukan WAITING
                if ($existingData->status !== 'WAITING') {
                    return response()->json([
                        'errors' => 'Data tidak bisa diubah karena status sudah disetujui',
                    ], 405);
                }
            }

            DB::transaction(function () use ($request, $idreq) {
                $getKar = Karyawan::with('jabatan')->find($request->karyawanid)->toArray();
                $firstItem = $getKar['lokasi_penempatan'][0];
                $lastItem = end($getKar['lokasi_penempatan']);

                if (str_contains($firstItem, "GAPURA ANGKASA - ")) {
                    // 1. Ganti prefix utama
                    $stringBaru = str_replace("GAPURA ANGKASA - ", "GAPURA ANGKASA CABANG ", $firstItem);

                    // 2. Daftar mapping bandara ke kota
                    $mapKota = [
                        'SYAMSUDIN NOOR'              => 'BANJARMASIN',
                        'MINANGKABAU'                 => 'PADANG',
                        'NGURAH RAI'                  => 'BALI',
                        'SEPINGGAN'                   => 'BALIKPAPAN',
                        'SOEKARNO HATTA'              => 'CENGKARENG',
                        'SULTAN MAHMUD BADARUDDIN II' => 'PALEMBANG',
                        'SULTAN SYARIF KASIM II'      => 'PEKANBARU',
                    ];

                    $terganti = false;

                    // 3. Cek dan ganti bagian nama bandaranya
                    foreach ($mapKota as $bandara => $kota) {
                        if (str_contains($stringBaru, $bandara)) {
                            $firstItem = str_replace($bandara, $kota, $stringBaru);
                            $terganti = true;
                            break;
                        }
                    }

                    // 3. Jika TIDAK ditemukan di mapKota
                    if (!$terganti) {
                        // Ambil original firstItem (sebelum diganti prefix-nya) + lastItem
                        $firstItem = $firstItem . " (" . $lastItem . " )";
                    }
                }

                RecordOut::updateOrCreate(
                    [
                        'id' => $idreq,
                    ],
                    [
                        'karyawan_id'    => $getKar['id'],
                        'project_detail' => $getKar['project_detail'],
                        'nip'            => $getKar['nip'],
                        'nama'           => $getKar['nama'],
                        'ttl'            => Carbon::parse($getKar['tanggal_lahir'])->translatedFormat('l, d F Y'),
                        'jabatan'        => ($getKar['jabatan']['name'] ?? 'STAFF'),
                        'nama_project'   => $firstItem,
                        'tanggal_masuk'  => Carbon::parse($getKar['tanggal_masuk'])->format('Y-m-d'),
                        'tanggal_keluar' => Carbon::parse($request->tglkeluar)->format('Y-m-d'),
                        'status_out'     => $request->statusout,
                        'catatan_keluar' => $request->catatan,
                        'updated_at'     => now()
                    ]
                );
            });

            return response()->json([
                'message' => 'Pengajuan Permintaan Karyawan Keluar Berhasil Diproses.'
            ], 200);
        }
    }

    public function edit_requestout(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;
            $data = RecordOut::whereNull('deleted_at')->where('status', 'WAITING')->find($id);
            if ($data) {
                return response()->json($data);
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

    public function hapus_requestout(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $data = RecordOut::whereNull('deleted_at')->where('status', 'WAITING')->find($id);
            if ($data) {
                $data->update([
                    'user_id' => auth()->user()->id
                ]);
                $data->delete();

                return response()->json([
                    'message' => 'Pengajuan Permintaan Karyawan Keluar Berhasil Dibatalkan.'
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
