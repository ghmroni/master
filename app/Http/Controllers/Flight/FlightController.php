<?php

namespace App\Http\Controllers\Flight;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use App\Models\FlightHaji;
use App\Models\FlightLampiran;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;

class FlightController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('flight-upload'), only: ['store_upload_flight']),
            new Middleware(PermissionMiddleware::using('flighthaji-upload'), only: ['store_upload_flighthaji']),
            new Middleware(PermissionMiddleware::using('flight-data'), only: ['index']),
            new Middleware(PermissionMiddleware::using('flight-edit'), only: ['store_regflight', 'store_timeflight', 'store_flight', 'update_layanan', 'destroy_layanan']),
            new Middleware(PermissionMiddleware::using('flighthaji-data'), only: ['data_flighthaji']),
        ];
    }

    ## ================================================================================= FLIGHT
    public function index(Request $request)
    {
        $akses = auth()->user()->getProjectAccess(); // id child
        $rootIds = Project::whereIn('id', $akses) // Ambil Id Root nya
            ->get()
            ->map(fn($child) => $child->businessRoot()->id)
            ->unique()
            ->values()
            ->toArray();

        if ($request->ajax()) {
            $query = Flight::whereNull('deleted_at')
                ->with([
                    'project:id,name',
                    'lampiran:id,tahun,bulan,cabang',
                ])
                ->whereIn('project_id', $rootIds)
                ->orderBy('tahun', 'DESC')
                ->orderBy('bulan', 'DESC')
                ->get()
                ->groupBy(function ($item) {
                    // grup berdasarkan kombinasi
                    return $item->tahun . '-' . $item->bulan . '-01';
                })
                ->map(function ($group, $key) {
                    return [
                        'periode' => strtoupper(Carbon::parse($key)->translatedFormat('F Y')),
                        'tahun'   => $group->first()->tahun,
                        'bulan'   => $group->first()->bulan,
                        'project' => $group->first()->project->name,
                        'lampiran' => $group->first()->lampiran->id ?? null,
                        'cabang'  => $group->first()->cabang,
                        'title'   => $group->first()->title,
                        // kumpulkan operator dengan id supaya bisa dipakai klik detail
                        'operators' => $group->map(fn($row) => [
                            'id'     => $row->id,
                            'tahun'  => $group->first()->tahun,
                            'bulan'  => $group->first()->bulan,
                            'cabang' => $group->first()->cabang,
                            'nama'   => $row->sheet,
                        ])
                            ->unique('nama')
                            ->sortBy('nama')
                            ->values()
                            ->toArray(),
                    ];
                })
                ->values()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('project', function ($row) {
                    return '<span class="text-success fw-bold d-flex">' . $row['project'] . '</span><span class="d-flex">' . $row['cabang'] . '</span>';
                })

                ->addColumn('operators', function ($row) {
                    $operator = [];
                    foreach ($row['operators'] as $item) {
                        $payload = [
                            // 'id'  => $item['id'],
                            'tahun'  => $item['tahun'],
                            'bulan'  => $item['bulan'],
                            'cabang' => $item['cabang'],
                            'nama'   => $item['nama'],
                            'exp'    => time() + 3600     // optional, expired 1 jam
                        ];

                        $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                        // $title = ($row['title'] ? '<button class="btn btn-warning btnLayanan btn-xs" data-token="' . $token . '" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-dark" data-bs-placement="top" title="Kelola Layanan"><i class="ti ti-layout"></i></button>' : '');
                        $title = '<button class="btn btn-warning btnLayanan btn-xs" data-token="' . $token . '" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-dark" data-bs-placement="top" title="Kelola Layanan"><i class="ti ti-layout"></i></button>';
                        $hapus = '<button class="btn btn-danger btn-xs hapusOperator" data-token="' . $token . '" data-sheet="' . $item['nama'] . '" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-dark" data-bs-placement="top" title="Hapus Operator"><i class="ti ti-trash"></i></button>';

                        $operator[] = '
                            <div class="btn-group me-1 mb-2">
                            ' . $title . '
                                <button class="btn btn-xs btn-primary detail" data-token="' . $token . '" data-bs-toggle="tooltip" data-bs-custom-class="tooltip-dark" data-bs-placement="top" title="Detail ' . $item['nama'] . '">' . $item['nama'] . '</button>
                            ' . $hapus . '
                            </div>
                        ';
                    }
                    return implode(' ', $operator);
                })
                ->addColumn('action', function ($row) {
                    $payload = [
                        'tahun'  => $row['tahun'],
                        'bulan'  => $row['bulan'],
                        'cabang' => $row['cabang'],
                        'exp'    => time() + 3600     // optional, expired 1 jam
                    ];
                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<button class="btn btn-sm btn-facebook downloadFlight" data-lampiran="' . $row['lampiran'] . '" data-token="' . $token . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Download"><i class="ti ti-cloud-down me-1"></i>Download</button>';
                    $button .= ($row['lampiran']
                        ? '
                        <div class="form-check form-check-primary mt-3">
                            <label class="form-check-label">
                                <input class="form-check-input me-1" type="checkbox" id="generate" name="generate" value="1" />
                                Generate Ulang Lampiran
                            </label>
                        </div>'
                        : '');

                    return  $button;
                })

                ->rawColumns([
                    'project'   => 'project',
                    'operators' => 'operators',
                    'action'    => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }
        return view('flight.flight-data', [
            'judul' => 'Data Flight'
        ]);
    }

    public function detail_operator(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $tahun  = $decoded->tahun;
            $bulan  = $decoded->bulan;
            $cabang = $decoded->cabang;
            $sheet  = $decoded->nama;

            $akses = auth()->user()->getProjectAccess();
            $rootIds = Project::whereIn('id', $akses) // Ambil Id Root nya
                ->get()
                ->map(fn($child) => $child->businessRoot()->id)
                ->unique()
                ->values()
                ->toArray();

            if ($request->ajax()) {
                $data = Flight::whereNull('deleted_at')
                    ->where([
                        'tahun'  => $tahun,
                        'bulan'  => $bulan,
                        'cabang' => $cabang,
                        'sheet'  => $sheet,
                    ])
                    ->whereIn('project_id', $rootIds)
                    ->orderBy('tgl_arrival', 'ASC')
                    ->select('*')
                    ->selectRaw('COUNT(*) OVER () as total') // window function
                    ->get()
                    ->toArray();

                return DataTables::of($data)
                    ->addColumn('registrasi', function ($row) {
                        $payload = [
                            'id'  => $row['id'],
                            'exp' => time() + 3600  // optional, expired 1 jam
                        ];
                        $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                        $perms = auth()->user()->can('flight-edit') ? 'detailReg' : '';

                        return '<button class="btn btn-xs btn-dark badge ' . $perms . '" data-token="' . $token . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Detail Registrasi"><i class="ti ti-pencil me-1"></i>' . $row['aircraft_registrasi'] . '</button>';
                    })

                    ->addColumn('sta', function ($row) {
                        return Carbon::parse($row['arrival_sta'])->format('H:i');
                    })

                    ->addColumn('ata', function ($row) {
                        $payload = [
                            'id'  => $row['id'],
                            'exp' => time() + 3600  // optional, expired 1 jam
                        ];
                        $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                        $perms = auth()->user()->can('flight-edit') ? 'addAta' : '';

                        return (empty($row['arrival_ata']) || $row['arrival_ata'] === "00:00:00"
                            ? '<button class="btn btn-xs btn-success badge ' . $perms . '" data-token="' . $token . '" >
                            <i class="fas fa-plus-circle me-1"></i>ATA</button>'
                            : Carbon::parse($row['arrival_ata'])->format('H:i'));
                    })

                    ->addColumn('std', function ($row) {
                        return Carbon::parse($row['departure_std'])->format('H:i');
                    })

                    ->addColumn('atd', function ($row) {
                        $payload = [
                            'id'  => $row['id'],
                            'exp' => time() + 3600  // optional, expired 1 jam
                        ];
                        $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                        $perms = auth()->user()->can('flight-edit') ? 'addAtd' : '';

                        return (empty($row['departure_atd']) || $row['departure_atd'] === "00:00:00"
                            ? '<button class="btn btn-xs btn-danger badge ' . $perms . '" data-token="' . $token . '" >
                            <i class="fas fa-plus-circle me-1"></i>ATD</button>'
                            : Carbon::parse($row['departure_atd'])->format('H:i'));
                    })

                    ->addColumn('action', function ($row) {
                        $payload = [
                            'id'  => $row['id'],
                            'exp' => time() + 3600  // optional, expired 1 jam
                        ];
                        $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                        $button = '<span class="badge bg-label-secondary"><i class="ti ti-info-circle me-1 ti-xs"></i>No Access</span>';
                        if (auth()->user()->can('flight-edit')) {
                            if ($row['total'] > 1) {
                                $button = '
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-warning edit" data-token="' . $token . '"><i class="fas fa-edit me-1"></i>Edit</button>
                                        <button class="btn btn-sm btn-danger hapus" data-token="' . $token . '"><i class="ti ti-trash me-1"></i>Hapus</button>
                                    </div>
                                ';
                            } else {
                                $button = '
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-warning edit" data-token="' . $token . '"><i class="fas fa-edit me-1"></i>Edit</button>
                                    </div>
                                ';
                            }
                        }
                        return $button;
                    })

                    ->rawColumns([
                        'registrasi' => 'registrasi',
                        'sta'        => 'sta',
                        'ata'        => 'ata',
                        'std'        => 'std',
                        'atd'        => 'atd',
                        'action'     => 'action'
                    ])
                    ->addIndexColumn()
                    ->toJson();
            }

            return view('flight.detail-operator', [
                'judul' => 'Upload Flights'
            ]);
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function detail_flight(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            if ($request->ajax()) {
                $query = Flight::findOrFail($id);
                #Rubah format tgl dan jam
                $query->arrival_tgl = Carbon::parse($query->tgl_arrival)->format('d F Y');
                $query->arrival_sta = Carbon::parse($query->arrival_sta)->format('H:i');
                $query->arrival_ata = ($query->arrival_ata && $query->arrival_ata !== "00:00:00")
                    ? Carbon::parse($query->arrival_ata)->format('H:i')
                    : null;

                $query->departure_tgl = Carbon::parse($query->tgl_departure)->format('d F Y');
                $query->departure_std = Carbon::parse($query->departure_std)->format('H:i');
                $query->departure_atd = ($query->departure_atd && $query->departure_atd !== "00:00:00")
                    ? Carbon::parse($query->departure_atd)->format('H:i')
                    : null;

                return response()->json($query);
            }
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function store_regflight(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'idreg'   => 'required',
                'namareg' => 'required',
            ],
            ['namareg.required' => 'Nama Registrasi masih kosong']
        );
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 409);
        } else {
            $token   = $request->idreg;
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $Registrasi = Flight::findOrFail($id);
            $Registrasi->update([
                'aircraft_registrasi' => trim(strtoupper($request->namareg))
            ]);

            return response()->json([
                'message' => 'Data Registrasi Berhasil Disimpan.'
            ], 200);
        }
    }

    public function store_timeflight(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'idreg'   => 'required',
                'ata' => 'required_without:atd|date_format:H:i',
                'atd' => 'required_without:ata|date_format:H:i',
            ],
            [
                'ata.required_without' => 'Actual Time Arrival masih kosong',
                'atd.required_without' => 'Actual Time Departure masih kosong',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 409);
        } else {
            $token   = $request->idreg;
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $timeFlight = Flight::findOrFail($id);
            if ($request->filled('ata')) {
                $timeFlight->arrival_ata = Carbon::createFromFormat('H:i', $request->ata);
            }

            if ($request->filled('atd')) {
                $timeFlight->departure_atd = Carbon::createFromFormat('H:i', $request->atd);
            }

            $timeFlight->save();

            return response()->json([
                'message' => 'Data Registrasi Berhasil Disimpan.'
            ], 200);
        }
    }

    public function store_flight(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'idreg'              => 'required',
                'tglarrival'         => 'required|date',
                'tgldeparture'       => 'required|date',
                'aircrafttype'       => 'required',
                'aircraftcategori'   => 'required',
                'aircraftregistrasi' => 'required',
                'arrivalflight'      => 'required',
                'arrivalfrom'        => 'required',
                'arrivalsta'         => 'required|date_format:H:i',
                'departureflight'    => 'required',
                'departureto'        => 'required',
                'departurestd'       => 'required|date_format:H:i',
                'status'             => 'required',
                'remarks'            => 'required',
            ],
            [
                'tglarrival.required'         => 'Tanggal Arrival masih kosong',
                'tgldeparture.required'       => 'Tanggal Departure masih kosong',
                'aircrafttype.required'       => 'Aircraft Type masih kosong',
                'aircraftcategori.required'   => 'Aircraft Categori masih kosong',
                'aircraftregistrasi.required' => 'Aircraft Registrasi masih kosong',
                'arrivalflight.required'      => 'Arrival Flight masih kosong',
                'arrivalfrom.required'        => 'Arrival From masih kosong',
                'arrivalsta.required'         => 'Arrival STA masih kosong',
                'departureflight.required'    => 'Departure Flight masih kosong',
                'departureto.required'        => 'Departure To masih kosong',
                'departurestd.required'       => 'Departure STD masih kosong',
                'status.required'             => 'Status masih kosong',
                'remarks.required'            => 'Remarks masih kosong',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 409);
        } else {
            $token   = $request->idreg;
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $ceklisArray = $request->input('ceklis', []);
            $ceklis = implode(',', array_map('strtoupper', $ceklisArray));

            $data = [
                'tgl_arrival'         => Carbon::parse($request->input('tglarrival'))->format('Y-m-d'),
                'tgl_departure'       => Carbon::parse($request->input('tgldeparture'))->format('Y-m-d'),
                'aircraft_type'       => trim(strtoupper($request->input('aircrafttype'))),
                'aircraft_categori'   => trim(strtoupper($request->input('aircraftcategori'))),
                'aircraft_registrasi' => trim(strtoupper($request->input('aircraftregistrasi'))),
                'arrival_flight'      => trim(strtoupper($request->input('arrivalflight'))),
                'arrival_from'        => trim(strtoupper($request->input('arrivalfrom'))),
                'arrival_sta'         => trim(strtoupper($request->input('arrivalsta'))),
                'arrival_ata'         => trim(strtoupper($request->input('arrivalata'))),
                'departure_flight'    => trim(strtoupper($request->input('departureflight'))),
                'departure_to'        => trim(strtoupper($request->input('departureto'))),
                'departure_std'       => trim(strtoupper($request->input('departurestd'))),
                'departure_atd'       => trim(strtoupper($request->input('departureatd'))),
                'status'              => trim(strtoupper($request->input('status'))),
                'remarks'             => trim(strtoupper($request->input('remarks'))),
                'ceklis'              => $ceklis,
                'user_id'             => auth()->id(),
            ];

            $flight = Flight::findOrFail($id);
            $flight->update($data);

            return response()->json([
                'message' => 'Data Flight Berhasil Disimpan.'
            ], 200);
        }
    }

    public function destroy_flight(Request $request)
    {
        try {
            $token   = $request->idreg;
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $flight = Flight::findOrFail($id);
            $flight->delete();

            return response()->json([
                'message' => 'Data Flight Berhasil Dihapus.'
            ], 200);
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function destroy_operator(Request $request)
    {
        try {
            $token   = $request->token;
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));

            $tahun  = $decoded->tahun;
            $bulan  = $decoded->bulan;
            $cabang = $decoded->cabang;
            $sheet  = $decoded->nama;

            Flight::where([
                'tahun'  => $tahun,
                'bulan'  => $bulan,
                'cabang' => $cabang,
                'sheet'  => $sheet,
            ])->delete();

            return response()->json([
                'message' => 'Operator Berhasil Dihapus.'
            ], 200);
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function detail_layanan(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $tahun  = $decoded->tahun;
            $bulan  = $decoded->bulan;
            $cabang = $decoded->cabang;
            $sheet  = $decoded->nama;

            if ($request->ajax()) {
                $query = Flight::where([
                    'tahun'  => $tahun,
                    'bulan'  => $bulan,
                    'cabang' => $cabang,
                    'sheet'  => $sheet,
                ])->first();

                $titles = explode(',', $query->title);
                $ceklis = explode(',', $query->ceklis);

                $data = [];
                foreach ($titles as $i => $title) {
                    // skip kalau title kosong/null setelah trim
                    if (empty(trim($title))) {
                        continue;
                    }

                    $payload = [
                        'id'  => $query->id . '-' . $i,   // gabungan id flight + index header
                        'exp' => time() + 3600
                    ];
                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $data[] = [
                        'token'  => $token,
                        'title'  => $title,
                        'ceklis' => $ceklis[$i] ?? ''
                    ];
                }

                $payloadFlight = [
                    'id'  => $query->id,
                    'exp' => time() + 3600
                ];
                $tokenFlight = JWT::encode($payloadFlight, config('jwt.secret'), 'HS256');

                return response()->json([
                    'canEdit' => auth()->user()->can('flight-edit'),
                    'token' => $tokenFlight,
                    'sheet' => $query->sheet,
                    'items' => $data
                ]);
            }
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function store_layanan(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'required',
            ],
            [
                'title.required' => 'Nama Layanan masih kosong',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 409);
        } else {
            $decoded = JWT::decode($request->token, new Key(config('jwt.secret'), 'HS256'));
            $id = $decoded->id;

            $flight = Flight::findOrFail($id);
            # Ambil Data dari DB
            // $titles = explode(',', $flight->title);
            // $ceklis = explode(',', $flight->ceklis);
            $titles = array_filter(explode(',', $flight->title), fn($t) => trim($t) !== '');
            $ceklis = array_filter(explode(',', $flight->ceklis), fn($c) => trim($c) !== '');

            $newTitle = trim(strtoupper($request->title));
            $newCeklis = trim(strtoupper($request->ceklis)) ?? '';

            // cek apakah title sudah ada
            if (in_array($newTitle, $titles)) {
                return response()->json(['message' => 'Nama Layanan sudah tersedia'], 400);
            }

            # Tambahkan Data Baru
            $titles[] = $newTitle;
            $ceklis[] = $newCeklis;

            $newTitles = implode(',', $titles);
            $newCeklis = implode(',', $ceklis);

            Flight::whereNull('deleted_at')
                ->where([
                    'tahun'  => $flight->tahun,
                    'bulan'  => $flight->bulan,
                    'cabang' => $flight->cabang,
                    'sheet'  => $flight->sheet,
                ])
                ->update([
                    'title'  => $newTitles,
                    'ceklis' => $newCeklis
                ]);

            return response()->json([
                'message' => 'Data Layanan Berhasil Disimpan.'
            ], 200);
        }
    }

    public function update_layanan(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'required',
            ],
            [
                'title.required' => 'Nama Layanan masih kosong',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 409);
        } else {
            $decoded = JWT::decode($request->token, new Key(config('jwt.secret'), 'HS256'));
            $idIndex = $decoded->id; // "123-1"
            // pecah jadi [id, index]
            [$flightId, $index] = explode('-', $idIndex);

            $flight = Flight::findOrFail($flightId);

            $titles = explode(',', $flight->title);
            $ceklis = explode(',', $flight->ceklis);

            $newTitle = trim(strtoupper($request->title));
            $oldTitle = $titles[$index];

            // kalau sama dengan nilai lama → update normal
            if ($newTitle === $oldTitle) {
                $titles[$index] = $newTitle;
                $ceklis[$index] = $request->ceklis ?? $ceklis[$index];
            } else {
                // kalau beda, cek apakah sudah ada di array
                if (in_array($newTitle, $titles)) {
                    return response()->json(['message' => 'Nama Layanan sudah tersedia'], 400);
                }

                // kalau belum ada, lanjut update
                $titles[$index] = $newTitle;
                $ceklis[$index] = $request->ceklis ?? $ceklis[$index];
            }

            $newTitles = implode(',', $titles);
            $newCeklis = implode(',', $ceklis);

            Flight::whereNull('deleted_at')
                ->where([
                    'tahun'  => $flight->tahun,
                    'bulan'  => $flight->bulan,
                    'cabang' => $flight->cabang,
                    'sheet'  => $flight->sheet,
                ])
                ->update([
                    'title'  => $newTitles,
                    'ceklis' => $newCeklis
                ]);

            return response()->json([
                'message' => 'Data Layanan Flight Berhasil Disimpan.'
            ], 200);
        }
    }

    public function destroy_layanan(Request $request)
    {
        $decoded = JWT::decode($request->token, new Key(config('jwt.secret'), 'HS256'));
        $idIndex = $decoded->id; // "123-1"
        // pecah jadi [id, index]
        [$flightId, $index] = explode('-', $idIndex);

        $flight = Flight::findOrFail($flightId);

        $titles = explode(',', $flight->title);
        $ceklis = explode(',', $flight->ceklis);

        // hapus index yang dimaksud
        unset($titles[$index], $ceklis[$index]);

        // reindex array biar rapih
        $titles = array_values($titles);
        $ceklis = array_values($ceklis);

        $newTitles = implode(',', $titles);
        $newCeklis = implode(',', $ceklis);

        Flight::whereNull('deleted_at')
            ->where([
                'tahun'  => $flight->tahun,
                'bulan'  => $flight->bulan,
                'cabang' => $flight->cabang,
                'sheet'  => $flight->sheet,
            ])
            ->update([
                'title'  => $newTitles,
                'ceklis' => $newCeklis
            ]);

        return response()->json([
            'message' => 'Layanan Flight Berhasil Dihapus.'
        ], 200);
    }

    public function download_flight(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'generate'   => 'required',
                'ghm'        => 'required',
                'gapura'     => 'required',
                'dibuat'     => 'required',
                'mengetahui' => 'required',
                'menyetujui' => 'required',
                'jab2'       => 'required',
                'jab3'       => 'required',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 409);
        } else {
            $generate = $request->input('generate');
            $lampiran = $request->input('lampiranid');

            if ($lampiran !== null && $generate == 0) {
                # Jika Data Sudah Ada dan tidak di generate
                return $this->pdf_flight($lampiran);
            } else {
                $token    = $request->input('tokenid');
                $decoded  = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));

                $tahun  = $decoded->tahun;
                $bulan  = $decoded->bulan;
                $cabang = $decoded->cabang;

                $petugas = [
                    'ghm'        => trim(strtoupper($request->ghm)),
                    'gapura'     => trim(strtoupper($request->gapura)),
                    'dibuat'     => trim(strtoupper($request->dibuat)),
                    'mengetahui' => trim(strtoupper($request->mengetahui)),
                    'menyetujui' => trim(strtoupper($request->menyetujui)),
                    'jab1'       => trim($request->jab1),
                    'jab2'       => trim($request->jab2),
                    'jab3'       => trim($request->jab3),
                ];

                $flight = Flight::whereNull('deleted_at')
                    ->where([
                        'tahun'  => $tahun,
                        'bulan'  => $bulan,
                        'cabang' => $cabang,
                    ])
                    ->orderBy('sheet')
                    ->get()
                    ->groupBy('sheet')
                    ->map(function ($group, $sheet) use ($tahun, $bulan) {
                        // urutkan dulu per group
                        $sorted = $group->sortBy('tgl_arrival');

                        // bikin rekap tanggal 1–31
                        $rekap = collect(range(1, 31))->mapWithKeys(function ($day) use ($sorted, $tahun, $bulan) {
                            $tanggal = sprintf('%04d-%02d-%02d', $tahun, $bulan, $day);

                            $count = $sorted->filter(function ($row) use ($tanggal) {
                                $tgl = \Carbon\Carbon::parse($row->tgl_departure)->toDateString();
                                return $tgl === $tanggal
                                    && !empty($row->arrival_ata)
                                    && $row->arrival_ata !== '00:00:00'
                                    && !empty($row->departure_atd)
                                    && $row->departure_atd !== '00:00:00';
                            })->count();

                            return [$day => $count];
                        });

                        return [
                            'sheet'    => $sheet,
                            'cabang'   => $group->first()->cabang ?? null,
                            'operator' => $group->first()->operator ?? null,
                            'area'     => $group->first()->area ?? null,
                            'title'     => $group->first()->title ?? null,
                            'periode'  => strtoupper(
                                Carbon::parse($tahun . '-' . $bulan . '-01')->translatedFormat('F Y')
                            ),
                            'records' => $sorted->values()->toArray(),
                            'rekap'   => $rekap->toArray(),
                            'total'   => $rekap->sum(),
                        ];
                    })
                    ->values();

                # Total masing2 tanggal per sheet
                $grandRekap = collect(range(1, 31))->mapWithKeys(function ($day) use ($flight) {
                    $totalPerDay = $flight->sum(function ($sheet) use ($day) {
                        return $sheet['rekap'][$day] ?? 0;
                    });
                    return [$day => $totalPerDay];
                });

                $grandTotal = $grandRekap->sum();

                # Kumpulin TITLE / LAYANAN dari semua sheet
                $allTitles = collect($flight)->flatMap(function ($sheet) {
                    return explode(',', $sheet['title']);
                })->filter()->unique()->values();

                $titleRekap = $allTitles->mapWithKeys(function ($title) use ($flight, $tahun, $bulan) {
                    $perSheet = collect($flight)->mapWithKeys(function ($sheet) use ($title, $tahun, $bulan) {
                        // ambil record di sheet yang punya title tsb
                        $count = collect($sheet['records'])->filter(function ($row) use ($title, $tahun, $bulan) {
                            $titles = explode(',', $row['title'] ?? '');
                            $tgl    = \Carbon\Carbon::parse($row['tgl_departure'])->toDateString();

                            return in_array($title, $titles)
                                && $tgl >= sprintf('%04d-%02d-01', $tahun, $bulan)
                                && $tgl <= sprintf('%04d-%02d-31', $tahun, $bulan)
                                && !empty($row['arrival_ata']) && $row['arrival_ata'] !== '00:00:00'
                                && !empty($row['departure_atd']) && $row['departure_atd'] !== '00:00:00';
                        })->count();

                        return [$sheet['sheet'] => $count];
                    });

                    $grandTotalTitle = $perSheet->sum();

                    return [$title => [
                        'namaSheet'        => $perSheet,
                        'grandTotalTitle' => $grandTotalTitle,
                    ]];
                });

                $dataLampiran = [
                    'tahun'       => $tahun,
                    'bulan'       => $bulan,
                    'cabang'      => $cabang,
                    'data_flight' => $flight ? $flight->toArray() : [],
                    'data_rekap'  => [
                        'per_tgl' => $grandRekap ? $grandRekap->toArray() : [],
                        'total'   => $grandTotal ?? 0,
                    ],
                    'data_layanan' => $titleRekap ? $titleRekap->toArray() : [],
                    'data_petugas' => $petugas ?? [],
                    'keterangan'   => $request->keterangan ?? null,
                    'user_id'      => auth()->id(),
                ];

                if ($lampiran) {
                    FlightLampiran::destroy($lampiran);
                }

                $idlampiran = FlightLampiran::create($dataLampiran)->id;
                return $this->pdf_flight($idlampiran);
            }
        }
    }

    public function pdf_flight($idlampiran)
    {
        $query = FlightLampiran::findOrFail($idlampiran);
        $datarekap = $query->data_rekap;
        $periode = Carbon::parse($query->tahun . '-' . $query->bulan)->translatedFormat('F Y');

        $pdf = Pdf::loadView('flight.pdf_rekap_flight', [
            'datas'       => collect($query->data_flight),
            'grandRekap'  => $datarekap['per_tgl'],
            'grandTotal'  => $datarekap['total'],
            'dataTitles'  => $query->data_layanan,
            'dataPetugas' => $query->data_petugas,
        ]);

        $filename = 'Data Flight - ' . $periode . '.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function detail_lampiran(Request $request)
    {
        $query = FlightLampiran::findOrFail($request->id);
        return response()->json($query->data_petugas);
    }

    ## ================================================================================= UPLOAD
    public function uploads(Request $request)
    {
        $akses = auth()->user()->getProjectAccess();

        ## Ambil hanya root yang mempunyai name "GAPURA"
        $projects = Project::whereIn('id', $akses)->get()
            ->map(fn($child) => $child->businessRoot())
            ->filter(fn($root) => str_contains(strtoupper($root->name), 'GAPURA'))
            ->unique('id')
            ->values();

        return view('flight.upload-flight', [
            'judul' => 'Upload Flights'
        ], compact('projects'));
    }

    public function store_upload_flight(Request $request)
    {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();

        if (!str_contains($originalName, 'template_upload_flight')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nama file tidak sesuai ketentuan!'
            ], 400);
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        // Ambil sheet ke-2 (index 1 karena 0 = sheet pertama)
        $sheet = $spreadsheet->getSheet(1);

        $cabang    = trim(strtoupper($sheet->getCell("B1")->getValue()));
        $operator  = trim(strtoupper($sheet->getCell("B2")->getValue()));
        $area      = trim(strtoupper($sheet->getCell("B3")->getValue()));
        $bulan     = trim($sheet->getCell("B4")->getValue());
        $tahun     = trim($sheet->getCell("B5")->getValue());
        $namaSheet = trim(strtoupper($sheet->getCell("B6")->getValue()));


        // 3. Cek apakah ada data mulai row 9
        $highestRow = $sheet->getHighestRow();
        $headerRow = 8;
        $highestColumn = $sheet->getHighestColumn($headerRow);
        $headerData = $sheet->rangeToArray("A{$headerRow}:{$highestColumn}{$headerRow}", null, true, true, true);

        // Hitung jumlah header
        $headers = $headerData[$headerRow];
        $totalHeader = count($headers);

        if ($highestRow <= 8) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada data setelah header row 8!'
            ], 400);
        }

        // 4. Ambil data mulai row 9
        $data = [];
        for ($row = 9; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray("A{$row}:{$highestColumn}{$row}", null, true, true, true);
            $rowValues = $rowData[$row];

            $mapped = [
                'tahun'               => $tahun,
                'bulan'               => $bulan,
                'tgl_arrival'         => trim($rowValues['A']) ?? null,
                'tgl_departure'       => trim($rowValues['B']) ?? null,
                'aircraft_type'       => trim($rowValues['C']) ?? null,
                'aircraft_categori'   => trim($rowValues['D']) ?? null,
                'aircraft_registrasi' => trim($rowValues['E']) ?? null,
                'arrival_flight'      => trim($rowValues['F']) ?? null,
                'arrival_from'        => trim($rowValues['G']) ?? null,
                'arrival_sta'         => trim($rowValues['H']) ?? null,
                'departure_flight'    => trim($rowValues['I']) ?? null,
                'departure_to'        => trim($rowValues['J']) ?? null,
                'departure_std'       => trim($rowValues['K']) ?? null,
                'status'              => trim($rowValues['L']) ?? null,
                'remarks'             => trim($rowValues['M']) ?? null,
                'cabang'              => $cabang,
                'operator'            => $operator,
                'area'                => $area,
                'sheet'               => $namaSheet,
                'project_id'          => $request->input('project'),
                'user_id'             => auth()->id(),
            ];

            // kalau ada header lebih dari 13 (M = kolom ke-13)
            if ($totalHeader > 13) {
                $extraHeaders = [];
                $extraValues  = [];

                $i = 0;
                foreach ($headers as $col => $headerName) {
                    $i++;
                    if ($i > 13) { // mulai dari kolom ke-14
                        // skip kalau headerName kosong/null
                        if (empty(trim($headerName))) {
                            continue;
                        }
                        $extraHeaders[] = $headerName;

                        $val = strtoupper(trim($rowValues[$col] ?? ''));

                        // ambil hanya 1 huruf
                        $val = substr($val, 0, 1);

                        // validasi: hanya X yang diterima
                        if ($val !== 'X') {
                            $val = ''; // kosongkan kalau bukan X
                        }

                        $extraValues[] = $val;
                    }
                }

                // $mapped['title']  = implode(',', $extraHeaders);
                // $mapped['ceklis'] = implode(',', $extraValues);
                $title  = implode(',', $extraHeaders);
                $ceklis = implode(',', $extraValues);
            } else {
                // $mapped['title']  = '';
                // $mapped['ceklis'] = '';
                $title  = '';
                $ceklis = '';
            }

            Flight::updateOrCreate(
                $mapped,
                [
                    'title'      => $title,
                    'ceklis'     => $ceklis,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        return response()->json([
            'message' => 'Data Flight Berhasil Diupload.'
        ], 200);
    }

    ## ================================================================================= HAJI
    public function store_upload_flighthaji(Request $request)
    {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();

        if (!str_contains($originalName, 'template_upload_flighthaji')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nama file tidak sesuai ketentuan!'
            ], 400);
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        // Ambil sheet ke-2 (index 1 karena 0 = sheet pertama)
        $sheet = $spreadsheet->getSheet(1);

        $cabang    = trim(strtoupper($sheet->getCell("B1")->getValue()));
        $operator  = trim(strtoupper($sheet->getCell("B2")->getValue()));
        $area      = trim(strtoupper($sheet->getCell("B3")->getValue()));
        $bulan     = trim($sheet->getCell("B4")->getValue());
        $tahun     = trim($sheet->getCell("B5")->getValue());
        $namaSheet = trim(strtoupper($sheet->getCell("B6")->getValue()));


        // 3. Cek apakah ada data mulai row 9
        $highestRow = $sheet->getHighestRow();
        $headerRow = 8;
        $highestColumn = $sheet->getHighestColumn($headerRow);
        $headerData = $sheet->rangeToArray("A{$headerRow}:{$highestColumn}{$headerRow}", null, true, true, true);

        // Hitung jumlah header
        $headers = $headerData[$headerRow];
        $totalHeader = count($headers);

        if ($highestRow <= 8) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada data setelah header row 8!'
            ], 400);
        }

        // 4. Ambil data mulai row 9
        $data = [];
        for ($row = 9; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray("A{$row}:{$highestColumn}{$row}", null, true, true, true);
            $rowValues = $rowData[$row];

            $mapped = [
                'tahun'               => $tahun,
                'bulan'               => $bulan,
                'tgl_arrival'         => trim($rowValues['A']) ?? null,
                'tgl_departure'       => trim($rowValues['B']) ?? null,
                'aircraft_type'       => trim($rowValues['C']) ?? null,
                'aircraft_categori'   => trim($rowValues['D']) ?? null,
                'aircraft_registrasi' => trim($rowValues['E']) ?? null,
                'arrival_flight'      => trim($rowValues['F']) ?? null,
                'arrival_from'        => trim($rowValues['G']) ?? null,
                'arrival_sta'         => trim($rowValues['H']) ?? null,
                'departure_flight'    => trim($rowValues['I']) ?? null,
                'departure_to'        => trim($rowValues['J']) ?? null,
                'departure_std'       => trim($rowValues['K']) ?? null,
                'status'              => trim($rowValues['L']) ?? null,
                'remarks'             => trim($rowValues['M']) ?? null,
                'cabang'              => $cabang,
                'operator'            => $operator,
                'area'                => $area,
                'sheet'               => $namaSheet,
                'project_id'          => $request->input('project'),
                'user_id'             => auth()->id(),
            ];

            FlightHaji::updateOrCreate(
                $mapped,
                [
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }


        return response()->json([
            'message' => 'Data Flight Haji Berhasil Diupload.'
        ], 200);
    }

    public function data_flighthaji(Request $request)
    {
        return view('flight.haji-data', [
            'judul' => 'Data Flight Haji'
        ]);
    }
}
