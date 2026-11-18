<?php

namespace App\Http\Controllers\Lowongan;

use App\Http\Controllers\Controller;
use App\Models\Lowongan;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Middleware\PermissionMiddleware;

class LowonganController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('lihat-lowongan'), only: ['index']),
            new Middleware(PermissionMiddleware::using('tambah-lowongan|edit-lowongan'), only: ['input_lowongan', 'store']),
            new Middleware(PermissionMiddleware::using('hapus-lowongan'), only: ['tutup_lowongan']),
        ];
    }

    public function index(Request $request)
    {
        // Cek Roles
        $role = auth()->user()->roles()->first()?->id;

        $query = Lowongan::with([
            'project' => function ($q) {
                $q->select('id', 'name', 'logo')->where('status', 'ENABLE');
            },
            'user' => function ($q) {
                $q->select('id', 'name', 'email'); // sesuaikan kolom yang mau ditampilkan
            }

        ])
            ->whereHas('project', function ($q) {
                $q->where('status', 'ENABLE');
            })
            ->whereNull('deleted_at')
            ->when(
                auth()->user()->can('lihat-lowongan') && $role !== 8, //'PROJECT LEADER'
                fn($q) => $q->where('user_id', auth()->user()->id)
            )
            ->withCount([
                'lowonganApply as totalApply' => function ($q) {
                    $q->where(function ($sub) {
                        $sub->WhereDoesntHave('pemanggilan');
                    });
                }
            ]);



        // Filter search dari banyak kolom + relasi
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', '%' . $search . '%')
                    ->orWhere('pekerjaan_dilamar', 'like', '%' . $search . '%')
                    ->orWhere('penempatan', 'like', '%' . $search . '%')
                    ->orWhereHas('project', function ($sub) use ($search) {
                        $sub->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Ambil data dengan pagination
        $lowongans = $query
            ->orderByRaw("CASE WHEN end >= NOW() THEN 0 ELSE 1 END") // aktif duluan
            ->orderBy('id', 'desc') // urutkan berdasarkan id terbaru
            ->paginate(6);

        // Jika AJAX, kirim partial view
        if ($request->ajax()) {
            return view('lowongan._list', compact('lowongans'))->render();
        }

        return view('lowongan.data-lowongan', [
            'judul' => 'Data Lowongan'
        ], compact('lowongans'));
    }

    public function input_lowongan(Request $request)
    {
        if ($request->ajax()) {
            $role = auth()->user()->roles()->first()?->id;

            // query pertama: root project yang anak/cucunya diakses user
            $projectsWithAccess = Project::whereNull('parent_id')
                ->where('status', 'ENABLE')
                ->where(function ($q) {
                    $q->whereHas('children.aksesProject', function ($query) {
                        $query->where('user_id', auth()->id());
                    })
                        ->orWhereHas('children.children.aksesProject', function ($query) {
                            $query->where('user_id', auth()->id());
                        });
                })
                ->get();

            // query kedua: semua root project ENABLE
            $projectsAll = Project::where('status', 'ENABLE')
                ->whereNull('parent_id')
                ->orderBy('name', 'ASC')
                ->get();

            // pilih query sesuai role
            $projects = ($role == 6) ? $projectsWithAccess : $projectsAll;

            return response()->json($projects, 200);
        }

        return view('lowongan.input-lowongan', [
            'judul' => 'Input Lowongan',
        ]);
    }

    public function edit_lowongan(Request $request, $slug)
    {
        $detail = Lowongan::whereNull('deleted_at')
            ->where('slug', $slug)
            ->firstOrFail();

        $project = Project::where('status', 'ENABLE')
            ->whereNull(['parent_id', 'deleted_at'])
            ->orderBy('name', 'asc')
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'detail' => $detail,
                'start' => $detail->start,
                'end' => $detail->end,
                'projects' => $project,
            ], 200);
        }

        return view('lowongan.edit-lowongan', [
            'judul' => 'Edit Lowongan',
            'detail' => $detail,
            'projects' => $project,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'judul'             => 'required',
                'project_id'        => 'required',
                'penempatan'        => 'required',
                'pekerjaan_dilamar' => 'required',
                'pengalaman'        => 'required',
                'deskripsi'         => 'required',
                'persyaratan'       => 'required',
                'start'             => 'required',
                'end'               => 'required',
                'kualifikasi'       => 'required',
            ],
            [
                'judul.required'             => 'Judul Lowongan masih kosong',
                'project_id.required'        => 'Keperluan Project masih kosong',
                'penempatan.required'        => 'Lokasi Penempatan masih kosong',
                'pekerjaan_dilamar.required' => 'Jabatan Lowongan masih kosong',
                'pengalaman.required'        => 'Pengalaman Pekerjaan masih kosong',
                'deskripsi.required'         => 'Deskripsi Pekerjaan masih kosong',
                'persyaratan.required'       => 'Persyaratan masih kosong',
                'start.required'             => 'Tanggal Mulai Lowongan masih kosong',
                'end.required'               => 'Tanggal Berakhir Lowongan masih kosong',
                'kualifikasi.required'       => 'Kualifikasi Umum masih kosong',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            $arr_kualifikasi = json_decode($request->kualifikasi, true);
            $kualifikasi = array_column($arr_kualifikasi, 'value');

            $data = array(
                'judul'             => trim(strtoupper($request->input('judul'))),
                'pekerjaan_dilamar' => strtoupper($request->input('pekerjaan_dilamar')),
                'penempatan'        => strtoupper($request->input('penempatan')),
                'kualifikasi'       => strtoupper(implode(',', $kualifikasi)),
                'persyaratan'       => $request->input('persyaratan'),
                'deskripsi'         => $request->input('deskripsi'),
                'pengalaman'        => strtoupper($request->input('pengalaman')),
                'user_id'           => auth()->user()->id,
                'start'             => $request->input('start'),
                'end'               => $request->input('end'),
                'project_id'        => $request->input('project_id'),
            );

            if (isset($request->id)) {
                $lowongan = Lowongan::findOrFail($request->id);
                $lowongan->update($data);
            } else {
                $arr_kualifikasi = json_decode($request->kualifikasi, true);
                $kualifikasi = array_column($arr_kualifikasi, 'value');

                $lowongan = Lowongan::create($data);
            }

            return response()->json([
                'message' => 'Lowongan Berhasil Disimpan.',
            ], 200);
        }
    }

    public function tutup_lowongan(Request $request)
    {
        $query = Lowongan::whereNull('deleted_at')
            ->where('slug', $request->slug)
            ->whereDate('end', '>=', Carbon::today()) // pastikan tgl_end >= hari ini
            ->firstOrFail();

        if ($query) {
            $query->update([
                'end' => Carbon::yesterday(),
            ]);

            return response()->json([
                'message' => 'Lowongan Berhasil Ditutup.',
            ], 200);
        }
    }
}
