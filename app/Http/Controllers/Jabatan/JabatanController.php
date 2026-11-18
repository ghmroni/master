<?php

namespace App\Http\Controllers\Jabatan;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\Tunjangan;
use App\Models\Unitkerja;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;

class JabatanController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('lihat-jabatan'), only: ['index']),
            new Middleware(PermissionMiddleware::using(['edit-jabatan', 'tambah-jabatan', 'hapus-jabatan']), only: ['store']),
            new Middleware(PermissionMiddleware::using('hapus-jabatan'), only: ['destroy']),
            new Middleware(PermissionMiddleware::using('lihat-unitkerja'), only: ['list_unitkerja']),
            new Middleware(PermissionMiddleware::using(['edit-unitkerja', 'tambah-unitkerja']), only: ['store_unitkerja', 'edit_unitkerja']),
            new Middleware(PermissionMiddleware::using('hapus-unitkerja'), only: ['destroy_unitkerja']),
            new Middleware(PermissionMiddleware::using('lihat-tunjangan'), only: ['tunjangan_list']),
            new Middleware(PermissionMiddleware::using(['edit-tunjangan', 'tambah-tunjangan']), only: ['tunjangan_store']),
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Jabatan::with('user')
                ->orderBy('updated_at', 'DESC')
                ->orderBy('name', 'ASC')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('updated', function ($row) {
                    return '<span class="text-muted">' . Carbon::parse($row['updated_at'])->translatedFormat('l, d F Y') . '</span>';
                })

                ->addColumn('action', function ($row) {
                    $button = '<div class="btn-group">';
                    if (auth()->user()->can('edit-jabatan')) {
                        $button .= '<button class="btn btn-sm btn-warning Edit" data-id=' . $row['id'] . '><i class="far fa-edit me-1"></i>Edit</button>';
                    }

                    if (auth()->user()->can('hapus-jabatan')) {
                        $button .= '<button class="btn btn-sm btn-danger Hapus" data-id=' . $row['id'] . ' data-name=' . $row['name'] . '><i class="fas fa-trash-alt me-1"></i>Hapus</button>';
                    }
                    $button .= '
                        </div>';

                    return $button;
                })

                ->rawColumns(['updated' => 'updated', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }
        return view('jabatan.jabatan-list', [
            'judul' => 'Jabatan'
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    Rule::unique('jabatan', 'name')->ignore($request->id)->where(function (Builder $query) {
                        $query->where('deleted_at', null);
                    }),
                ],
            ],
            [
                'name.required' => 'Nama Jabatan masih kosong.',
                'name.unique'   => 'Nama Jabatan sudah tersedia.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            Jabatan::updateOrcreate(
                [
                    'id' => $request->id
                ],
                [
                    'name'       => trim(strtoupper($request->input('name'))),
                    'user_id'    => Auth::user()->id,
                    'updated_at' => now()
                ]
            );

            return response()->json([
                'message' => 'Jabatan Berhasil Disimpan'
            ], 200);
        }
    }

    public function edit(Request $request)
    {
        $data = Jabatan::find($request->id);
        return response()->json($data, 200);
    }

    public function destroy(Request $request)
    {
        $data = Jabatan::find($request->id);
        $data->delete();
        return response()->json([
            'message' => 'Jabatan Berhasil Dihapus'
        ], 200);
    }

    public function get_jabatan()
    {
        $query = Jabatan::select('*')->orderBy('name', 'ASC')->pluck('id', 'name');
        return response()->json($query);
    }

    public function list_unitkerja(Request $request)
    {
        if ($request->ajax()) {
            $query = Unitkerja::with('user')
                ->orderBy('updated_at', 'DESC')
                ->orderBy('name', 'ASC')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('updated', function ($row) {
                    return '<span class="text-muted">' . Carbon::parse($row['updated_at'])->translatedFormat('l, d F Y') . '</span>';
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id' => $row['id'],
                        'exp' => time() + 3600 // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '
                        <div class="btn-group">
                            <button class="btn btn-sm btn-warning Edit" data-id=' . $token . '><i class="far fa-edit me-1"></i>Edit</button>
                            <button class="btn btn-sm btn-danger Hapus" data-id=' . $token . '><i class="fa fa-trash me-1"></i>Hapus</button>
                        </div>';

                    return $button;
                })

                ->rawColumns(['updated' => 'updated', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }
        return view('jabatan.unitkerja-list', [
            'judul' => 'Unitkerja'
        ]);
    }

    public function edit_unitkerja(Request $request)
    {
        if ($request->ajax()) {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $data = Unitkerja::find($id);
            return response()->json($data, 200);
        }
    }

    public function store_unitkerja(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'nama' => [
                    'required',
                    Rule::unique('unitkerja', 'name')
                        ->ignore($request->idunit)
                        ->where(function ($query) {
                            $query->whereNull('deleted_at');
                        }),
                ],
            ],
            [
                'nama.required' => 'Nama Unitkerja masih kosong.',
                'nama.unique'   => 'Nama Unitkerja sudah tersedia.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            Unitkerja::updateOrcreate(
                [
                    'id' => $request->idunit
                ],
                [
                    'name'       => trim(strtoupper($request->input('nama'))),
                    'user_id'    => Auth::user()->id,
                    'updated_at' => now()
                ]
            );

            return response()->json([
                'message' => 'Data Unitkerja Berhasil Disimpan'
            ], 200);
        }
    }

    public function destroy_unitkerja(Request $request)
    {
        $token   = $request->query('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $data = Unitkerja::find($id);
        $data->delete();
        return response()->json([
            'message' => 'Data Unitkerja Berhasil Dihapus'
        ], 200);
    }

    public function get_unitkerja()
    {
        $query = Unitkerja::select('*')->whereNull('deleted_at')->orderBy('name', 'ASC')->pluck('id', 'name');
        return response()->json($query);
    }

    public function tunjangan_list(Request $request)
    {
        if ($request->ajax()) {
            $query = Tunjangan::with('user')
                ->orderBy('updated_at', 'DESC')
                ->orderBy('name', 'ASC')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('updated', function ($row) {
                    return '<span class="text-muted">' . Carbon::parse($row['updated_at'])->translatedFormat('l, d F Y') . '</span>';
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];
                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<div class="btn-group">';
                    if (auth()->user()->can('edit-tunjangan')) {
                        $button .= '<button class="btn btn-sm btn-warning Edit" data-token=' . $token . '><i class="far fa-edit me-1"></i>Edit</button>';
                    }

                    if (auth()->user()->can('hapus-tunjangan')) {
                        $button .= '<button class="btn btn-sm btn-danger Hapus" data-token=' . $token . ' data-name=' . $row['name'] . '><i class="fas fa-trash-alt me-1"></i>Hapus</button>';
                    }
                    $button .= '
                        </div>';

                    return $button;
                })

                ->rawColumns(['updated' => 'updated', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }
        return view('jabatan.tunjangan-list', [
            'judul' => 'Tunjangan'
        ]);
    }

    public function tunjangan_store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    Rule::unique('tunjangan', 'name')->ignore($request->id)->where(function (Builder $query) {
                        $query->where('deleted_at', null);
                    }),
                ],
            ],
            [
                'name.required' => 'Nama Tunjangan masih kosong.',
                'name.unique'   => 'Nama Tunjangan sudah tersedia.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            Tunjangan::updateOrcreate(
                [
                    'id' => $request->id
                ],
                [
                    'name'       => trim(strtoupper($request->input('name'))),
                    'user_id'    => Auth::user()->id,
                    'updated_at' => now()
                ]
            );

            return response()->json([
                'message' => 'Tunjangan Berhasil Disimpan'
            ], 200);
        }
    }

    public function tunjangan_edit(Request $request)
    {
        $token   = $request->query('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $data = Tunjangan::find($id);
        return response()->json($data, 200);
    }

    public function tunjangan_destroy(Request $request)
    {
        $token   = $request->query('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $data = Tunjangan::find($id);
        $data->delete();
        return response()->json([
            'message' => 'Tunjangan Berhasil Dihapus'
        ], 200);
    }

    public function get_tunjangan()
    {
        $query = Tunjangan::select('*')->orderBy('name', 'ASC')->pluck('id', 'name');
        return response()->json($query);
    }
}
