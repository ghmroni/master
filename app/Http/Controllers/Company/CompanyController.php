<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Companies;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;

class CompanyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('lihat-company'), only: ['index']),
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Companies::with('user')
                ->orderBy('updated_at', 'DESC')
                ->orderBy('name', 'ASC')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('logo', function ($row) {

                    $url = '/assets/img/avatars/no-image.png';
                    if ($row['logo'] != null) {
                        $url = '/assets/logos/' . $row['logo'];
                    }

                    return '
                        <a href="' . $url . '" class="glightbox" data-glightbox="type: image; zoomable: true;" >
                            <img src="' . $url . '" class="img-thumbnail" alt="image" width="70" >
                        </a>
                    ';
                })
                ->addColumn('dibuat', function ($row) {
                    return '<small class="text-muted">' . $row['user']['name'] . '</small>';
                })

                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    if (auth()->user()->can('edit-company')) {
                        return '<button class="btn btn-sm btn-warning Edit" data-token=' . $token . '><i class="far fa-edit me-1"></i>Edit</button>';
                    } else {
                        return '<small class="text-muted">' . Carbon::parse($row['updated_at'])->translatedFormat('l, d F Y') . '</small>';
                    }
                })

                ->rawColumns([
                    'logo'   => 'logo',
                    'dibuat' => 'dibuat',
                    'action' => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }
        return view('company.list-company', [
            'judul' => 'List Perusahaan'
        ]);
    }

    public function nextCompanyCode(Request $request)
    {
        //     $lastCompany = Companies::orderBy('code', 'desc')->first();
        //     # Jika belum ada data, mulai dari 'A'
        //     if (!$lastCompany || !$lastCompany->code) {
        //         return response()->json(['next_code' => 'A']);
        //     }
        //     # Naikkan satu huruf (A -> B, Z -> AA)
        //     $nextCode = ++$lastCompany->code;
        //     return response()->json(['next_code' => $nextCode]);
        $lastCompany = Companies::orderByRaw('LENGTH(code) DESC')
            ->orderBy('code', 'desc')
            ->first();
        # Jika belum ada data, mulai dari '01'
        if (!$lastCompany || !$lastCompany->code) {
            return response()->json(['next_code' => '01']);
        }
        # Ubah ke integer, tambah 1, lalu format minimal 2 digit
        $nextNumber = (int) $lastCompany->code + 1;
        $nextCode = str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
        return response()->json(['next_code' => $nextCode]);
    }

    public function detail_company(Request $request)
    {
        $token   = $request->input('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $query = Companies::findOrFail($id);
        return response()->json($query);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => [
                    'required',
                    Rule::unique('companies', 'name')
                        ->ignore($request->idcompany)
                        ->whereNull('deleted_at')
                ],
                'code' => [
                    'required',
                    Rule::unique('companies', 'code')
                        ->ignore($request->idcompany)
                        ->whereNull('deleted_at')
                ],
                'inisial' => [
                    'required',
                    'alpha',
                    'size:3',
                    Rule::unique('companies', 'inisial')
                        ->ignore($request->idcompany)
                        ->whereNull('deleted_at')
                ],
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ],
            [
                'name.required' => 'Nama Perusahaan masih kosong.',
                'name.unique'   => 'Nama Perusahaan sudah tersedia.',
                'code.required' => 'Kode Perusahaan masih kosong.',
                'code.mimes'    => 'Logo Perusahaan harus format .jpeg / .jpg / .png',
                'inisial.required' => 'Inisial Perusahaan masih kosong.',
                'inisial.alpha' => 'Inisial Perusahaan harus berupa huruf.',
                'inisial.size' => 'Inisial Perusahaan harus 3 huruf.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $data = [
                'name'    => trim(strtoupper($request->name)),
                'code'    => trim(strtoupper($request->code)),
                'inisial' => trim(strtoupper($request->inisial)),
                'user_id' => auth()->id(),
            ];

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('assets/logos');
                // Pindahkan file
                $file->move($destinationPath, $filename);

                $data['logo'] = $filename;
            }

            Companies::updateOrCreate(
                [
                    'id' => $request->idcompany
                ],
                $data
            );

            return response()->json([
                'message' => 'Perusahaan Berhasil Ditambahkan.'
            ], 200);
        }
    }

    public function get_perusahaan(Request $request)
    {
        $query = Companies::orderBy('name', 'ASC')->pluck('id', 'name');
        return response()->json($query);
    }

    public function current_perusahaan(Request $request)
    {
        $query = Companies::findOrFail($request->id);
        return response()->json($query);
    }
}
