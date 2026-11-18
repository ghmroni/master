<?php

namespace App\Http\Controllers\Kontrakinduk;

use App\Http\Controllers\Controller;
use App\Models\Kontrakinduk;
use App\Models\KontrakIndukAdendum;
use App\Models\KontrakIndukApprovalRequest;
use App\Models\KontrakIndukBiaya;
use App\Models\KontrakIndukLog;
use App\Models\KontrakIndukSettingHarga;
use App\Models\Project;
use App\Models\Tunjangan;
use App\Models\Unitkerja;
use Carbon\Carbon;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KontrakindukController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('lihat-kontrakinduk'), only: ['index']),
            new Middleware(PermissionMiddleware::using(['edit-kontrakinduk', 'tambah-kontrakinduk']), only: ['store']),
            new Middleware(PermissionMiddleware::using('approval-kontrakinduk'), only: ['list_approval']),
        ];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Kontrakinduk::whereNull('deleted_at')
                ->with([
                    'settingHarga',
                    'adendum',
                    'approvalPending',
                    'approval',
                ])
                // ->withCount('adendum')
                ->orderBy('id', 'desc')
                ->orderByRaw("CASE
                        WHEN status = 'ENABLED' THEN 1
                        WHEN status = 'DISABLED' THEN 2
                        ELSE 3 END
                    ")
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('nomor', function ($row) {
                    $jenis = $row['jenis_kontrakinduk'];

                    if ($row['status'] == 'ENABLED') {
                        $status = '<span class="badge rounded-pill bg-label-success mb-2"><i class="fas fa-check me-1"></i>AKTIF</span>';
                    } else {
                        $status = '<span class="badge rounded-pill bg-label-danger mb-2"><i class="fas fa-ban me-1"></i>TIDAK AKTIF</span>';
                    }

                    return $row['nomor'] .
                        '<br><span class="badge bg-label-' . ($jenis == 'SDM' ? 'success' : 'primary') . ' mt-1 mb-1">KONTRAK ' . $jenis . '</span>
                        <br>' . $status;
                })
                ->addColumn('periode', function ($row) {
                    $endDate = Carbon::parse($row['end'])->startOfDay();
                    $today   = Carbon::today();

                    return Carbon::parse($row['start'])->translatedFormat('d F Y') . '<br>sampai<br>
                    <span class="' . ($endDate->lessThan($today) ? 'text-danger' : '') . '">' . Carbon::parse($row['end'])->translatedFormat('d F Y') . '</span>';
                })
                ->addColumn('judul', function ($row) {
                    $catatan = $row['judul'];
                    $shortText = Str::limit($catatan, 25, '...'); // Potong teks untuk tampilan awal
                    return $shortText . '<br>' . (strlen($catatan) > 25 ? '<button class="btn btn-label-success btn-xs read-more mt-1" data-bs-toggle="tooltip" 
                            data-bs-placement="top" 
                            data-bs-custom-class="tooltip-dark" 
                            title="' . e($catatan) . '" 
                            style="cursor: pointer;" data-full="' . e($catatan) . '">Hover me</button>' : '') . '
                    ';
                })
                ->addColumn('setting', function ($row) {
                    $settings = $row['setting_harga'];
                    $color = ['text-success', 'text-warning', 'text-primary', 'text-info', 'text-secondary', 'text-dark'];

                    $allProjectsHtml = [];
                    $totalSettings = count($settings);
                    // Gunakan $index untuk mendapatkan nomor urut (dimulai dari 0, jadi kita tambah 1)
                    foreach ($settings as $index => $set) {

                        $payloadSetting = [
                            'id'  => $set['id'],
                            'exp' => time() + 3600  // optional, expired 1 jam
                        ];

                        $tokenSetting = JWT::encode($payloadSetting, config('jwt.secret'), 'HS256');

                        $nomorUrut = $index + 1;
                        // 1. Berikan warna pada setiap item lokasi
                        $lokasiProjectFormatted = array_map(
                            fn($item, $class) => "<span class='$class fw-bold d-flex'>$item</span>",
                            $set['lokasi_project'],
                            array_slice($color, 0, count($set['lokasi_project']))
                        );
                        // 2. Gabungkan teks lokasi untuk project saat ini
                        $stringLokasi = implode(' ', $lokasiProjectFormatted);
                        // Anda bisa menambahkan sedikit style agar nomor urut terlihat jelas
                        $jumlahAdendumPerSetting = 0;
                        if (isset($row['adendum']) && is_array($row['adendum'])) {
                            foreach ($row['adendum'] as $adendum) {
                                if ($adendum['setting_id'] == $set['id']) {
                                    $jumlahAdendumPerSetting++;
                                }
                            }
                        }

                        $htmlContent = "<div class='d-flex align-items-start'>" .
                            (($totalSettings > 1) ? "<span class='fw-bold me-2 text-dark'>{$nomorUrut}.</span>" : "") .
                            "<div>{$stringLokasi}</div>" .
                            "</div>" .
                            "<span class='badge rounded-pill bg-label-info'>ADENDUM KE : {$jumlahAdendumPerSetting} </span><br>";

                        if (auth()->user()->can('tambah-kontrakinduk')) {
                            // Titik pembungkus token dihilangkan agar token JWT utuh
                            $htmlContent .= "
                                <div class='btn-group'>
                                    <button class='btn btn-xs btn-secondary mt-1 DetailSetting' data-token='{$tokenSetting}'><i class='ti ti-eye me-1'></i>Detail</button>
                                ";
                            if ($row['status'] == "ENABLED") {
                                // 💡 1. CARI APAKAH ADA ADENDUM YANG MATCH DENGAN $set['id'] SAAT INI
                                $hasMatchingAdendum = false;
                                $tokenAdendum = 0;
                                // Pastikan key 'adendum' ada dan merupakan array/hasMany
                                if (isset($row['adendum']) && is_array($row['adendum'])) {
                                    // Looping isi hasMany adendum untuk mencari yang setting_id nya cocok
                                    foreach ($row['adendum'] as $adendum) {

                                        if ($adendum['setting_id'] == $set['id']) {
                                            $hasMatchingAdendum = true;

                                            $payloadAdendum = [
                                                'id'  => $adendum['id'],
                                                'exp' => time() + 3600  // optional, expired 1 jam
                                            ];

                                            $tokenAdendum = JWT::encode($payloadAdendum, config('jwt.secret'), 'HS256');

                                            // break; // Hentikan loop jika sudah ketemu satu yang cocok
                                        }
                                    }
                                }
                                // Jika array adendum TIDAK kosong, tombol Edit muncul
                                if ($hasMatchingAdendum) {
                                    $htmlContent .= "<button class='btn btn-xs btn-warning mt-1 EditAdendum' data-token='{$tokenAdendum}' data-setting='{$set['id']}'><i class='ti ti-pencil me-1'></i>Edit</button>";
                                }
                                // Tombol Tambah Adendum selalu muncul jika status ENABLED
                                $htmlContent .= "<button class='btn btn-xs btn-primary mt-1 Adendum' data-token='{$tokenSetting}'><i class='ti ti-plus me-1'></i>Adendum</button>";
                            }

                            $htmlContent .= "</div>";
                        }

                        $allProjectsHtml[] = $htmlContent;
                    }

                    // 4. Gabungkan antar blok project dengan tag <hr>
                    return implode("<hr class='my-1'>", $allProjectsHtml);
                })

                ->addColumn('action', function ($row) {
                    $endDate = Carbon::parse($row['end'])->startOfDay();
                    $today   = Carbon::today();
                    $duaBulanLagi = Carbon::today()->addMonth(2); // 2 bulan dari sekarang

                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    $button = '<div class="d-flex flex-column gap-2">';

                    if ($row['status'] == "ENABLED") {

                        if (auth()->user()->can('edit-kontrakinduk')) {
                            if ($endDate->lessThan($duaBulanLagi)) {
                                $button .= '<button class="btn btn-xs btn-info Perpanjang" data-token=' . $token . ' ><i class="ti ti-heart me-1"></i>Perpanjang</button>';
                            } else {

                                if (!isset($row['adendum']) || empty($row['adendum'])) {
                                    $button .= '<button class="btn btn-xs btn-warning Edit" data-token=' . $token . ' ><i class="ti ti-pencil me-1"></i>Edit Kontrakinduk</button>';
                                }
                            }
                        }
                        if (auth()->user()->can('hapus-kontrakinduk')) {
                            $button .= '<button class="btn btn-sm btn-danger Nonaktifkan" data-token=' . $token . ' data-nomor=' . $row['nomor'] . '><i class="fas fa-power-off me-1"></i>Nonaktifkan</button>';
                        }

                        if (!auth()->user()->can('edit-kontrakinduk') && !auth()->user()->can('hapus-kontrakinduk')) {
                            $button .= '<span class="badge rounded-pill bg-label-danger"><i class=" fas fa-lock me-1"></i>Tidak ada izin</span>';
                        }

                        $pendingApprovals = array_filter($row['approval'] ?? [], function ($item) {
                            return isset($item['status']) && $item['status'] === 'PENDING';
                        });

                        $lastPending = !empty($pendingApprovals) ? end($pendingApprovals) : null;

                        $statusApproval    = $lastPending['status'] ?? ''; // Pasti PENDING jika ada
                        $aktivitasApproval = $lastPending['aktivitas'] ?? '';

                        [$warna, $keterangan] = match ($aktivitasApproval) {
                            'EDIT KONTRAK INDUK'     => ['text-warning', 'Perubahan'],
                            'EDIT ADENDUM'           => ['text-primary', 'Perubahan Adendum'],
                            'TAMBAH ADENDUM'           => ['text-primary', 'Penambahan Adendum'],
                            'NONAKTIF KONTRAK INDUK' => ['text-danger', 'Penonaktifan'],
                            default                  => ['text-light', ''],
                        };

                        if ($statusApproval === "PENDING") {
                            $button .= "<div class='d-flex align-items-center lh-1 me-3 mb-3 mb-sm-0 text-light'>
                            <div class='spinner-grow {$warna}' role='status'></div>
                                Menunggu Persetujuan {$keterangan}
                            </div>";
                        }
                    } else {

                        $button .= '
                            <span class="badge rounded-pill bg-label-danger"><i class="fas fa-info-circle me-1"></i>Data Sudah Dinonaktifkan</span>'
                            . ($row['parent_id'] == null ? '' : '<small class="badge text-dark"> <i class="fa fa-quote-right fa-xs me-1 text-info"></i>PERPANJANGAN KONTRAK</small>') . '
                        ';
                    }

                    $button .= '</div>';


                    return $button;
                })

                ->rawColumns([
                    'nomor'    => 'nomor',
                    'judul'    => 'judul',
                    'periode'  => 'periode',
                    'setting' => 'setting',
                    'action'   => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('kontrakinduk.kontrakinduk-list', [
            'judul' => 'Kontrak Induk'
        ]);
    }

    public function kontrakinduk_tambah(Request $request)
    {
        return view('kontrakinduk.kontrakinduk-tambah', [
            'judul' => 'Tambah Kontrak Induk'
        ]);
    }

    public function store(Request $request)
    {
        $allInput = $request->all();

        // 2. FORCE PARSING: Ubah string literal flat menjadi multidimensional array murni
        $parsedData = [];
        foreach ($allInput as $key => $value) {
            // Cari key yang memiliki pola format seperti "setting[0][project_id]" atau "setting[1][nominal][0][0]"
            if (preg_match('/^setting\[(\d+)\]\[(\w+)\]/', $key, $matches)) {
                $index = $matches[1]; // Mengambil angka indeks (0, 1, dst)
                $field = $matches[2]; // Mengambil nama field (project_id, project_detail, atau nominal)

                // Jika field berupa nominal bertingkat (misal: "setting[0][nominal][0][0]")
                if ($field === 'nominal') {
                    // Bongkar sisa indeks terdalam [0][0] menggunakan parse_str
                    parse_str($key . '=' . urlencode($value), $subOutput);

                    // Satukan langsung ke dalam indeks yang benar
                    if (!isset($parsedData['setting'][$index]['nominal'])) {
                        $parsedData['setting'][$index]['nominal'] = [];
                    }
                    $parsedData['setting'][$index]['nominal'] = array_merge_recursive(
                        $parsedData['setting'][$index]['nominal'],
                        $subOutput['setting'][$index]['nominal']
                    );
                } else {
                    // Jika field biasa seperti project_id atau project_detail, masukkan langsung sesuai nomor indeksnya
                    $parsedData['setting'][$index][$field] = $value;
                }
            } else {
                // Untuk data global seperti unitkerja[] dan tunjangan[] tetap dimasukkan normal
                $parsedData[$key] = $value;
            }
        }

        $validator = Validator::make(
            $parsedData,
            [
                'judul'      => 'required',
                'nomor'      => [
                    'required',
                    'regex:/^\S+$/',
                    Rule::unique('kontrakinduk', 'nomor')
                        ->ignore($request->idinduk)
                        ->where(function ($query) {
                            $query->whereNull('deleted_at');
                        }),
                ],
                'jenis'                => 'required',
                'tglterbit'            => 'required|date',
                'dari'                 => 'required|date',
                'sampai'               => 'required|date|after_or_equal:dari',
                'keterangan'           => 'required_if:status,1',
                'setting'               => 'required|array|min:1',
                'setting.*.project_id'  => 'required',
                'setting.*.nominal'     => 'required|array',
                'setting.*.nominal.*'   => 'required|array',
                'setting.*.nominal.*.*' => [
                    'required',
                    'string',
                    'regex:/^[0-9.]+(,[0-9]{2})?$/'
                ],

                'file' => $request->hasFile('file')
                    ? 'required|file|mimes:pdf|max:2048'
                    :  'nullable',

            ],
            [
                'judul.required'                 => 'Judul Kontrak Induk masih kosong.',
                'nomor.required'                 => 'Nomor Kontrak Induk masih kosong.',
                'nomor.unique'                   => 'Nomor Kontrak Induk Sudah pernah diinput.',
                'nomor.regex'                    => 'Nomor Kontrak Induk tidak boleh menggunakan spasi.',
                'project.required'               => 'Keperluan Project Kontrak Induk masih kosong.',
                'project.unique'                 => 'Project Kontrak Induk Sudah pernah diinput.',
                'jenis.required'                 => 'Jenis Kontrak Induk masih kosong.',
                'tglterbit.required'             => 'Tanggal Terbit Kontrak Induk masih kosong.',
                'dari.required'                  => 'Tanggal Mulai Kontrak Induk masih kosong.',
                'sampai.required'                => 'Tanggal Akhir Kontrak Induk masih kosong.',
                'keterangan.required'            => 'Keterangan Kontrak Induk masih kosong.',
                'setting.required'               => "Data setting project tidak ditemukan atau kosong.",
                'setting.*.project_id.required'  => "Lokasi project pada 'Setting Project #:position' belum dipilih.",
                'setting.*.nominal.*.*.required' => "Ada nominal biaya pada 'Setting Project #:position' yang masih kosong.",
                'setting.*.nominal.*.*.regex'    => "Format nominal uang pada 'Setting Project #:position' harus berupa angka.",
                'file.required'                  => 'File Kontrak Induk masih kosong.',
                'file.max'                       => 'Ukuran file maximal 2Mb.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            $unitKerjaInput = array_map('strtoupper', $request->input('unitkerja[]') ?? $request->input('unitkerja') ?? []);
            $unitList = $request->input('unitkerja', []);
            foreach ($unitKerjaInput as $val) {
                $namaUnitkerja = strtoupper(trim($val));
                if ($namaUnitkerja !== '') {
                    $unitList[] = $namaUnitkerja;
                    Unitkerja::firstOrCreate(
                        ['name' => $namaUnitkerja],
                        ['user_id' => auth()->id(), 'updated_at' => now()]
                    );
                }
            }

            // 💡 OPTIMASI 1: Ambil semua ID unit kerja sekaligus dari DB, jadikan Array Map [ 'PAB' => 1, 'AVSEC' => 2 ]
            // Ini menghemat 36+ query di dalam loop bawah menjadi hanya 1 query saja.
            $unitMapFromDb = Unitkerja::whereIn('name', $unitList)->pluck('id', 'name')->toArray();

            // 2. Proses & Sinkronisasi Master Tunjangan (Dibersihkan dari filter parsial agar UPAH/THR ikut masuk)
            $tunjanganList = array_map('strtoupper', $request->input('tunjangan[]') ?? $request->input('tunjangan') ?? []);
            if (is_array($tunjanganList)) {
                foreach ($tunjanganList as $key => $val) {
                    $namaTunjangan = strtoupper(trim($val));
                    // 2. Filter: Proses jika diawali 'TUNJANGAN' ATAU diawali 'TUNJ'
                    if (str_starts_with($namaTunjangan, 'TUNJANGAN') || str_starts_with($namaTunjangan, 'TUNJ')) {
                        // 3. Jalankan logika database (Gunakan updateOrCreate sesuai permintaan Anda)
                        Tunjangan::updateOrCreate(
                            [
                                'name' => $namaTunjangan, // Kondisi pencarian
                            ],
                            [
                                'user_id'    => auth()->id(), // Data yang dibuat/diperbarui
                                'updated_at' => now()
                            ]
                        );
                    }
                }
            }

            // Rekonstruksi data matriks flat "setting[x]..." menjadi Array Multidimensi asli PHP
            // $settings = [];
            $settings = $request->input('setting', []);
            foreach ($request->all() as $key => $value) {
                if (preg_match('/^setting\[(\d+)\]\[(project_id|project_detail)\]$/', $key, $matches)) {
                    $index = $matches[1];
                    $field = $matches[2];
                    $settings[$index][$field] = $value;
                } elseif (preg_match('/^setting\[(\d+)\]\[nominal\]\[(\d+)\]\[(\d+)\]$/', $key, $matches)) {
                    $index = $matches[1];
                    $biayaIdx = $matches[2];
                    $unitIdx = $matches[3];
                    $settings[$index]['nominal'][$biayaIdx][$unitIdx] = $value;
                }
            }

            if (empty($settings)) {
                return response()->json(['status' => 'error', 'message' => 'Data setting project tidak ditemukan.'], 422);
            }

            $namaFile = null;
            // Menentukan Nama Gambar
            if ($request->hasFile('file')) {
                $extension = $request->file('file')->getClientOriginalExtension();
                $filename = 'Kontrakinduk_' . Str::random(10) . now()->timestamp . '.' . $extension;

                Storage::disk('public')->putFileAs('hris/kontrakinduk/', $request->file('file'), $filename);
                $namaFile = $filename;
            } else {
                $namaFile = $request->file_lama;
            }

            DB::beginTransaction();
            try {
                $idInduk = $request->input('idinduk') ?: null;

                ## AKSI EDIT
                if (!is_null($idInduk)) {
                    $payloadData = $request->except(['_token']);

                    if ($namaFile) {
                        $payloadData['file_upload_name'] = $namaFile;
                    }

                    # 🔥 AMBIL SNAPSHOT DATA LAMA SEBELUM BERUBAH
                    // Merujuk pada fungsi Anda untuk mengambil data struktur lengkap
                    $snapshotDataLama = $this->getStrukturKontrakLengkap($idInduk);
                    foreach ($settings as $index => $dataProject) {
                        // 1. Cek apakah project_id ini sudah terdaftar di kontrak induk yang aktif
                        $isExist = KontrakIndukSettingHarga::where('project_id', $dataProject['project_id'])
                            ->whereHas('kontrakinduk', function ($query) use ($idInduk) {
                                $query->whereNot('kontrakinduk_id', $idInduk)
                                    ->where('status', 'ENABLED');
                            })
                            ->exists();

                        // 2. Jika sudah ada, lempar error dan stop eksekusi
                        if ($isExist) {
                            throw ValidationException::withMessages([
                                "errors" => "SETTING PROJECT #" . ($index + 1) . " sudah terdaftar pada kontrak induk yang sedang aktif."
                            ]);
                        }
                    }
                    // Simpan ke antrian approval dengan dua kondisi (Lama vs Baru)
                    KontrakIndukApprovalRequest::updateOrCreate(
                        [
                            'kontrakinduk_id' => $idInduk,
                            'status'          => 'PENDING',
                            'aktivitas'       => 'EDIT KONTRAK INDUK',
                        ],
                        [
                            'keterangan'      => trim(strtoupper($request->input('keterangan'))),
                            'data_lama'       => $snapshotDataLama ? $snapshotDataLama->toArray() : null,
                            'payload_request' => $payloadData,
                            'requested_by'    => auth()->id(),
                        ]
                    );

                    DB::commit();
                    return response()->json(['message' => 'Perubahan data berhasil dikirim dan menunggu persetujuan.'], 200);
                }

                ## AKSI SIMPAN BARU

                # CREATE KONTRAK INDUK
                $kontrak = Kontrakinduk::create([
                    'nomor'              => trim(strtoupper($request->input('nomor'))),
                    'judul'              => trim(strtoupper($request->input('judul'))),
                    'tanggal_terbit'     => $request->input('tglterbit'),
                    'jenis_kontrakinduk' => $request->input('jenis'),
                    'keterangan'         => trim(strtoupper($request->input('keterangan'))),
                    'start'              => $request->input('dari'),
                    'end'                => $request->input('sampai'),
                    'status'             => 'ENABLED',
                    'file'               => $namaFile,
                ]);

                foreach ($settings as $index => $dataProject) {
                    // 1. Cek apakah project_id ini sudah terdaftar di kontrak induk yang aktif
                    $isExist = KontrakIndukSettingHarga::where('project_id', $dataProject['project_id'])
                        ->whereHas('kontrakinduk', function ($query) {
                            $query->where('status', 'ENABLED');
                        })
                        ->exists();

                    // 2. Jika sudah ada, lempar error dan stop eksekusi
                    if ($isExist) {
                        throw ValidationException::withMessages([
                            "errors" => "SETTING PROJECT #" . ($index + 1) . " sudah terdaftar pada kontrak induk yang sedang aktif."
                        ]);
                    }
                    # CREATE KONTRAK INDUK SETTING HARGA
                    $settingHarga = KontrakIndukSettingHarga::create([
                        'kontrakinduk_id'       => $kontrak->id,
                        'project_id'            => $dataProject['project_id'],
                        'project_detail'        => $dataProject['project_detail'] ?? '',
                        'tanggal_mulai_berlaku' => $kontrak->start,
                        'tanggal_akhir_berlaku' => $kontrak->end,
                    ]);

                    # CREATE KONTRAK INDUK BIAYA
                    if (isset($dataProject['nominal'])) {
                        foreach ($dataProject['nominal'] as $biayaIdx => $columns) {
                            foreach ($columns as $unitIdx => $nominalString) {
                                $namaUnitKerja = $unitList[$unitIdx] ?? null;
                                $unitkerjaId = strtoupper(trim($namaUnitKerja)) ? ($unitMapFromDb[strtoupper(trim($namaUnitKerja))] ?? null) : null;
                                $namaJenisBiaya = $tunjanganList[$biayaIdx] ?? 'JENIS BIAYA';
                                $hargaBersih = (float) str_replace('.', '', $nominalString);

                                $biaya = KontrakIndukBiaya::create([
                                    'setting_harga_id' => $settingHarga->id,
                                    'unitkerja_id'     => $unitkerjaId,
                                    'jenis_biaya'      => $namaJenisBiaya,
                                    'harga'            => $hargaBersih,
                                    'sort_order'       => (int)$biayaIdx + 1
                                ]);
                            }
                        }
                    }
                }

                # PENCATATAN LOG MUTASI DATA BARU (CREATE)
                $dataSesudah = $this->getStrukturKontrakLengkap($kontrak->id);
                KontrakIndukLog::create([
                    'kontrakinduk_id' => $kontrak->id,
                    // 'adendum_id'      => null,
                    'aktivitas'       => 'CREATED_KONTRAK_INDUK',
                    'keterangan'      => trim(strtoupper($request->input('keterangan'))),
                    'user_id'         => auth()->id(),
                    'data_lama'       => null,
                    'data_baru'       => $dataSesudah
                ]);

                DB::commit();
                return response()->json(['message' => 'Kontrak Induk Berhasil Disimpan'], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Gagal memproses data: ' . $e->getMessage()], 500);
            }
        }
    }

    private function getStrukturKontrakLengkap($id)
    {
        if (!$id) return null;

        if (!$id) return null;

        return Kontrakinduk::with([
            'settingHarga' => function ($query) {
                $query->orderBy('id', 'asc');
            },
            'settingHarga.biaya' => function ($query) {
                $query->orderBy('id', 'asc');
            }
        ])->find($id);
    }

    private function getStrukturAdendum($id)
    {
        if (!$id) return null;

        if (!$id) return null;

        return KontrakIndukAdendum::with([
            'settingHarga' => function ($query) {
                $query->orderBy('id', 'asc');
            },
            'settingHarga.biaya' => function ($query) {
                $query->orderBy('id', 'asc');
            }
        ])->find($id);
    }

    public function list_approval(Request $request)
    {
        if ($request->ajax()) {
            $query = KontrakIndukApprovalRequest::whereNull('deleted_at')
                ->with([
                    'kontrakinduk',
                    'adendum',
                    'requester:id,name',
                    'approver:id,name',
                ])
                ->orderByRaw("CASE
                WHEN status = 'PENDING' THEN 1
                ELSE 2 END
                ")
                ->orderBy('id', 'desc')
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('jenis', function ($row) {
                    $kelasWarna = match ($row['aktivitas']) {
                        'EDIT ADENDUM'           => 'text-warning',
                        'TAMBAH ADENDUM'         => 'text-warning',
                        'NONAKTIF KONTRAK INDUK' => 'text-danger',
                        default                  => 'text-info',
                    };

                    return '<strong class="' . $kelasWarna . '">' . str_replace('EDIT', 'PERUBAHAN', $row['aktivitas']) .
                        '</strong><br><i class="fas fa-quote-right me-1 fa-xs ' . $kelasWarna . '"></i>'
                        . $row['keterangan'] .
                        '<i class="fas fa-quote-right fa-xs ms-1 ' . $kelasWarna . '"></i>';
                })
                ->addColumn('nomor', function ($row) {
                    return ($row['adendum'] == null ? $row['kontrakinduk']['nomor'] : $row['adendum']['nomor_adendum']);
                })
                ->addColumn('peminta', function ($row) {
                    return '<span class="badge">' . $row['requester']['name'] . '</span>
                        <br><small class="badge fs-xs text-light">' . Carbon::parse($row['updated_at'])->format('d-M-Y') . '</small>';
                })
                ->addColumn('status', function ($row) {
                    if ($row['status'] == 'PENDING') {
                        $status = '<span class="badge rounded-pill bg-label-warning">Menunggu Persetujuan</span>';
                    } else if ($row['status'] == 'APPROVED') {
                        $status = '<span class="badge rounded-pill bg-label-success">Selesai</span>
                        <br><small class="badge fs-xs text-light">by ' . $row['approver']['name'] . ': ' . Carbon::parse($row['updated_at'])->format('Y-m-d H:i:s') . '</small>';
                    } else {
                        $status = '<span class="badge rounded-pill bg-label-danger">Ditolak</span>
                        <br><small class="badge fs-xs text-light">by ' . $row['approver']['name'] . ': ' . Carbon::parse($row['updated_at'])->format('Y-m-d H:i:s') . '</small>';
                    }
                    return $status;
                })
                ->addColumn('action', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $token = JWT::encode($payload, config('jwt.secret'), 'HS256');
                    return '<button class="btn btn-sm btn-dark Detail btn-xs" data-token=' . $token . ' ><i class="ti ti-eye me-1"></i>Detail</button>';
                })

                ->rawColumns([
                    'jenis'   => 'jenis',
                    'nomor'   => 'nomor',
                    'peminta' => 'peminta',
                    'status'  => 'status',
                    'action'  => 'action'
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('kontrakinduk.kontrakinduk-approval', [
            'judul' => 'Data Persetujuan Kontrak Induk / Adendum'
        ]);
    }

    public function proses_approval_kontrakinduk(Request $request)
    {
        $token   = $request->input('idreq');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $approval = KontrakIndukApprovalRequest::findOrFail($id);
        if ($approval->status !== 'PENDING') {
            return response()->json(['message' => 'Permintaan ini sudah diproses sebelumnya.'], 400);
        }

        // 💡 Trik Inti: Konversi balik array JSON payload menjadi object Request tiruan (Fake Request)
        $payload = $approval->payload_request;
        $fakeRequest = new \Illuminate\Http\Request($payload);

        DB::beginTransaction();
        try {
            $idInduk = $approval->kontrakinduk_id;

            # A. LOCK DATA SEBELUM PERUBAHAN (Kondisi Eksisting)
            $dataSebelum = $this->getStrukturKontrakLengkap($idInduk);

            // Siapkan variabel helper loops dari fake request
            $unitList = array_map(fn($v) => strtoupper(trim($v)), $fakeRequest->input('unitkerja', []));
            $tunjanganList = array_map(fn($v) => strtoupper(trim($v)), $fakeRequest->input('tunjangan', []));
            $settings = $fakeRequest->input('setting', []);

            $unitMapFromDb = Unitkerja::whereIn('name', $unitList)
                ->pluck('id', 'name')
                ->toArray();

            # 1. UPDATE DATA UTAMA KONTRAK INDUK
            $kontrak = Kontrakinduk::updateOrCreate(
                [
                    'id' => $idInduk
                ],
                [
                    'nomor'              => trim(strtoupper($fakeRequest->input('nomor'))),
                    'judul'              => trim(strtoupper($fakeRequest->input('judul'))),
                    'tanggal_terbit'     => $fakeRequest->input('tglterbit'),
                    'jenis_kontrakinduk' => $fakeRequest->input('jenis'),
                    'start'              => $fakeRequest->input('dari'),
                    'end'                => $fakeRequest->input('sampai'),
                    'file'               => $payload['file_lama'] ?? ($dataSebelum ? $dataSebelum->file : null),
                ]
            );

            $keepSettingHargaIds = [];
            $keepBiayaIds = [];

            # 2. UPSERT DATA MATRIKS BARU
            foreach ($settings as $index => $dataProject) {
                $settingHarga = KontrakIndukSettingHarga::updateOrCreate(
                    [
                        'kontrakinduk_id' => $kontrak->id,
                        'project_id'      => $dataProject['project_id'],
                    ],
                    [
                        'project_detail'        => $dataProject['project_detail'] ?? '',
                        'tanggal_mulai_berlaku' => $kontrak->start,
                        'tanggal_akhir_berlaku' => $kontrak->end,
                    ]
                );

                $keepSettingHargaIds[] = $settingHarga->id;

                if (isset($dataProject['nominal'])) {
                    foreach ($dataProject['nominal'] as $biayaIdx => $columns) {
                        foreach ($columns as $unitIdx => $nominalString) {
                            $namaUnitKerja = $unitList[$unitIdx] ?? null;
                            $unitkerjaId = $namaUnitKerja ? ($unitMapFromDb[$namaUnitKerja] ?? null) : null;
                            $namaJenisBiaya = $tunjanganList[$biayaIdx] ?? 'JENIS BIAYA';
                            $hargaBersih = (float) str_replace('.', '', $nominalString);

                            $biaya = KontrakIndukBiaya::updateOrCreate(
                                [
                                    'setting_harga_id' => $settingHarga->id,
                                    'unitkerja_id'     => $unitkerjaId,
                                    'jenis_biaya'      => $namaJenisBiaya
                                ],
                                [
                                    'harga'      => $hargaBersih,
                                    'sort_order' => (int)$biayaIdx + 1
                                ]
                            );

                            $keepBiayaIds[] = $biaya->id;
                        }
                    }
                }
            }

            # 3. CLEANUP SINKRONISASI (Menghapus row/header lama yang dibuang user saat edit)
            KontrakIndukBiaya::whereIn('setting_harga_id', $keepSettingHargaIds)
                ->whereNotIn('id', $keepBiayaIds)
                ->delete();

            KontrakIndukSettingHarga::where('kontrakinduk_id', $kontrak->id)
                ->whereNotIn('id', $keepSettingHargaIds)
                ->delete();

            # B. LOCK DATA SESUDAH PERUBAHAN (Kondisi Terbaru)
            $dataSesudah = $this->getStrukturKontrakLengkap($kontrak->id);

            # C. PROSES SANITASI & INPUT AUDIT TRAIL LOG
            $arraySebelum = $dataSebelum ? $dataSebelum->toArray() : [];
            $arraySesudah = $dataSesudah ? $dataSesudah->toArray() : [];

            $cleaner = function (&$item) use (&$cleaner) {
                foreach ($item as $key => &$value) {
                    if (in_array($key, ['created_at', 'updated_at', 'deleted_at'])) {
                        unset($item[$key]);
                        continue;
                    }
                    if (is_numeric($value)) $value = (string) $value;
                    if (is_array($value)) $cleaner($value);
                }
            };

            $cleaner($arraySebelum);
            $cleaner($arraySesudah);

            // Rekam log mutasi riwayat tabel utama
            if (json_encode($arraySebelum) !== json_encode($arraySesudah)) {
                KontrakIndukLog::create([
                    'kontrakinduk_id' => $kontrak->id,
                    'aktivitas'       => 'EDITED_VIA_APPROVAL',
                    'keterangan'      => 'PERUBAHAN DISETUJUI OLEH ' . strtoupper(auth()->user()->name),
                    'user_id'         => $approval->requested_by, // Log merujuk pada staff pengaju awal
                    'data_lama'       => $arraySebelum,
                    'data_baru'       => $arraySesudah
                ]);
            }

            # 4. UPDATE STATUS LEMBAR REKUES APPROVAL
            $approval->update([
                'status'      => 'APPROVED',
                'approved_by' => auth()->id()
            ]);

            DB::commit();
            return response()->json(['message' => 'Perubahan sukses disetujui dan data sistem telah diperbarui.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menyetujui request: ' . $e->getMessage()], 500);
        }
    }

    public function proses_reject_kontrakinduk(Request $request)
    {
        $request->validate([
            'alasan_reject' => 'required|string|max:500'
        ]);

        $token   = $request->input('idreq');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $approval = KontrakIndukApprovalRequest::findOrFail($id);

        if ($approval->status !== 'PENDING') {
            return response()->json(['message' => 'Perubahan data ini sudah diproses sebelumnya.'], 400);
        }

        $approval->update([
            'status'            => 'REJECTED',
            'keterangan_reject' => trim(strtoupper($request->input('alasan_reject'))),
            'approved_by'       => auth()->id()
        ]);

        return response()->json(['message' => 'Perubahan data berhasil ditolak.'], 200);
    }

    public function showApprovalKontrakinduk(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $approval = KontrakIndukApprovalRequest::whereNull('deleted_at')
                ->with([
                    'kontrakinduk',
                    'adendum',
                    'requester:id,name',
                    'approver:id,name',
                ])
                ->findOrFail($id);

            //     // Pastikan data sudah dicast menjadi array (bisa menggunakan json_decode jika belum dicast di model)
            // $old = is_string($approval->data_lama) ? json_decode($approval->data_lama, true) : $approval->data_lama;
            // $new = is_string($approval->payload_request) ? json_decode($approval->payload_request, true) : $approval->payload_request;

            $old = $approval->data_lama;
            $new = $approval->payload_request;

            ## DETEKSI PERBEDAAN REDAKSI (INFORMASI UMUM)
            // =========================================================================
            $redaksiDiff = [];
            $fieldsToCompare = [
                'nomor'      => ['label' => 'Nomor ' . (Str::contains($approval->aktivitas, 'ADENDUM') ? 'Adendum' : 'Kontrak'), 'old_key' => 'nomor', 'new_key' => 'nomor'],
                'judul'      => ['label' => 'Judul ' . (Str::contains($approval->aktivitas, 'ADENDUM') ? 'Adendum' : 'Kontrak'), 'old_key' => 'judul', 'new_key' => 'judul'],
                'tglterbit'  => ['label' => 'Tanggal Terbit', 'old_key' => 'tanggal_terbit', 'new_key' => 'tglterbit'],
                'jenis'      => ['label' => 'Jenis Kontrak', 'old_key' => 'jenis_kontrakinduk', 'new_key' => 'jenis'],
                'dari'       => ['label' => 'Tanggal Mulai', 'old_key' => 'start', 'new_key' => 'dari'],
                'sampai'     => ['label' => 'Tanggal Selesai', 'old_key' => 'end', 'new_key' => 'sampai'],
                'keterangan' => ['label' => 'Keterangan', 'old_key' => 'keterangan', 'new_key' => 'keterangan'],
            ];

            foreach ($fieldsToCompare as $field => $meta) {
                $valOld = trim($old[$meta['old_key']] ?? '');
                $valNew = trim($new[$meta['new_key']] ?? '');

                if (strtoupper($valOld) !== strtoupper($valNew)) {
                    $redaksiDiff[] = [
                        'label'   => $meta['label'],
                        'dari'    => $valOld ?: '-',
                        'menjadi' => $valNew ?: '-'
                    ];
                }
            }

            ## STRUKTURISASI MATRIKS BIYAYA UNTUK BLADE
            // =========================================================================
            $matrixTables = [];

            $unitkerjaHeaders = $new['unitkerja'] ?? [];
            $tunjanganRows = $new['tunjangan'] ?? [];
            $settingsNew = $new['setting'] ?? [];

            // 💡 1. Ambil daftar project_id dari data lama untuk pemetaan status perubahan proyek
            $oldProjects = [];
            // if (isset($old['setting_harga'])) {
            //     foreach ($old['setting_harga'] as $sh) {
            //         $oldProjectName = isset($sh['lokasi_project']) ? implode(' - ', $sh['lokasi_project']) : "Project ID: " . $sh['project_id'];
            //         $oldProjects[$sh['project_id']] = [
            //             'name' => $oldProjectName,
            //             'data' => $sh
            //         ];
            //     }
            // }
            if (isset($old['setting_harga']) && (is_array($old['setting_harga']) || $old['setting_harga'] instanceof \Illuminate\Support\Collection)) {

                foreach ($old['setting_harga'] as $sh) {
                    // PENCEGAHAN UTAMA: Pastikan $sh adalah array atau objek, bukan integer
                    if (!is_array($sh) && !is_object($sh)) {
                        continue;
                    }

                    // Konversi ke array jika berupa objek Eloquent Model
                    $shArray = is_object($sh) ? $sh->toArray() : $sh;

                    $projectId = $shArray['project_id'] ?? null;
                    if (!$projectId) {
                        continue;
                    }

                    // Ambil lokasi_project dengan aman
                    $lokasiProject = $shArray['lokasi_project'] ?? null;

                    if (is_array($lokasiProject)) {
                        $oldProjectName = implode(' - ', $lokasiProject);
                    } elseif (is_string($lokasiProject)) {
                        $oldProjectName = $lokasiProject;
                    } else {
                        $oldProjectName = "Project ID: " . $projectId;
                    }

                    $oldProjects[$projectId] = [
                        'name' => $oldProjectName,
                        'data' => $shArray
                    ];
                }
            }

            // 💡 2. Loop Proyek-Proyek Baru yang Diajukan (Bisa Proyek Lama / Proyek Baru Tambahan)
            $processedNewProjectIds = [];

            foreach ($settingsNew as $tableIdx => $projectData) {
                $projectId = $projectData['project_id'];
                $processedNewProjectIds[] = (int)$projectId;

                // 🔥 PERBAIKAN UTAMA: Cari nama proyek secara cerdas
                $projectName = "";
                $projectStatus = "TETAP"; // Default status proyek di form

                if (array_key_exists($projectId, $oldProjects)) {
                    // Jika proyek ini memang sudah ada dari data lama
                    $projectName = $oldProjects[$projectId]['name'];
                } else {
                    // ✨ JIKA INI PROYEK BARU YANG DITAMBAHKAN SAAT EDIT: Query langsung ke DB asli
                    $projectDb = Project::find($projectId);
                    if ($projectDb) {
                        $projectName = $projectDb->name;
                    } else {
                        $projectName = "PROYEK BARU (ID: " . $projectId . ")";
                    }
                    $projectStatus = "BARU"; // Penanda untuk Blade
                }

                $tableGrid = [];
                $allUnitKerjaDb = Unitkerja::pluck('name', 'id')->toArray();

                foreach ($tunjanganRows as $biayaIdx => $namaBiaya) {
                    foreach ($unitkerjaHeaders as $unitIdx => $namaUnit) {

                        // Ambil Nilai BARU
                        $rawNominalNew = $projectData['nominal'][$biayaIdx][$unitIdx] ?? '0';
                        $hargaBaru = (float) str_replace('.', '', $rawNominalNew);

                        // Cari Nilai LAMA
                        $hargaLama = 0;
                        if (isset($oldProjects[$projectId])) {
                            $shLama = $oldProjects[$projectId]['data'];
                            if (isset($shLama['biaya'])) {
                                foreach ($shLama['biaya'] as $b) {
                                    // Cari nama unit kerja berdasarkan 'unitkerja_id' (angka) menggunakan kamus $allUnitKerjaDb
                                    $idUnitLama = $b['unitkerja_id'] ?? null;
                                    $namaUnitLama = $allUnitKerjaDb[$idUnitLama] ?? '';

                                    $matchUnit = strtoupper(trim($namaUnitLama));
                                    $matchBiaya = strtoupper(trim($b['jenis_biaya'] ?? ''));

                                    if ($matchBiaya === strtoupper(trim($namaBiaya)) && $matchUnit === strtoupper(trim($namaUnit))) {
                                        $hargaLama = (float) $b['harga'];
                                        break;
                                    }
                                }
                            }
                        }

                        $tableGrid[$namaBiaya][$namaUnit] = [
                            'lama'       => $hargaLama,
                            'baru'       => $hargaBaru,
                            'is_changed' => ($hargaLama !== $hargaBaru)
                        ];
                    }
                }

                $matrixTables[] = [
                    'project_id'   => $projectId,
                    'project_name' => $projectName,
                    'status_proyek' => $projectStatus,
                    'headers'      => $unitkerjaHeaders,
                    'rows'         => $tunjanganRows,
                    'grid'         => $tableGrid
                ];
            }

            // 💡 3. DETEKSI PROYEK LAMA YANG DIHAPUS USER SAAT EDIT
            // Jika project_id ada di database lama tapi tidak dikirim lagi di payload request baru
            foreach ($oldProjects as $oldProjId => $oldProjData) {
                if (!in_array((int)$oldProjId, $processedNewProjectIds)) {

                    // Daftarkan ke list tabel sebagai status 'DIHAPUS' untuk diinfokan ke approver
                    $matrixTables[] = [
                        'project_id'   => $oldProjId,
                        'project_name' => $oldProjData['name'],
                        'status_proyek' => 'DIHAPUS',
                        'headers'      => [],
                        'rows'         => [],
                        'grid'         => []
                    ];
                }
            }

            $payload = [
                'id'  => $approval->id,
                'exp' => time() + 3600  // optional, expired 1 jam
            ];

            $approval->token = JWT::encode($payload, config('jwt.secret'), 'HS256');

            return view('kontrakinduk.kontrakinduk-detail-approval', [
                'judul' => 'Detail Approval'
            ], compact('approval', 'redaksiDiff', 'matrixTables', 'new'));
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function edit_kontrakinduk(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;
            // Ambil data kontrak induk beserta seluruh relasi harganya dan detail biayanya
            $kontrak = Kontrakinduk::with([
                'settingHarga.biaya.unitkerja',
                'approvalPending' => function ($query) {
                    $query->where('aktivitas', 'EDIT KONTRAK INDUK');
                }
            ])->findOrFail($id);

            $unitkerjaList = []; // Menyimpan objek/array unit kerja [id => nama]
            $tunjanganList = []; // Menyimpan nama tunjangan

            # JIKA SEBELUMNYA SUDAH PERNAH EDIT TAPI BELUM DI APPROVE MAKA AMBIL DARI DATA TSB
            if ($kontrak->approvalPending) {
                $payloadRaw = $kontrak->approvalPending->payload_request;
                $payloadArray = is_string($payloadRaw) ? json_decode($payloadRaw, true) : $payloadRaw;

                if (!empty($payloadArray)) {
                    // 1. Pemetaan field utama
                    $payloadArray['id']                 = ($payloadArray['idinduk'] ?? null);
                    $payloadArray['start']              = $payloadArray['dari'] ?? ($payloadArray['start'] ?? null);
                    $payloadArray['end']                = $payloadArray['sampai'] ?? ($payloadArray['end'] ?? null);
                    $payloadArray['tanggal_terbit']     = $payloadArray['tglterbit'] ?? ($payloadArray['tanggal_terbit'] ?? null);
                    $payloadArray['file']               = $payloadArray['file_upload_name'] ?? ($payloadArray['file'] ?? null);
                    $payloadArray['jenis_kontrakinduk'] = $payloadArray['jenis'] ?? ($payloadArray['jenis_kontrakinduk'] ?? null);
                    $payloadArray['keterangan_old']     = $payloadArray['keterangan'] ?? ($payloadArray['keterangan_old'] ?? null);

                    $kontrakRaw = new Kontrakinduk();
                    $kontrakRaw->forceFill($payloadArray);
                    $kontrakRaw->exists = true;

                    // 2. Ambil list unitkerja dan tunjangan dasar dari payload root
                    $payloadUnitKerja = $payloadArray['unitkerja'] ?? []; // Contoh: ["PAB", "LOAD MASTER"]
                    $payloadTunjangan = $payloadArray['tunjangan'] ?? []; // Contoh: ["UPAH POKOK", "TUNJANGAN KETERAMPILAN"]

                    // 3. Bangun objek relasi settingHarga berdasarkan isi array 'setting'
                    $rawSettings = $payloadArray['setting'] ?? [];
                    $settingHargaCollection = collect($rawSettings)->map(function ($set) use ($payloadUnitKerja, $payloadTunjangan) {

                        $settingHargaModel = new KontrakIndukSettingHarga();
                        $settingHargaModel->forceFill([
                            'project_id'     => $set['project_id'] ?? null,
                            'project_detail' => $set['project_detail'] ?? null,
                        ]);

                        // Pembongkaran matriks koordinat [baris_tunjangan][kolom_unit_kerja]
                        $biayaCollection = collect();
                        $matrixNominal = $set['nominal'] ?? [];

                        foreach ($payloadTunjangan as $biayaIdx => $tunjanganName) {
                            foreach ($payloadUnitKerja as $unitIdx => $unitName) {

                                // Ambil nilai berdasarkan koordinat matriks payload, hilangkan titik jika itu format Rupiah string
                                $rawPrice = $matrixNominal[$biayaIdx][$unitIdx] ?? 0;
                                $cleanPrice = is_string($rawPrice) ? (int)str_replace('.', '', $rawPrice) : (int)$rawPrice;

                                $biayaModel = new KontrakIndukBiaya();
                                $biayaModel->forceFill([
                                    'jenis_biaya'   => $tunjanganName,
                                    'unitkerja_id'  => $unitIdx + 1, // Kita beri ID dummy berurutan agar pencocokan indeks sukses
                                    'harga'         => $cleanPrice,
                                ]);

                                // Buat objek unit kerja tiruan
                                $unitKerjaModel = new Unitkerja();
                                $unitKerjaModel->forceFill([
                                    'id'   => $unitIdx + 1,
                                    'name' => $unitName,
                                ]);
                                $biayaModel->setRelation('unitkerja', $unitKerjaModel);

                                $biayaCollection->push($biayaModel);
                            }
                        }

                        $settingHargaModel->setRelation('biaya', $biayaCollection);
                        return $settingHargaModel;
                    });

                    $kontrakRaw->setRelation('settingHarga', $settingHargaCollection);
                    $kontrakRaw->setRelation('approvalPending', $kontrak->approvalPending);

                    $kontrak = $kontrakRaw;
                }
            }

            // END
            // --- EKSTRAKSI DATA UNTUK STEP 2 ---

            foreach ($kontrak->settingHarga as $setHarga) {
                foreach ($setHarga->biaya as $biaya) {
                    if ($biaya->unitkerja) {
                        // Simpan ID sebagai key agar tidak duplikat
                        $unitkerjaList[$biaya->unitkerja_id] = $biaya->unitkerja->name;
                    }
                    $namaTunjangan = $biaya->jenis_biaya;
                    $tunjanganList[$namaTunjangan] = $namaTunjangan;
                }
            }

            // Ubah menjadi array biasa untuk looping koordinat indeks di Frontend/Form
            // PENTING: Kita butuh ID Unit Kerja untuk mencocokkan data biaya dari DB
            $unitkerjaIds   = array_keys($unitkerjaList);   // Contoh: [1, 5, 12] (ID dari tabel unitkerja)
            $unitkerjaNames = array_values($unitkerjaList); // Contoh: ['AVSEC', 'PAB', 'HRD'] (Untuk teks header)
            $tunjanganArray = array_values($tunjanganList);  // Contoh: ['UANG MAKAN', 'TRANSPORT']

            // --- EKSTRAKSI DATA UNTUK STEP 3 (MATRIKS) ---
            $settingsFormatted = [];
            foreach ($kontrak->settingHarga as $indexSet => $setHarga) {

                $projectDetail = $setHarga->project_detail;
                if (is_string($projectDetail)) {
                    $projectDetail = explode(',', $projectDetail);
                }

                $nominalData = [];

                // Looping berdasarkan susunan baris (Tunjangan) dan kolom (Unit Kerja)
                foreach ($tunjanganArray as $biayaIdx => $tunjanganName) {
                    foreach ($unitkerjaIds as $unitIdx => $unitId) {

                        // PENCARIAN UTAMA: Cari data biaya di tabel kontrakindukbiaya yang COCOK
                        // Kita filter berdasarkan 'jenis_biaya' DAN 'unitkerja_id' (bukan membandingkan teks string lagi)
                        $matchBiaya = $setHarga->biaya->first(function ($b) use ($tunjanganName, $unitId) {
                            return $b->jenis_biaya === $tunjanganName && (int)$b->unitkerja_id === (int)$unitId;
                        });

                        // Ambil nominal asli angka murni jika ditemukan, jika tidak set ke 0
                        $nominalValue = $matchBiaya ? $matchBiaya->harga : 0;

                        // Masukkan ke matriks koordinat form [indeks_baris_tunjangan][indeks_kolom_unit]
                        $nominalData[$biayaIdx][$unitIdx] = $nominalValue;
                    }
                }

                $projectName = $setHarga->project ? $setHarga->project->nama_project : 'Project Terpilih';

                $settingsFormatted[] = [
                    'project_id'     => $setHarga->project_id,
                    'project_name'   => $projectName,
                    'project_detail' => $projectDetail,
                    'nominal'        => $nominalData,
                    'dtlokasi'       => $kontrak->settingHarga->toArray()
                ];
            }

            // Kirim data ke Frontend AJAX
            if ($request->ajax()) {
                return response()->json([
                    'data'   => [
                        'idkontrak'     => $kontrak->id,
                        'nomor'         => $kontrak->nomor,
                        'dari'          => $kontrak->start,
                        'sampai'        => $kontrak->end,
                        // Kirim nama unit kerja untuk text header di Step 3
                        'unitkerja'     => $unitkerjaNames,
                        'tunjangan'     => $tunjanganArray,
                        // 'settings'      => $settingsFormatted,
                    ]
                ], 200);
            }

            return view('kontrakinduk.kontrakinduk-edit', [
                'judul' => 'Edit Kontrak Induk'
            ], compact('kontrak', 'unitkerjaNames', 'tunjanganArray', 'settingsFormatted'));
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function request_nonaktif(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'file'   => 'required|file|mimes:pdf,jpeg,jpg,png|max:2048',
                'alasan' => 'required',
            ],
            [
                'file.required'   => 'File Lampiran masih kosong.',
                'alasan.required' => 'Alasan masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 401);
        } else {
            $token   = $request->input('idkontrak');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filename = 'LampiranOff_' . Str::random(10) . now()->timestamp . '.' . $extension;
            Storage::disk('public')->putFileAs('hris/kontrakinduk/', $request->file('file'), $filename);

            if (!is_null($id)) {
                $payloadData = $request->except(['_token']);
                $payloadData['file_upload_name'] = $filename;
                $snapshotDataLama = $this->getStrukturKontrakLengkap($id);

                // Simpan ke antrian approval dengan dua kondisi (Lama vs Baru)
                KontrakIndukApprovalRequest::updateOrCreate(
                    [
                        'kontrakinduk_id' => $id,
                        'status'          => 'PENDING',
                        'aktivitas'       => 'NONAKTIF KONTRAK INDUK',
                    ],
                    [
                        'adendum_id'      => null,
                        'keterangan'      => trim(strtoupper($request->input('alasan'))),
                        'data_lama'       => $snapshotDataLama ? $snapshotDataLama->toArray() : null,
                        'payload_request' => $payloadData,
                        'requested_by'    => auth()->id(),
                    ]
                );

                return response()->json([
                    'message' => 'Penonaktifan Kontrak Induk Berhasil Diajukan.'
                ]);
            }
        }
    }

    public function proses_approval_off_kontrakinduk(Request $request)
    {
        $token   = $request->input('idreq');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;
        $approval = KontrakIndukApprovalRequest::findOrFail($id);

        if ($approval->status !== 'PENDING') {
            return response()->json(['message' => 'Permintaan ini sudah diproses sebelumnya.'], 400);
        }

        DB::beginTransaction();
        try {
            $idInduk = $approval->kontrakinduk_id;

            # UPDATE KONTRAK INDUK
            $kontrak = Kontrakinduk::findOrFail($idInduk);

            $kontrak->update([
                'status' => 'DISABLED',
                'updated_at' => now(),
            ]);

            KontrakIndukLog::create([
                'kontrakinduk_id' => $kontrak->id,
                'aktivitas'       => 'NONAKTIF_VIA_APPROVAL',
                'keterangan'      => 'PERUBAHAN DISETUJUI OLEH ' . strtoupper(auth()->user()->name) . ' ALASAN ' . $approval->keterangan,
                'user_id'         => $approval->requested_by, // Log merujuk pada staff pengaju awal
                'data_lama'       => $approval->data_lama,
                'data_baru'       => ''
            ]);

            # UPDATE REQUEST
            $approval->update([
                'status'      => 'APPROVED',
                'approved_by' => auth()->id()
            ]);

            DB::commit();
            return response()->json(['message' => 'Permohonan Penonaktifan Kontrak Induk Berhasil Disetujui.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menyetujui request: ' . $e->getMessage()], 500);
        }
    }

    public function proses_reject_off_kontrakinduk(Request $request)
    {
        $request->validate([
            'alasan_reject' => 'required|string|max:500'
        ]);

        $token   = $request->input('idreq');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $approval = KontrakIndukApprovalRequest::findOrFail($id);

        if ($approval->status !== 'PENDING') {
            return response()->json(['message' => 'Perubahan data ini sudah diproses sebelumnya.'], 400);
        }

        $approval->update([
            'status'            => 'REJECTED',
            'keterangan_reject' => trim(strtoupper($request->input('alasan_reject'))),
            'approved_by'       => auth()->id()
        ]);

        return response()->json(['message' => 'Penonaktifan berhasil ditolak.'], 200);
    }

    public function adendum_tambah(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $adendum = KontrakIndukSettingHarga::with([
                // 'kontrakinduk.adendum',
                'kontrakinduk.adendum' => function ($q) use ($id) {
                    $q->where('setting_id', $id);
                },
                'biaya.unitkerja',
                'kontrakinduk.approvalPending' => function ($query) use ($id) {
                    $query->where('aktivitas', 'TAMBAH ADENDUM')
                        ->where('setting_id', $id);
                }
            ])->findOrFail($id);

            $kontrakinduk = $adendum->kontrakinduk;

            $unitkerjaList = [];
            $tunjanganList = [];
            $payloadArray = null;

            # JIKA SEBELUMNYA SUDAH PERNAH INPUT TAPI BELUM DI APPROVE MAKA AMBIL DARI DATA TSB
            if ($adendum->kontrakinduk && $adendum->kontrakinduk->approvalPending) {

                $payloadRaw   = $adendum->kontrakinduk->approvalPending->payload_request;
                $payloadArray = is_string($payloadRaw) ? json_decode($payloadRaw, true) : $payloadRaw;

                if (!empty($payloadArray)) {

                    // A. Bangun Model Kontrakinduk Tiruan
                    $kontrakRaw = new Kontrakinduk();
                    $kontrakRaw->forceFill([
                        'id'                 => $payloadArray['idinduk'] ?? null,
                        'start'              => $payloadArray['dari'] ?? ($payloadArray['start'] ?? null),
                        'end'                => $payloadArray['sampai'] ?? ($payloadArray['end'] ?? null),
                        'tanggal_terbit'     => $payloadArray['tglterbit'] ?? ($payloadArray['tanggal_terbit'] ?? null),
                        'file'               => $payloadArray['file_upload_name'] ?? ($payloadArray['file'] ?? null),
                        'jenis_kontrakinduk' => $payloadArray['jenis'] ?? ($payloadArray['jenis_kontrakinduk'] ?? null),
                        'keterangan_old'     => $payloadArray['keterangan'] ?? ($payloadArray['keterangan_old'] ?? null),
                    ]);
                    $kontrakRaw->exists = true;

                    // B. Ekstrak Data Matriks Baris & Kolom dari Root Payload
                    $payloadUnitKerja = $payloadArray['unitkerja'] ?? [];
                    $payloadTunjangan = $payloadArray['tunjangan'] ?? [];
                    $rawSettings      = $payloadArray['setting'] ?? [];

                    // C. Bangun Collection Setting Harga Berdasarkan Data Payload
                    $settingHargaCollection = collect($rawSettings)->map(function ($set) use ($payloadUnitKerja, $payloadTunjangan) {

                        $settingHargaModel = new KontrakIndukSettingHarga();
                        $settingHargaModel->forceFill([
                            'project_id'     => $set['project_id'] ?? null,
                            'project_detail' => $set['project_detail'] ?? null,
                        ]);

                        $biayaCollection = collect();
                        $matrixNominal   = $set['nominal'] ?? [];

                        // Bongkar matriks [baris_tunjangan][kolom_unit_kerja]
                        foreach ($payloadTunjangan as $biayaIdx => $tunjanganName) {
                            foreach ($payloadUnitKerja as $unitIdx => $unitName) {

                                $rawPrice   = $matrixNominal[$biayaIdx][$unitIdx] ?? 0;
                                $cleanPrice = is_string($rawPrice) ? (int)str_replace('.', '', $rawPrice) : (int)$rawPrice;

                                // Bangun Model Biaya Tiruan
                                $biayaModel = new KontrakIndukBiaya();
                                $biayaModel->forceFill([
                                    'jenis_biaya'   => $tunjanganName,
                                    'unitkerja_id'  => $unitIdx + 1,
                                    'harga'         => $cleanPrice,
                                ]);

                                // Bangun Model Unit Kerja Tiruan
                                $unitKerjaModel = new Unitkerja();
                                $unitKerjaModel->forceFill([
                                    'id'   => $unitIdx + 1,
                                    'name' => $unitName,
                                ]);

                                $biayaModel->setRelation('unitkerja', $unitKerjaModel);
                                $biayaCollection->push($biayaModel);
                            }
                        }

                        // Tempelkan collection biaya ke model setting harga
                        $settingHargaModel->setRelation('biaya', $biayaCollection);
                        return $settingHargaModel;
                    });

                    // D. Pasangkan Kembali Relasi ke Kontrak Induk Tiruan
                    $kontrakRaw->setRelation('settingHarga', $settingHargaCollection);
                    $kontrakRaw->setRelation('approvalPending', $adendum->kontrakinduk->approvalPending);

                    // E. SOLUSI UTAMA: Ambil setting harga pertama hasil rekonstruksi payload
                    $adendumFromPayload = $settingHargaCollection->first();

                    if ($adendumFromPayload) {
                        // Masukkan kontrak induk tiruan ke dalam setting harga tiruan
                        $adendumFromPayload->setRelation('kontrakinduk', $kontrakRaw);

                        // Ganti objek $adendum utama dengan objek tiruan berstruktur sama
                        $adendum = $adendumFromPayload;
                    }
                    $adendum['id'] = $id;
                }
            }
            // END

            foreach ($adendum->biaya as $biaya) {
                if ($biaya->unitkerja) {
                    // Simpan ID sebagai key agar tidak duplikat
                    $unitkerjaList[$biaya->unitkerja_id] = $biaya->unitkerja->name;
                }
                $namaTunjangan = $biaya->jenis_biaya;
                $tunjanganList[$namaTunjangan] = $namaTunjangan;
            }

            // Ubah menjadi array biasa untuk looping koordinat indeks di Frontend/Form
            // PENTING: Kita butuh ID Unit Kerja untuk mencocokkan data biaya dari DB
            $unitkerjaIds   = array_keys($unitkerjaList);   // Contoh: [1, 5, 12] (ID dari tabel unitkerja)
            $unitkerjaNames = array_values($unitkerjaList); // Contoh: ['AVSEC', 'PAB', 'HRD'] (Untuk teks header)
            $tunjanganArray = array_values($tunjanganList);  // Contoh: ['UANG MAKAN', 'TRANSPORT']

            // --- EKSTRAKSI DATA UNTUK STEP 3 (MATRIKS) ---
            $settingsFormatted = [];

            $projectDetail = $adendum->project_detail;

            if (is_string($projectDetail)) {
                $projectDetail = explode(',', $projectDetail);
            }

            $nominalData = [];

            // Looping berdasarkan susunan baris (Tunjangan) dan kolom (Unit Kerja)
            foreach ($tunjanganArray as $biayaIdx => $tunjanganName) {
                foreach ($unitkerjaIds as $unitIdx => $unitId) {

                    // PENCARIAN UTAMA: Cari data biaya di tabel kontrakindukbiaya yang COCOK
                    // Kita filter berdasarkan 'jenis_biaya' DAN 'unitkerja_id' (bukan membandingkan teks string lagi)
                    $matchBiaya = $adendum->biaya->first(function ($b) use ($tunjanganName, $unitId) {
                        return $b->jenis_biaya === $tunjanganName && (int)$b->unitkerja_id === (int)$unitId;
                    });

                    // Ambil nominal asli angka murni jika ditemukan, jika tidak set ke 0
                    $nominalValue = $matchBiaya ? $matchBiaya->harga : 0;

                    // Masukkan ke matriks koordinat form [indeks_baris_tunjangan][indeks_kolom_unit]
                    $nominalData[$biayaIdx][$unitIdx] = $nominalValue;
                }
            }

            $projectName = $adendum->project ? $adendum->project->nama_project : 'Project Terpilih';

            $settingsFormatted[] = [
                'project_id'     => $adendum->project_id,
                'project_name'   => $projectName,
                'project_detail' => $projectDetail,
                'nominal'        => $nominalData,
                'dtlokasi'       => $adendum->toArray()
            ];

            if ($request->ajax()) {
                return response()->json([
                    'data'   => [
                        'idkontrak'     => $kontrakinduk->id,
                        'dari'          => $kontrakinduk->start,
                        'sampai'        => $kontrakinduk->end,
                        // Kirim nama unit kerja untuk text header di Step 3
                        'unitkerja'     => $unitkerjaNames,
                        'tunjangan'     => $tunjanganArray,
                        // 'settings'      => $settingsFormatted,
                    ]
                ], 200);
            }

            return view('kontrakinduk.adendum-add', [
                'judul' => 'Tambah Adendum'
            ], compact('adendum', 'payloadArray', 'kontrakinduk', 'unitkerjaNames', 'tunjanganArray', 'settingsFormatted'));
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function adendum_edit(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;
            $adendum = KontrakIndukAdendum::with([
                'kontrakinduk',
                'settingHarga.biaya.unitkerja',
                'kontrakinduk.approvalPending' => function ($query) use ($request) {
                    $query->where('aktivitas', 'EDIT ADENDUM')
                        ->where('setting_id', $request->query('setting'));
                }
            ])->findOrFail($id);

            $kontrakinduk = $adendum->kontrakinduk;

            $unitkerjaList = [];
            $tunjanganList = [];
            $payloadArray = null;

            # 1. JIKA ADA APPROVAL PENDING, REKONSTRUKSI OBJEK $adendum DARI PAYLOAD
            if ($kontrakinduk && $kontrakinduk->approvalPending) {
                $payloadRaw   = $kontrakinduk->approvalPending->payload_request;
                // dd($kontrakinduk->approvalPending);
                $payloadArray = is_string($payloadRaw) ? json_decode($payloadRaw, true) : $payloadRaw;
                if (!empty($payloadArray)) {
                    // A. Buat Model Adendum Tiruan
                    $kontrakRaw = new KontrakIndukAdendum();
                    $kontrakRaw->forceFill([
                        'id'                    => $id,
                        'setting_id'            => $payloadArray['idsetting'] ?? ($payloadArray['idsetting'] ?? null),
                        'judul_adendum'         => $payloadArray['judul'] ?? ($payloadArray['judul'] ?? null),
                        'nomor_adendum'         => $payloadArray['nomor'] ?? ($payloadArray['nomor'] ?? null),
                        'tanggal_mulai_berlaku' => $payloadArray['dari'] ?? ($payloadArray['start'] ?? null),
                        'tanggal_akhir_berlaku' => $payloadArray['sampai'] ?? ($payloadArray['end'] ?? null),
                        'tanggal_terbit'        => $payloadArray['tglterbit'] ?? null,
                        'file'                  => $payloadArray['file_upload_name'] ?? null,
                        'keterangan'            => $payloadArray['keterangan'] ?? null,
                    ]);
                    $kontrakRaw->exists = true;

                    // B. Ekstrak Data Matriks dari Payload
                    $payloadUnitKerja = $payloadArray['unitkerja'] ?? [];
                    $payloadTunjangan = $payloadArray['tunjangan'] ?? [];
                    $rawSettings      = $payloadArray['setting'] ?? [];

                    // C. Bangun Collection Setting Harga dari Payload
                    $settingHargaCollection = collect($rawSettings)->map(function ($set) use ($payloadUnitKerja, $payloadTunjangan) {
                        $settingHargaModel = new KontrakIndukSettingHarga();
                        $settingHargaModel->forceFill([
                            'project_id'     => $set['project_id'] ?? null,
                            'project_detail' => is_array($set['project_detail'] ?? null) ? implode(',', $set['project_detail']) : ($set['project_detail'] ?? null),
                        ]);

                        $biayaCollection = collect();
                        $matrixNominal   = $set['nominal'] ?? [];

                        foreach ($payloadTunjangan as $biayaIdx => $tunjanganName) {
                            foreach ($payloadUnitKerja as $unitIdx => $unitName) {
                                $rawPrice   = $matrixNominal[$biayaIdx][$unitIdx] ?? 0;
                                $cleanPrice = is_string($rawPrice) ? (int)str_replace('.', '', $rawPrice) : (int)$rawPrice;

                                $biayaModel = new KontrakIndukBiaya();
                                $biayaModel->forceFill([
                                    'jenis_biaya'   => $tunjanganName,
                                    'unitkerja_id'  => $unitIdx + 1,
                                    'harga'         => $cleanPrice,
                                ]);

                                $unitKerjaModel = new Unitkerja();
                                $unitKerjaModel->forceFill([
                                    'id'   => $unitIdx + 1,
                                    'name' => $unitName,
                                ]);

                                $biayaModel->setRelation('unitkerja', $unitKerjaModel);
                                $biayaCollection->push($biayaModel);
                            }
                        }

                        $settingHargaModel->setRelation('biaya', $biayaCollection);
                        return $settingHargaModel;
                    });

                    // D. Pasang Kembali Semua Relasi Ke Model Adendum Tiruan
                    $kontrakRaw->setRelation('settingHarga', $settingHargaCollection);
                    $kontrakRaw->setRelation('kontrakinduk', $kontrakinduk);
                    $kontrakRaw->setRelation('project', $adendum->project);

                    // Ganti objek $adendum utama dengan objek tiruan yang strukturnya sudah sama
                    $adendum = $kontrakRaw;
                }
            }

            # 2. EKSTRAKSI DATA PASCA-REKONSTRUKSI (Berlaku untuk data DB maupun Payload)
            // Ambil objek setting harga pertama dari collection HasMany secara aman
            $firstSetting = $adendum->settingHarga->first();
            $biayaCollection = $firstSetting ? $firstSetting->biaya : collect();

            // Loop data biaya untuk mengumpulkan list header unit kerja dan tunjangan
            foreach ($biayaCollection as $biaya) {
                if ($biaya->unitkerja) {
                    $unitkerjaList[$biaya->unitkerja_id] = $biaya->unitkerja->name;
                }
                $namaTunjangan = $biaya->jenis_biaya;
                $tunjanganList[$namaTunjangan] = $namaTunjangan;
            }

            $unitkerjaIds   = array_keys($unitkerjaList);
            $unitkerjaNames = array_values($unitkerjaList);
            $tunjanganArray = array_values($tunjanganList);

            // --- EKSTRAKSI DATA UNTUK STEP 3 (MATRIKS) ---
            $projectDetail = $firstSetting ? $firstSetting->project_detail : '';
            if (is_string($projectDetail)) {
                $projectDetail = explode(',', $projectDetail);
            }

            $nominalData = [];
            foreach ($tunjanganArray as $biayaIdx => $tunjanganName) {
                foreach ($unitkerjaIds as $unitIdx => $unitId) {
                    $matchBiaya = $biayaCollection->first(function ($b) use ($tunjanganName, $unitId) {
                        return $b->jenis_biaya === $tunjanganName && (int)$b->unitkerja_id === (int)$unitId;
                    });

                    $nominalData[$biayaIdx][$unitIdx] = $matchBiaya ? $matchBiaya->harga : 0;
                }
            }

            $projectName = $adendum->project ? $adendum->project->nama_project : 'Project Terpilih';

            $settingsFormatted = [];
            $settingsFormatted[] = [
                'project_id'     => $firstSetting ? $firstSetting->project_id : null,
                'project_name'   => $projectName,
                'project_detail' => $projectDetail,
                'nominal'        => $nominalData,
                'dtlokasi'       => $adendum->toArray()
            ];

            // dd($settingsFormatted);
            # 3. KIRIM RESPON AJAX / BLADE
            if ($request->ajax()) {
                return response()->json([
                    'data' => [
                        'idkontrak'  => $kontrakinduk->id ?? null,
                        'dari'       => $adendum->tanggal_mulai_berlaku,
                        'sampai'     => $adendum->tanggal_akhir_berlaku,
                        'endkontrak' => $kontrakinduk->end ?? null,
                        'unitkerja'  => $unitkerjaNames,
                        'tunjangan'  => $tunjanganArray,
                        'settings'   => $settingsFormatted,
                    ]
                ], 200);
            }

            // Lempar variabel $adendum ke Blade (Strukturnya dijamin seragam)

            return view('kontrakinduk.adendum-edit', [
                'judul' => 'Tambah Adendum'
            ], compact('adendum', 'kontrakinduk', 'unitkerjaNames', 'tunjanganArray', 'settingsFormatted'));
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function adendum_request(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'judul' => 'required',
                'nomor' => [
                    'required',
                    'regex:/^\S+$/',
                    Rule::unique('kontrakinduk_adendum', 'nomor_adendum')
                        ->ignore($request->idadendum)
                        ->where(function ($query) {
                            $query->whereNull('deleted_at');
                        }),
                ],
                'tglterbit'  => 'required|date',
                'dari'       => 'required|date',
                'sampai'     => 'required|date|after_or_equal:dari',
                'keterangan' => 'required',
                'file' => $request->hasFile('file')
                    ? 'required|file|mimes:pdf|max:2048'
                    :  'nullable',
            ],
            [
                'judul.required'      => 'Judul masih kosong',
                'nomor.required'      => 'Nomor masih kosong',
                'nomor.unique'        => 'Nomor sudah pernah diinput',
                'tglterbit.required'  => 'Tanggal Terbit masih kosong',
                'dari.required'       => 'Tanggal Berlaku masih kosong',
                'sampai.required'     => 'Tanggal Berlaku masih kosong',
                'keterangan.required' => 'Keterangan masih kosong',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            $unitKerjaInput = array_map('strtoupper', $request->input('unitkerja[]') ?? $request->input('unitkerja') ?? []);
            $unitList = $request->input('unitkerja', []);
            foreach ($unitKerjaInput as $val) {
                $namaUnitkerja = strtoupper(trim($val));
                if ($namaUnitkerja !== '') {
                    $unitList[] = $namaUnitkerja;
                    Unitkerja::firstOrCreate(
                        ['name' => $namaUnitkerja],
                        ['user_id' => auth()->id(), 'updated_at' => now()]
                    );
                }
            }

            // 💡 OPTIMASI 1: Ambil semua ID unit kerja sekaligus dari DB, jadikan Array Map [ 'PAB' => 1, 'AVSEC' => 2 ]
            // Ini menghemat 36+ query di dalam loop bawah menjadi hanya 1 query saja.
            $unitMapFromDb = Unitkerja::whereIn('name', $unitList)->pluck('id', 'name')->toArray();

            // 2. Proses & Sinkronisasi Master Tunjangan (Dibersihkan dari filter parsial agar UPAH/THR ikut masuk)
            $tunjanganList = array_map('strtoupper', $request->input('tunjangan[]') ?? $request->input('tunjangan') ?? []);
            if (is_array($tunjanganList)) {
                foreach ($tunjanganList as $key => $val) {
                    $namaTunjangan = strtoupper(trim($val));
                    // 2. Filter: Proses jika diawali 'TUNJANGAN' ATAU diawali 'TUNJ'
                    if (str_starts_with($namaTunjangan, 'TUNJANGAN') || str_starts_with($namaTunjangan, 'TUNJ')) {
                        // 3. Jalankan logika database (Gunakan updateOrCreate sesuai permintaan Anda)
                        Tunjangan::updateOrCreate(
                            [
                                'name' => $namaTunjangan, // Kondisi pencarian
                            ],
                            [
                                'user_id'    => auth()->id(), // Data yang dibuat/diperbarui
                                'updated_at' => now()
                            ]
                        );
                    }
                }
            }

            $settings = $request->input('setting', []);
            foreach ($request->all() as $key => $value) {
                if (preg_match('/^setting\[(\d+)\]\[(project_id|project_detail)\]$/', $key, $matches)) {
                    $index = $matches[1];
                    $field = $matches[2];
                    $settings[$index][$field] = $value;
                } elseif (preg_match('/^setting\[(\d+)\]\[nominal\]\[(\d+)\]\[(\d+)\]$/', $key, $matches)) {
                    $index = $matches[1];
                    $biayaIdx = $matches[2];
                    $unitIdx = $matches[3];
                    $settings[$index]['nominal'][$biayaIdx][$unitIdx] = $value;
                }
            }

            if (empty($settings)) {
                return response()->json(['status' => 'error', 'message' => 'Data setting project tidak ditemukan.'], 422);
            }

            $namaFile = null;
            // Menentukan Nama Gambar
            if ($request->hasFile('file')) {
                $extension = $request->file('file')->getClientOriginalExtension();
                $filename = 'Adendum_' . Str::random(10) . now()->timestamp . '.' . $extension;

                Storage::disk('public')->putFileAs('hris/kontrakinduk/', $request->file('file'), $filename);
                $namaFile = $filename;
            } else {
                $namaFile = $request->file_lama;
            }

            DB::beginTransaction();
            try {
                $idInduk = $request->input('idinduk');
                if (!is_null($idInduk)) {
                    $idAdendum = $request->input('idadendum') ?: null;
                    $payloadData = $request->except(['_token']);

                    if ($namaFile) {
                        $payloadData['file_upload_name'] = $namaFile;
                    }

                    # 🔥 AMBIL SNAPSHOT DATA LAMA SEBELUM BERUBAH
                    // Merujuk pada fungsi Anda untuk mengambil data struktur lengkap
                    if ($idAdendum != null) {
                        $snapshotDataLama = $this->getStrukturAdendum($idAdendum);
                    } else {
                        $snapshotDataLama = null;
                    }

                    // Simpan ke antrian approval dengan dua kondisi (Lama vs Baru)
                    KontrakIndukApprovalRequest::updateOrCreate(
                        [
                            'kontrakinduk_id' => $idInduk,
                            'adendum_id'      => $idAdendum,
                            'setting_id'      => $request->idsetting ?? null,
                            'status'          => 'PENDING',
                            'aktivitas'       => ($idAdendum == null ? 'TAMBAH' : 'EDIT') . ' ADENDUM',
                        ],
                        [
                            'keterangan'      => trim(strtoupper($request->input('keterangan'))),
                            'data_lama'       => $snapshotDataLama ? $snapshotDataLama->toArray() : null,
                            'payload_request' => $payloadData,
                            'requested_by'    => auth()->id(),
                        ]
                    );

                    DB::commit();
                    return response()->json(['message' => 'Permintaan ' . ($idAdendum == null ? 'Penambahan' : 'Perubahan') . ' Adendum Baru / Berita Acara berhasil dikirim dan menunggu persetujuan.'], 200);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Gagal memproses data: ' . $e->getMessage()], 500);
            }
        }
    }

    public function adendum_store_new(Request $request)
    {
        $token   = $request->input('idreq');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;
        $approval = KontrakIndukApprovalRequest::findOrFail($id);
        if ($approval->status !== 'PENDING') {
            return response()->json(['message' => 'Permintaan ini sudah diproses sebelumnya.'], 400);
        }

        // 💡 Trik Inti: Konversi balik array JSON payload menjadi object Request tiruan (Fake Request)
        $payload = $approval->payload_request;
        $fakeRequest = new \Illuminate\Http\Request($payload);

        DB::beginTransaction();
        try {
            $idInduk = $approval->kontrakinduk_id;
            $idSetting = $approval->setting_id;
            $idAdendum = $approval->adendum_id;

            # A. LOCK DATA SEBELUM PERUBAHAN (Kondisi Eksisting)
            $dataSebelum = $this->getStrukturAdendum($idAdendum);

            // Siapkan variabel helper loops dari fake request
            $unitList = array_map(fn($v) => strtoupper(trim($v)), $fakeRequest->input('unitkerja', []));
            $tunjanganList = array_map(fn($v) => strtoupper(trim($v)), $fakeRequest->input('tunjangan', []));
            $settings = $fakeRequest->input('setting', []);

            $unitMapFromDb = Unitkerja::whereIn('name', $unitList)
                ->pluck('id', 'name')
                ->toArray();

            $totalAdendum = KontrakIndukAdendum::where('kontrakinduk_id', $idInduk)
                ->where('setting_id', $idSetting)
                ->where('id', '!=', $idAdendum) // Jangan hitung diri sendiri jika ID adendum sudah ada
                ->whereNull('deleted_at')
                ->count();

            $adendumKeNext = $totalAdendum + 1;
            # UPDATE ADENDUM
            $Adendum = KontrakIndukAdendum::updateOrCreate(
                [
                    'id'              => $idAdendum,
                    'kontrakinduk_id' => $idInduk,
                    'setting_id'      => $idSetting
                ],
                [
                    'judul_adendum'         => trim(strtoupper($fakeRequest->input('judul'))),
                    'nomor_adendum'         => trim(strtoupper($fakeRequest->input('nomor'))),
                    'adendum_ke'            => $adendumKeNext,
                    'tanggal_terbit'        => $fakeRequest->input('tglterbit'),
                    'tanggal_mulai_berlaku' => $fakeRequest->input('dari'),
                    'tanggal_akhir_berlaku' => $fakeRequest->input('sampai'),
                    'keterangan'            => trim(strtoupper($fakeRequest->input('keterangan'))),
                    'file'                  => $payload['file_upload_name'] ?? ($dataSebelum ? $dataSebelum->file : null),
                ]
            );

            $keepSettingHargaIds = [];
            $keepBiayaIds = [];

            # 2. UPSERT DATA MATRIKS BARU
            foreach ($settings as $index => $dataProject) {
                $settingHarga = KontrakIndukSettingHarga::updateOrCreate(
                    [
                        'id'              => $idSetting,
                        'kontrakinduk_id' => $idInduk,
                    ],
                    [
                        'project_id'      => $dataProject['project_id'],
                        'project_detail'        => $dataProject['project_detail'] ?? '',
                        'tanggal_mulai_berlaku' => $Adendum->tanggal_mulai_berlaku,
                        'tanggal_akhir_berlaku' => $Adendum->tanggal_akhir_berlaku,
                    ]
                );

                $keepSettingHargaIds[] = $settingHarga->id;

                if (isset($dataProject['nominal'])) {
                    foreach ($dataProject['nominal'] as $biayaIdx => $columns) {
                        foreach ($columns as $unitIdx => $nominalString) {
                            $namaUnitKerja = $unitList[$unitIdx] ?? null;
                            $unitkerjaId = $namaUnitKerja ? ($unitMapFromDb[$namaUnitKerja] ?? null) : null;
                            $namaJenisBiaya = $tunjanganList[$biayaIdx] ?? 'JENIS BIAYA';
                            $hargaBersih = (float) str_replace('.', '', $nominalString);

                            $biaya = KontrakIndukBiaya::updateOrCreate(
                                [
                                    'setting_harga_id' => $settingHarga->id,
                                    'unitkerja_id'     => $unitkerjaId,
                                    'jenis_biaya'      => $namaJenisBiaya
                                ],
                                [
                                    'harga'      => $hargaBersih,
                                    'sort_order' => (int)$biayaIdx + 1
                                ]
                            );

                            $keepBiayaIds[] = $biaya->id;
                        }
                    }
                }
            }

            # 3. CLEANUP SINKRONISASI (Menghapus row/header lama yang dibuang user saat edit)
            KontrakIndukBiaya::whereIn('setting_harga_id', $keepSettingHargaIds)
                ->whereNotIn('id', $keepBiayaIds)
                ->delete();

            # B. LOCK DATA SESUDAH PERUBAHAN (Kondisi Terbaru)
            $dataSesudah = $this->getStrukturKontrakLengkap($Adendum->id);

            # C. PROSES SANITASI & INPUT AUDIT TRAIL LOG
            $arraySebelum = $dataSebelum ? $dataSebelum->toArray() : [];
            $arraySesudah = $dataSesudah ? $dataSesudah->toArray() : [];

            $cleaner = function (&$item) use (&$cleaner) {
                foreach ($item as $key => &$value) {
                    if (in_array($key, ['created_at', 'updated_at', 'deleted_at'])) {
                        unset($item[$key]);
                        continue;
                    }
                    if (is_numeric($value)) $value = (string) $value;
                    if (is_array($value)) $cleaner($value);
                }
            };

            $cleaner($arraySebelum);
            $cleaner($arraySesudah);

            // Rekam log mutasi riwayat tabel utama
            if (json_encode($arraySebelum) !== json_encode($arraySesudah)) {
                KontrakIndukLog::create([
                    'kontrakinduk_id' => $idInduk,
                    'aktivitas'       => ($approval->adendum_id == null ? 'ADDING_VIA_APPROVAL_ADENDUM' : 'EDITED_VIA_APPROVAL_ADENDUM'),
                    'keterangan'      => 'PERUBAHAN DISETUJUI OLEH ' . strtoupper(auth()->user()->name),
                    'user_id'         => $approval->requested_by, // Log merujuk pada staff pengaju awal
                    'data_lama'       => $arraySebelum,
                    'data_baru'       => $arraySesudah
                ]);
            }

            # 4. UPDATE STATUS LEMBAR REKUES APPROVAL
            $approval->update([
                'status'      => 'APPROVED',
                'approved_by' => auth()->id()
            ]);

            DB::commit();
            return response()->json(['message' => ($approval->adendum_id == null ? 'Penambahan Adendum' : 'Perubahan Adendum') . ' Berhasil disetujui dan data sistem telah diperbarui.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menyetujui Permintaan: ' . $e->getMessage()], 500);
        }
    }

    public function adendum_reject(Request $request)
    {
        $request->validate([
            'alasan_reject' => 'required|string|max:500'
        ]);

        $token   = $request->input('idreq');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $approval = KontrakIndukApprovalRequest::findOrFail($id);

        if ($approval->status !== 'PENDING') {
            return response()->json(['message' => 'Perubahan data ini sudah diproses sebelumnya.'], 400);
        }

        $approval->update([
            'status'            => 'REJECTED',
            'keterangan_reject' => trim(strtoupper($request->input('alasan_reject'))),
            'approved_by'       => auth()->id()
        ]);

        return response()->json(['message' => 'Permintaan berhasil ditolak.'], 200);
    }

    public function kontrakinduk_perpanjang(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $kontrak = Kontrakinduk::select('id', 'nomor')
                ->whereNull('deleted_at')
                ->where('status', 'ENABLED')
                ->findOrFail($id);

            return view('kontrakinduk.perpanjang-kontrakinduk', [
                'judul' => 'Perpanjang Kontrak Induk'
            ], compact('kontrak'));
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function proses_perpanjang(Request $request)
    {
        $allInput = $request->all();

        // 2. FORCE PARSING: Ubah string literal flat menjadi multidimensional array murni
        $parsedData = [];
        foreach ($allInput as $key => $value) {
            // Cari key yang memiliki pola format seperti "setting[0][project_id]" atau "setting[1][nominal][0][0]"
            if (preg_match('/^setting\[(\d+)\]\[(\w+)\]/', $key, $matches)) {
                $index = $matches[1]; // Mengambil angka indeks (0, 1, dst)
                $field = $matches[2]; // Mengambil nama field (project_id, project_detail, atau nominal)

                // Jika field berupa nominal bertingkat (misal: "setting[0][nominal][0][0]")
                if ($field === 'nominal') {
                    // Bongkar sisa indeks terdalam [0][0] menggunakan parse_str
                    parse_str($key . '=' . urlencode($value), $subOutput);

                    // Satukan langsung ke dalam indeks yang benar
                    if (!isset($parsedData['setting'][$index]['nominal'])) {
                        $parsedData['setting'][$index]['nominal'] = [];
                    }
                    $parsedData['setting'][$index]['nominal'] = array_merge_recursive(
                        $parsedData['setting'][$index]['nominal'],
                        $subOutput['setting'][$index]['nominal']
                    );
                } else {
                    // Jika field biasa seperti project_id atau project_detail, masukkan langsung sesuai nomor indeksnya
                    $parsedData['setting'][$index][$field] = $value;
                }
            } else {
                // Untuk data global seperti unitkerja[] dan tunjangan[] tetap dimasukkan normal
                $parsedData[$key] = $value;
            }
        }

        $validator = Validator::make(
            $parsedData,
            [
                'judul'      => 'required',
                'nomor'      => [
                    'required',
                    'regex:/^\S+$/',
                    Rule::unique('kontrakinduk', 'nomor')
                        ->ignore($request->idinduk)
                        ->where(function ($query) {
                            $query->whereNull('deleted_at');
                        }),
                ],
                'parentId'             => 'required',
                'jenis'                => 'required',
                'tglterbit'            => 'required|date',
                'dari'                 => 'required|date',
                'sampai'               => 'required|date|after_or_equal:dari',
                'keterangan'           => 'required_if:status,1',
                'setting'              => 'required|array|min:1',
                'setting.*.project_id' => 'required',
                'setting.*.nominal'    => 'required|array',
                'setting.*.nominal.*'  => 'required|array',
                'setting.*.nominal.*.*' => [
                    'required',
                    'string',
                    'regex:/^[0-9.]+(,[0-9]{2})?$/'
                ],

                'file' => $request->hasFile('file')
                    ? 'required|file|mimes:pdf|max:2048'
                    :  'nullable',

            ],
            [
                'judul.required'                 => 'Judul Kontrak Induk masih kosong.',
                'nomor.required'                 => 'Nomor Kontrak Induk masih kosong.',
                'nomor.unique'                   => 'Nomor Kontrak Induk Sudah pernah diinput.',
                'nomor.regex'                    => 'Nomor Kontrak Induk tidak boleh menggunakan spasi.',
                'project.required'               => 'Keperluan Project Kontrak Induk masih kosong.',
                'project.unique'                 => 'Project Kontrak Induk Sudah pernah diinput.',
                'jenis.required'                 => 'Jenis Kontrak Induk masih kosong.',
                'tglterbit.required'             => 'Tanggal Terbit Kontrak Induk masih kosong.',
                'dari.required'                  => 'Tanggal Mulai Kontrak Induk masih kosong.',
                'sampai.required'                => 'Tanggal Akhir Kontrak Induk masih kosong.',
                'keterangan.required'            => 'Keterangan Kontrak Induk masih kosong.',
                'setting.required'               => "Data setting project tidak ditemukan atau kosong.",
                'setting.*.project_id.required'  => "Lokasi project pada 'Setting Project #:position' belum dipilih.",
                'setting.*.nominal.*.*.required' => "Ada nominal biaya pada 'Setting Project #:position' yang masih kosong.",
                'setting.*.nominal.*.*.regex'    => "Format nominal uang pada 'Setting Project #:position' harus berupa angka.",
                'file.required'                  => 'File Kontrak Induk masih kosong.',
                'file.max'                       => 'Ukuran file maximal 2Mb.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {

            $unitKerjaInput = array_map('strtoupper', $request->input('unitkerja[]') ?? $request->input('unitkerja') ?? []);
            $unitList = $request->input('unitkerja', []);
            foreach ($unitKerjaInput as $val) {
                $namaUnitkerja = strtoupper(trim($val));
                if ($namaUnitkerja !== '') {
                    $unitList[] = $namaUnitkerja;
                    Unitkerja::firstOrCreate(
                        ['name' => $namaUnitkerja],
                        ['user_id' => auth()->id(), 'updated_at' => now()]
                    );
                }
            }

            // 💡 OPTIMASI 1: Ambil semua ID unit kerja sekaligus dari DB, jadikan Array Map [ 'PAB' => 1, 'AVSEC' => 2 ]
            // Ini menghemat 36+ query di dalam loop bawah menjadi hanya 1 query saja.
            $unitMapFromDb = Unitkerja::whereIn('name', $unitList)->pluck('id', 'name')->toArray();

            // 2. Proses & Sinkronisasi Master Tunjangan (Dibersihkan dari filter parsial agar UPAH/THR ikut masuk)
            $tunjanganList = array_map('strtoupper', $request->input('tunjangan[]') ?? $request->input('tunjangan') ?? []);
            if (is_array($tunjanganList)) {
                foreach ($tunjanganList as $key => $val) {
                    $namaTunjangan = strtoupper(trim($val));
                    // 2. Filter: Proses jika diawali 'TUNJANGAN' ATAU diawali 'TUNJ'
                    if (str_starts_with($namaTunjangan, 'TUNJANGAN') || str_starts_with($namaTunjangan, 'TUNJ')) {
                        // 3. Jalankan logika database (Gunakan updateOrCreate sesuai permintaan Anda)
                        Tunjangan::updateOrCreate(
                            [
                                'name' => $namaTunjangan, // Kondisi pencarian
                            ],
                            [
                                'user_id'    => auth()->id(), // Data yang dibuat/diperbarui
                                'updated_at' => now()
                            ]
                        );
                    }
                }
            }

            // Rekonstruksi data matriks flat "setting[x]..." menjadi Array Multidimensi asli PHP
            // $settings = [];
            $settings = $request->input('setting', []);
            foreach ($request->all() as $key => $value) {
                if (preg_match('/^setting\[(\d+)\]\[(project_id|project_detail)\]$/', $key, $matches)) {
                    $index = $matches[1];
                    $field = $matches[2];
                    $settings[$index][$field] = $value;
                } elseif (preg_match('/^setting\[(\d+)\]\[nominal\]\[(\d+)\]\[(\d+)\]$/', $key, $matches)) {
                    $index = $matches[1];
                    $biayaIdx = $matches[2];
                    $unitIdx = $matches[3];
                    $settings[$index]['nominal'][$biayaIdx][$unitIdx] = $value;
                }
            }

            if (empty($settings)) {
                return response()->json(['status' => 'error', 'message' => 'Data setting project tidak ditemukan.'], 422);
            }

            $namaFile = null;
            // Menentukan Nama Gambar
            if ($request->hasFile('file')) {
                $extension = $request->file('file')->getClientOriginalExtension();
                $filename = 'Kontrakinduk_' . Str::random(10) . now()->timestamp . '.' . $extension;

                Storage::disk('public')->putFileAs('hris/kontrakinduk/', $request->file('file'), $filename);
                $namaFile = $filename;
            } else {
                $namaFile = $request->file_lama;
            }

            DB::beginTransaction();
            try {
                # NONAKTIFKAN KONTRAK LAMA
                $old = Kontrakinduk::with('settingHarga')->findOrFail($request->parentId);
                // $idSettingOld = [];
                // foreach ($old->settingHarga as $set) {
                //     $idSettingOld[] = $set->id;
                // }

                // KontrakIndukSettingHarga::where('kontrakinduk_id', $request->parentId)
                //     ->whereIn('id', $idSettingOld)
                //     ->delete();

                $old->update([
                    'status'     => 'DISABLED',
                    'parent_id'  => $request->parentId,
                    'updated_at' => now(),
                ]);

                # CREATE KONTRAK INDUK
                $kontrak = Kontrakinduk::create([
                    'nomor'              => trim(strtoupper($request->input('nomor'))),
                    'judul'              => trim(strtoupper($request->input('judul'))),
                    'tanggal_terbit'     => $request->input('tglterbit'),
                    'jenis_kontrakinduk' => $request->input('jenis'),
                    'keterangan'         => trim(strtoupper($request->input('keterangan'))),
                    'start'              => $request->input('dari'),
                    'end'                => $request->input('sampai'),
                    'status'             => 'ENABLED',
                    'file'               => $namaFile,
                ]);

                foreach ($settings as $index => $dataProject) {
                    // 1. Cek apakah project_id ini sudah terdaftar di kontrak induk yang aktif
                    $isExist = KontrakIndukSettingHarga::where('project_id', $dataProject['project_id'])
                        ->whereHas('kontrakinduk', function ($query) {
                            $query->where('status', 'ENABLED');
                        })
                        ->exists();

                    // 2. Jika sudah ada, lempar error dan stop eksekusi
                    if ($isExist) {
                        throw ValidationException::withMessages([
                            "errors" => "SETTING PROJECT #" . ($index + 1) . " sudah terdaftar pada kontrak induk yang sedang aktif."
                        ]);
                    }
                    # CREATE KONTRAK INDUK SETTING HARGA
                    $settingHarga = KontrakIndukSettingHarga::create([
                        'kontrakinduk_id'       => $kontrak->id,
                        'project_id'            => $dataProject['project_id'],
                        'project_detail'        => $dataProject['project_detail'] ?? '',
                        'tanggal_mulai_berlaku' => $kontrak->start,
                        'tanggal_akhir_berlaku' => $kontrak->end,
                    ]);

                    # CREATE KONTRAK INDUK BIAYA
                    if (isset($dataProject['nominal'])) {
                        foreach ($dataProject['nominal'] as $biayaIdx => $columns) {
                            foreach ($columns as $unitIdx => $nominalString) {
                                $namaUnitKerja = $unitList[$unitIdx] ?? null;
                                $unitkerjaId = strtoupper(trim($namaUnitKerja)) ? ($unitMapFromDb[strtoupper(trim($namaUnitKerja))] ?? null) : null;
                                $namaJenisBiaya = $tunjanganList[$biayaIdx] ?? 'JENIS BIAYA';
                                $hargaBersih = (float) str_replace('.', '', $nominalString);

                                $biaya = KontrakIndukBiaya::create([
                                    'setting_harga_id' => $settingHarga->id,
                                    'unitkerja_id'     => $unitkerjaId,
                                    'jenis_biaya'      => $namaJenisBiaya,
                                    'harga'            => $hargaBersih,
                                    'sort_order'       => (int)$biayaIdx + 1
                                ]);
                            }
                        }
                    }
                }

                # PENCATATAN LOG MUTASI DATA BARU (CREATE)
                $dataSesudah = $this->getStrukturKontrakLengkap($kontrak->id);
                KontrakIndukLog::create([
                    'kontrakinduk_id' => $kontrak->id,
                    'aktivitas'       => 'PERPANJANGAN_KONTRAK_INDUK',
                    'keterangan'      => trim(strtoupper($request->input('keterangan'))),
                    'user_id'         => auth()->id(),
                    'data_lama'       => $request->parentId,
                    'data_baru'       => $dataSesudah
                ]);

                DB::commit();
                return response()->json(['message' => 'Kontrak Induk Berhasil Disimpan'], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Gagal memproses data: ' . $e->getMessage()], 500);
            }
        }
    }

    public function list_unitkerja_project(Request $request)
    {
        $token = $request->query('token');
        // Decode dari Base64
        $decoded = base64_decode($token);
        $ids = explode(',', $decoded);
        $idp = $ids[0];

        $query = KontrakIndukSettingHarga::whereNull('deleted_at')
            ->where(function ($q) use ($idp) {
                // Mengunci pencarian ID agar presisi dan tidak bocor ke ID lain
                $q->where('project_detail', '=', $idp)
                    ->orWhere('project_detail', 'LIKE', $idp . ',%');
            })
            ->with('kontrakinduk', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->with(['biaya' => function ($q) {
                $q->whereNull('deleted_at'); // Jika tabel biaya menggunakan soft deletes
            }])
            ->with('biaya.unitkerja')
            ->get();

        // Mengumpulkan semua unit kerja dari hasil query
        $unitKerjaList = $query->pluck('biaya') // Mengambil koleksi 'biaya' dari setiap baris
            ->flatten()                        // Menggabungkan semua array biaya menjadi satu dimensi
            ->pluck('unitkerja')               // Mengambil objek 'unitkerja' dari setiap biaya
            ->filter()                         // Membuang data jika ada unitkerja yang null
            ->unique('id')                     // Grouping / menghilangkan duplikasi berdasarkan ID unit kerja
            ->map(function ($unit) {
                // Mengambil hanya id dan name saja
                return [
                    'id'   => $unit->id,
                    'name' => $unit->name
                ];
            })
            ->values();                        // Me-reset indeks array agar berurutan dari 0
        return response()->json($unitKerjaList->toArray());
        // Tampilkan hasil untuk memeriksa data
        // dd($unitKerjaList->toArray());
    }
}
