<?php

namespace App\Http\Controllers\Pelamar;

use App\Http\Controllers\Controller;
use App\Models\Keluarga;
use App\Models\Pelamar;
use App\Models\PendidikanNonformal;
use App\Models\PengalamanKerja;
use App\Models\SertifikatPelamar;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;

class PelamarController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('lihat-pelamar'), only: ['index']),
            // new Middleware(PermissionMiddleware::using('edit-project|tambah-project'), only: ['store_project']),
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Pelamar::with('lowonganApply.pemanggilan')
                ->orderByRaw("
                    CASE status
                        WHEN 'WAITING' THEN 1
                        WHEN 'PROGRESS' THEN 2
                        WHEN 'COMPLETE' THEN 3
                        ELSE 4
                    END
                ")
                ->orderBy('id_pelamar', 'DESC')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('status', function ($row) {
                    if ($row['status'] == 'WAITING') {
                        $status = '<span class="badge rounded-pill bg-label-primary">' . $row['status'] . '</span>';
                    } else if ($row['status'] == 'PROGRESS') {
                        $status = '<span class="badge rounded-pill bg-label-warning">' . $row['status'] . '</span>';
                    } else if ($row['status'] == 'COMPLETE') {
                        $status = '<span class="badge rounded-pill bg-label-success">' . $row['status'] . '</span>';
                    } else {
                        $status = '<span class="badge rounded-pill bg-label-secondary">' . $row['status'] . '</span>';
                    }

                    return $status;
                })

                ->addColumn('usia', function ($row) {
                    $tglLahir = Carbon::parse($row['tgllahir']);
                    $usia = $tglLahir->age; // otomatis hitung umur dari tanggal lahir

                    return $usia . ' tahun';
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id' => $row['id_pelamar'],
                        'exp' => time() + 3600 // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<div class="btn-group">';
                    // cek apakah pelamar punya lowonganApply dengan pemanggilan
                    if (!empty($row['lowongan_apply'])) {
                        foreach ($row['lowongan_apply'] as $apply) {
                            if (!empty($apply['pemanggilan'])) {
                                // tampilkan tombol jika ada pemanggilan
                                $button .= '<button class="btn btn-sm btn-info riwayat" 
                                                data-token="' . $token . '" data-nama="' . ucwords(strtolower($row['nama'])) . '">
                                                <i class="fas fa-history me-1"></i>Riwayat
                                            </button>';
                                break; // cukup sekali saja
                            }
                        }
                    }
                    $button .= '<button class="btn btn-sm btn-secondary detailPelamar" data-token=' . $token . '><i class="fas fa-info-circle me-1"></i>Detail</button>';
                    $button .= '</div>';

                    return $button;
                })

                ->rawColumns(['status' => 'status', 'usia' => 'usia', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('pelamar.show', [
            'judul' => 'Data Pelamar'
        ]);
    }

    public function riwayat_apply(Request $request)
    {
        if ($request->ajax()) {
            $token = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id = $decoded->id;
            $query = Pelamar::with([
                // urutkan lowonganApply berdasarkan created_at desc
                'lowonganApply' => function ($q) {
                    $q->whereHas('pemanggilan') // Hanya lowonganApply yang mempunyai relasi pemanggilan / sudah di panggil
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->with([
                            // load lowongan dengan field tertentu
                            'lowongan:id,judul,pekerjaan_dilamar,penempatan',
                            // load pemanggilan + recordPemanggilan dengan order
                            'pemanggilan.recordPemanggilan' => function ($q2) {
                                $q2->orderBy('id', 'desc');
                            },
                        ]);
                },
            ])
                ->where('id_pelamar', $id)
                ->whereHas('lowonganApply.pemanggilan') // Hanya Pelamar yang mempunyai relasi lowonganApply.pemanggilan
                ->firstOrFail();

            // transform hasil
            $query->lowonganApply->transform(function ($apply) {
                // rubah format created_at pada pemanggilan_pelamar
                $apply->created_at_formatted = Carbon::parse($apply->created_at)->translatedFormat('l, d F Y');
                return $apply;
            });

            return response()->json($query->toArray());
        }
    }

    public function detail_pelamar(Request $request)
    {
        $token = $request->query('token');

        try {
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id = $decoded->id;
            $pelamar = Pelamar::with([
                'perkawinan',
                'provinsiKtp',
            ])->findOrFail($id);
            return view('pelamar.detail', [
                'judul' => 'Detail ' . ucwords(strtolower($pelamar->nama)),
            ], compact('pelamar'));
        } catch (\Exception $e) {
            return response()->view('errors.401', [
                'judul' => 'Unauthorized!',
                'exception' => $e->getMessage(),
                'status' => 401
            ], 401);
        }
    }

    public function ajax_keluarga(Request $request)
    {
        if ($request->ajax()) {
            $token = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id = $decoded->id;

            $orderby = ["IBU KANDUNG", "SUAMI", "ISTRI", "ANAK"];
            $quoted = array_map(fn($v) => "'" . $v . "'", $orderby);

            $query = Keluarga::whereNull('deleted_at')
                ->where('pelamar_id', $id)
                ->orderByRaw('FIELD(status_hubungan, ' . implode(',', $quoted) . ')')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('lahir', function ($row) {
                    return Carbon::parse($row['tgllahir_keluarga'])->translatedFormat('d F Y');
                })

                ->addColumn('kelamin', function ($row) {
                    return ($row['kelamin_keluarga'] == "L" ? "LAKI - LAKI" : "PEREMPUAN");
                })

                ->rawColumns(['lahir' => 'lahir', 'kelamin' => 'kelamin'])
                ->addIndexColumn()
                ->toJson();
        }
    }

    public function ajax_pendidikan(Request $request)
    {
        if ($request->ajax()) {
            $token = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id = $decoded->id;

            $query = PendidikanNonformal::whereNull('deleted_at')
                ->where('pelamar_id', $id)
                ->orderBy('tglmulai_nonformal', 'desc')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('awal', function ($row) {
                    return Carbon::parse($row['tglmulai_nonformal'])->translatedFormat('d F Y');
                })

                ->addColumn('akhir', function ($row) {
                    return Carbon::parse($row['tglselesai_nonformal'])->translatedFormat('d F Y');
                })

                ->rawColumns(['awal' => 'awal', 'akhir' => 'akhir'])
                ->addIndexColumn()
                ->toJson();
        }
    }

    public function ajax_paklaring(Request $request)
    {
        if ($request->ajax()) {
            $token = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id = $decoded->id;

            $query = PengalamanKerja::whereNull('deleted_at')
                ->where('pelamar_id', $id)
                ->orderBy('tglawal', 'desc')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('awal', function ($row) {
                    return Carbon::parse($row['tglawal'])->translatedFormat('d F Y');
                })

                ->addColumn('akhir', function ($row) {
                    return Carbon::parse($row['tglakhir'])->translatedFormat('d F Y');
                })

                ->addColumn('lampiran', function ($row) {
                    $payload = [
                        'lampiran' => $row['file_paklaring'],
                        'exp' => time() + 3600 // optional, expired 1 jam
                    ];
                    $paklaring = JWT::encode($payload, config('jwt.secret'), 'HS256');
                    if ($row['file_paklaring'] != null) {
                        return '<button class="btn btn-icon lihatLampiran" data-lampiran=' . $paklaring . '><img src="/assets/img/banner/pdf.png" alt="img" width="20" /></button>';
                    } else {
                        return '<button class="btn btn-icon btn-sm btn-xl"><i class="fas fa-ban text-danger"></i></button>';
                    }
                })

                ->rawColumns(['awal' => 'awal', 'akhir' => 'akhir', 'lampiran' => 'lampiran'])
                ->addIndexColumn()
                ->toJson();
        }
    }

    public function ajax_sertifikat(Request $request)
    {
        if ($request->ajax()) {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $query = SertifikatPelamar::whereNull('deleted_at')
                ->where('pelamar_id', $id)
                ->orderBy('tglsertifikat', 'desc')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('terbit', function ($row) {
                    return Carbon::parse($row['tglsertifikat'])->translatedFormat('d F Y');
                })

                ->addColumn('lampiran', function ($row) {
                    $payload = [
                        'lampiran' => $row['file_sertifikat'],
                        'exp' => time() + 3600 // optional, expired 1 jam
                    ];
                    $paklaring = JWT::encode($payload, config('jwt.secret'), 'HS256');
                    if ($row['file_sertifikat'] != null) {
                        return '<button class="btn btn-icon lihatLampiran" data-lampiran=' . $paklaring . '><img src="/assets/img/banner/pdf.png" alt="img" width="20" /></button>';
                    } else {
                        return '<button class="btn btn-icon btn-sm btn-xl"><i class="fas fa-ban text-danger"></i></button>';
                    }
                })

                ->rawColumns(['terbit' => 'terbit', 'lampiran' => 'lampiran'])
                ->addIndexColumn()
                ->toJson();
        }
    }

    public function preview_paklaring($lampiran)
    {
        $decoded = JWT::decode($lampiran, new Key(config('jwt.secret'), 'HS256'));
        $file = $decoded->lampiran;

        $folder = explode('_', $file);
        if (!auth()->check()) {
            abort(403);
        }

        $path = storage_path("app/public/pelamar/" . $folder[0] . "/" . $file);

        if (!file_exists($path)) {
            abort(404);
        }

        $mime = \Illuminate\Support\Facades\File::mimeType($path);

        return response()->make(file_get_contents($path), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $file . '"'
        ]);
    }
}
