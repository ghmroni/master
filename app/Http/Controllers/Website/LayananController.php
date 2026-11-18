<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Webdetaillayanan;
use App\Models\Weblayanan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class LayananController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // new Middleware(PermissionMiddleware::using('edit-website'), only: ['index']),
            // new Middleware(PermissionMiddleware::using('edit-website'), except: ['index']),
            new Middleware(PermissionMiddleware::using('kelola-website')),
        ];
    }

    public function index(Request $request)
    {
        // Hapus Session Sementara
        session()->forget('data_tim_sementara');
        session()->forget('data_nilai_sementara');

        if ($request->ajax()) {
            $tentang = Weblayanan::where('kategori', 'TENTANG')->first();
            $tim     = Weblayanan::where('kategori', 'TIM')->first();
            $nilai   = Weblayanan::where('kategori', 'NILAI')->first();

            return response()->json([
                'tentang' => $tentang,
                'tim'     => $tim,
                'nilai'   => $nilai,
            ]);
        }

        return view('website.tentang-kami', [
            'judul' => 'Tentang Kami'
        ]);
    }

    public function tentang_detail(Request $request)
    {
        $query = Weblayanan::where('slug', $request->slug)->firstOrFail();
        return response()->json($query, 200);
    }

    public function tentang_store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'gambar' => $request->hasFile('gambar')
                    ? 'required|image|mimes:jpeg,jpg,png|dimensions:width=720,height=514'
                    :  'nullable',
                'title'     => 'required',
                'shortdesc' => 'required',
                'longdesc'  => 'required',
                'kategori'  => 'required',
            ],
            [
                'gambar.required'    => 'Gambar masih kosong.',
                'gambar.dimensions'  => 'Oops! Ukuran Gambar Harus 720 x 514',
                'title.required'     => 'Title masih kosong.',
                'shortdesc.required' => 'Deskripsi Singkat masih kosong.',
                'longdesc.required'  => 'Deskripsi Singkat masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 401);
        } else {

            $idtentang = $request->idtentang;

            $data = array(
                'title'             => trim(strtoupper($request->input('title'))),
                'deskripsi_singkat' => trim($request->input('shortdesc')),
                'deskripsi_lengkap' => trim($request->input('longdesc')),
            );

            // Menentukan Nama Gambar
            if ($request->hasFile('gambar')) {

                $image = $request->file('gambar');
                $size = getimagesize($image);

                $width = $size[0];
                $height = $size[1];

                if ($width != 720 || $height != 514) {
                    return response()->json([
                        'message' => 'Ukuran gambar harus 720 x 514 piksel'
                    ], 401);
                }

                $extension = $request->file('gambar')->getClientOriginalExtension();
                $filename = 'tentang_ghm' . now()->timestamp . '.' . $extension;

                Storage::disk('public')->putFileAs('website/layanan/', $request->file('gambar'), $filename);
                $data['gambar_layanan'] = $filename;
            } else {
                $data['gambar_layanan'] = $request->gambar_lama;
            }

            if ($idtentang) {
                $model = Weblayanan::where('id', $idtentang);
                $model->update($data);
            } else {
                $simpan = Weblayanan::create($data);
            }

            return response()->json([
                'message' => 'Data Berhasil Disimpan.'
            ]);
        }
    }

    // ============================================================================================================ TIM KAMI
    public function tim_kami(Request $request)
    {
        if ($request->ajax()) {
            $cek = Weblayanan::where('kategori', 'TIM')->first();

            if ($cek) {
                // Jika Session belum di create / belum ada
                if (!session()->has('data_tim_sementara')) {

                    $data = Webdetaillayanan::where('layanan_id', $cek->id)->get()->toArray();
                    // Masukan Ke session sementara
                    session()->put('data_tim_sementara', $data);
                }
            }

            // Ambil Data
            $query = session()->get('data_tim_sementara', []);

            return DataTables::of($query)

                ->addColumn('gambar', function ($row) {
                    $url = '/assets/img/avatars/no-image.png';
                    if ($row['photo'] != null) {
                        $url = '/storage/website/team/' . $row['photo'];
                    }
                    return '<img src="' . $url . '" width="70" class="img-thumbnail" />';
                })

                ->addColumn('action', function ($row) {

                    $button = '<div class="btn-group">
                        <span class="btn btn-sm btn-warning editUser" data-id=' . $row['id'] . '><i class="far fa-edit me-1"></i>Edit</span>
                        <span class="btn btn-sm btn-danger hapusUser" data-id=' . $row['id'] . '><i class="fas fa-user-times me-1"></i>Hapus</span>
                    </div>';

                    return $button;
                })

                ->rawColumns(['gambar' => 'gambar', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('website.tim-kami', [
            'judul' => 'Tim Kami',
        ]);
    }

    public function get_data_tim(Request $request)
    {
        if ($request->ajax()) {
            $data = Weblayanan::where('kategori', 'TIM')->first();

            return response()->json($data, 200);
        }
    }

    public function detail_tim_sementara(Request $request)
    {
        $data = session('data_tim_sementara', []);
        $item = collect($data)->firstWhere('id', $request->id);
        return response()->json($item, 200);
    }

    public function tampung_tim_kami(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'photo' => $request->hasFile('gambar')
                    ? 'required|image|mimes:jpeg,jpg,png|dimensions:width=360,height=360'
                    :  'nullable',
                'name'    => 'required',
                'jabatan' => 'required',
                'quote'   => 'required',
                'nourut'  => 'required|numeric'
            ],
            [
                'photo.required'   => 'Photo masih kosong.',
                'photo.dimensions' => 'Oops! Ukuran Photo harus 360 x 360',
                'name.required'    => 'Nama Lengkap masih kosong.',
                'jabatan.required' => 'Jabatan masih kosong.',
                'quote.required'   => 'Quote atau Kutipan masih kosong.',
                'nourut.required'  => 'Nomor urut masih kosong.',
                'nourut.numeric'   => 'Nomor urut harus angka.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 401);
        } else {

            $idsementara = $request->idsementara;
            // Ambil data lama, tambahkan data baru
            $data = session()->get('data_tim_sementara', []);

            $dataTimBaru = [
                // 'id'        => Str::uuid(),
                'nama'    => trim(strtoupper($request->name)),
                'nourut'  => trim($request->nourut),
                'jabatan' => trim($request->jabatan),
                'quote'   => trim($request->quote),
            ];

            // Menentukan Nama Gambar
            if ($request->hasFile('photo')) {

                $image = $request->file('photo');
                $size = getimagesize($image);

                $width = $size[0];
                $height = $size[1];

                if ($width != 360 || $height != 360) {
                    return response()->json([
                        'message' => 'Ukuran gambar harus 360 x 360 pixel'
                    ], 401);
                }

                $extension = $request->file('photo')->getClientOriginalExtension();
                $filename = trim(str_replace(' ', '_', $request->name)) . now()->timestamp . '.' . $extension;

                Storage::disk('public')->putFileAs('website/team/', $request->file('photo'), $filename);
                $dataTimBaru['photo'] = $filename;
            } else {
                $dataTimBaru['photo'] = $request->photo_sementara;
            }

            if ($idsementara) {
                // Update Data Session Berdasarkan ID
                $dataUpdate = collect($data)->map(function ($item) use ($idsementara, $dataTimBaru, $request) {
                    if ($dataTimBaru['photo'] == null) {
                        $dataTimBaru['photo'] = $request->photo_lama;
                    }

                    if ($item['id'] == $idsementara) {
                        return array_merge($item, $dataTimBaru);
                    }
                    return $item;
                })->values()->toArray();

                session()->put('data_tim_sementara', $dataUpdate);
            } else {
                // Simpan Baru
                $dataTimBaru['id'] = Str::uuid();
                $data[] = $dataTimBaru;
                session()->put('data_tim_sementara', $data);
            }

            return response()->json([
                'message' => 'Data Berhasil Ditambah.'
            ]);
        }
    }

    public function hapus_tim_sementara(Request $request)
    {
        // Ambil data Session
        $data = session()->get('data_tim_sementara', []);
        $filtered = collect($data)->reject(fn($item) => $item['id'] == $request->id)->values()->toArray();

        session()->put('data_tim_sementara', $filtered);
        return response()->json([
            'message' => 'Data Berhasil Dihapus.'
        ]);
    }

    public function tim_kami_store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'gambar' => $request->hasFile('gambar')
                    ? 'required|image|mimes:jpeg,jpg,png|dimensions:width=720,height=514'
                    :  'nullable',
                'shortdesc' => 'required',
            ],
            [
                'gambar.required'    => 'Gambar masih kosong.',
                'gambar.dimensions'  => 'Oops! Ukuran Gambar harus 720 x 514',
                'shortdesc.required' => 'Deskripsi masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 401);
        } else {
            $idtim = $request->idtim;

            $dtTampungTim = session()->get('data_tim_sementara', []);

            $data = array(
                'kategori'          => $request->kategori,
                'title'             => $request->title,
                'deskripsi_singkat' => $request->shortdesc,
            );

            // Menentukan Nama Gambar
            if ($request->hasFile('gambar')) {

                $image = $request->file('gambar');
                $size = getimagesize($image);

                $width = $size[0];
                $height = $size[1];

                if ($width != 720 || $height != 514) {
                    return response()->json([
                        'message' => 'Ukuran gambar harus 720 x 514 pixel'
                    ], 401);
                }

                $extension = $request->file('gambar')->getClientOriginalExtension();
                $filename = 'team_ghm' . now()->timestamp . '.' . $extension;

                Storage::disk('public')->putFileAs('website/layanan/', $request->file('gambar'), $filename);
                $data['gambar_layanan'] = $filename;
            } else {
                $data['gambar_layanan'] = $request->gambar_lama;
            }

            if ($idtim) {
                $model = Weblayanan::where('id', $idtim);
                $model->update($data);
                $hapusTim = Webdetaillayanan::where('layanan_id', $idtim)->delete();
                // Ambil Data Tim Session
                // $dtTampungTim = session()->get('data_tim_sementara', []);
                foreach ($dtTampungTim as $tim) {
                    Webdetaillayanan::create([
                        'id'         => $tim['id'],
                        'layanan_id' => $idtim,
                        'nourut'     => $tim['nourut'],
                        'nama'       => $tim['nama'],
                        'jabatan'    => $tim['jabatan'],
                        'quote'      => $tim['quote'],
                        'photo'      => $tim['photo'],
                    ]);
                }
                //Hapus Session Sementara
                session()->forget('data_tim_sementara');
            } else {
                $simpan = Weblayanan::create($data);
                $idsimpan = $simpan->id;
                // Ambil Data Tim Session
                // $dtTampungTim = session()->get('data_tim_sementara', []);
                foreach ($dtTampungTim as $tim) {
                    Webdetaillayanan::create([
                        'id'         => $tim['id']->toString(),
                        'layanan_id' => $idsimpan,
                        'nourut'     => $tim['nourut'],
                        'nama'       => $tim['nama'],
                        'jabatan'    => $tim['jabatan'],
                        'quote'      => $tim['quote'],
                        'photo'      => $tim['photo'],
                    ]);
                }
                //Hapus Session Sementara
                session()->forget('data_tim_sementara');
            }


            return response()->json([
                'message' => 'Data Berhasil Simpan.'
            ]);
        }
    }

    // ============================================================================================================ NILAI PERUSAHAAN
    public function nilai_korporasi(Request $request)
    {
        if ($request->ajax()) {
            $cek = Weblayanan::where('kategori', 'NILAI')->first();

            if ($cek) {
                // Jika Session belum di create / belum ada
                if (!session()->has('data_nilai_sementara')) {

                    $data = Webdetaillayanan::where('layanan_id', $cek->id)->get()->toArray();
                    // Masukan Ke session sementara
                    session()->put('data_nilai_sementara', $data);
                }
            }

            // Ambil Data
            $query = session()->get('data_nilai_sementara', []);

            return DataTables::of($query)

                ->addColumn('logo', function ($row) {
                    return '<i class="fas ' . $row['photo'] . ' fa-3x text-warning"></i>';
                })

                ->addColumn('action', function ($row) {

                    $button = '<div class="btn-group">
                        <span class="btn btn-sm btn-warning editNilai" data-id=' . $row['id'] . '><i class="far fa-edit me-1"></i>Edit</span>
                        <span class="btn btn-sm btn-danger hapusNilai" data-id=' . $row['id'] . '><i class="fas fa-trash me-1"></i>Hapus</span>
                    </div>';

                    return $button;
                })

                ->rawColumns(['logo' => 'logo', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('website.nilai-korporasi', [
            'judul' => 'Nilai Perusahaan',
        ]);
    }

    public function get_nilai_korporasi(Request $request)
    {
        if ($request->ajax()) {
            $data = Weblayanan::where('kategori', 'NILAI')->first();

            return response()->json($data, 200);
        }
    }

    public function store_nilai_korporasi(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'gambar' => $request->hasFile('gambar')
                    ? 'required|image|mimes:jpeg,jpg,png|dimensions:width=720,height=514'
                    :  'nullable',
                'shortdesc' => 'required',
            ],
            [
                'gambar.required'    => 'Gambar masih kosong.',
                'gambar.dimensions'  => 'Oops! Ukuran Gambar harus 720 x 514 pixel',
                'shortdesc.required' => 'Deskripsi masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 401);
        } else {
            $idnilai = $request->idnilai;
            // Ambil Data Tim Session
            $dtTampung = session()->get('data_nilai_sementara', []);

            $data = array(
                'kategori'          => trim($request->kategori),
                'title'             => trim($request->title),
                'deskripsi_singkat' => trim($request->shortdesc),
            );

            // Menentukan Nama Gambar
            if ($request->hasFile('gambar')) {

                $image = $request->file('gambar');
                $size = getimagesize($image);

                $width = $size[0];
                $height = $size[1];

                if ($width != 720 || $height != 514) {
                    return response()->json([
                        'message' => 'Ukuran gambar harus 720 x 514 pixel'
                    ], 401);
                }

                $extension = $request->file('gambar')->getClientOriginalExtension();
                $filename = 'nilai_ghm' . now()->timestamp . '.' . $extension;

                Storage::disk('public')->putFileAs('website/layanan/', $request->file('gambar'), $filename);
                $data['gambar_layanan'] = $filename;
            } else {
                $data['gambar_layanan'] = $request->gambar_lama;
            }

            if ($idnilai) {
                $model = Weblayanan::where('id', $idnilai);
                $model->update($data);
                $hapusNilai = Webdetaillayanan::where('layanan_id', $idnilai)->delete();

                // $dtTampungTim = session()->get('data_tim_sementara', []);
                foreach ($dtTampung as $tim) {
                    Webdetaillayanan::create([
                        'id'         => $tim['id'],
                        'layanan_id' => $idnilai,
                        'nourut'     => $tim['nourut'],
                        'nama'       => $tim['nama'],
                        'quote'      => $tim['quote'],
                        'photo'      => $tim['photo'],
                    ]);
                }
                //Hapus Session Sementara
                session()->forget('data_nilai_sementara');
            } else {
                $simpan = Weblayanan::create($data);
                $idsimpan = $simpan->id;

                foreach ($dtTampung as $tim) {
                    Webdetaillayanan::create([
                        'id'         => $tim['id']->toString(),
                        'layanan_id' => $idsimpan,
                        'nourut'     => $tim['nourut'],
                        'nama'       => $tim['nama'],
                        'quote'      => $tim['quote'],
                        'photo'      => $tim['photo'],
                    ]);
                }
                //Hapus Session Sementara
                session()->forget('data_nilai_sementara');
            }


            return response()->json([
                'message' => 'Data Berhasil Simpan.'
            ]);
        }
    }

    public function detail_nilai_tampung(Request $request)
    {
        $data = session('data_nilai_sementara', []);
        $item = collect($data)->firstWhere('id', $request->id);
        return response()->json($item, 200);
    }

    public function tampung_nilai_korporasi(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'logo'   => 'required',
                'nourut' => 'required|numeric',
                'quote'  => 'required',
                'nama'   => 'required',
            ],
            [
                'logo.required'   => 'Icon atau Logo masih kosong.',
                'nama.required'   => 'Title Untuk Nilai Perusahaan masih kosong.',
                'quote.required'  => 'Quote atau Kutipan masih kosong.',
                'nourut.required' => 'Nomor urut masih kosong.',
                'nourut.numeric'  => 'Nomor urut harus angka.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 401);
        } else {

            $idtampung = $request->idtampung;
            // Ambil data lama, tambahkan data baru
            $data = session()->get('data_nilai_sementara', []);

            $dataNilaiBaru = [
                // 'id'        => Str::uuid(),
                'nama'   => trim(strtoupper($request->nama)),
                'nourut' => trim($request->nourut),
                'photo'  => trim($request->logo),
                'quote'  => trim($request->quote),
            ];

            if ($idtampung) {
                // Update Data Session Berdasarkan ID
                $dataUpdate = collect($data)->map(function ($item) use ($idtampung, $dataNilaiBaru) {

                    if ($item['id'] == $idtampung) {
                        return array_merge($item, $dataNilaiBaru);
                    }
                    return $item;
                })->values()->toArray();

                session()->put('data_nilai_sementara', $dataUpdate);
            } else {
                // Simpan Baru
                $dataNilaiBaru['id'] = Str::uuid();
                $data[] = $dataNilaiBaru;
                session()->put('data_nilai_sementara', $data);
            }

            return response()->json([
                'message' => 'Data Berhasil Ditambah.'
            ]);
        }
    }

    public function hapus_nilai_tampung(Request $request)
    {
        // Ambil data Session
        $data = session()->get('data_nilai_sementara', []);
        $filtered = collect($data)->reject(fn($item) => $item['id'] == $request->id)->values()->toArray();

        session()->put('data_nilai_sementara', $filtered);
        return response()->json([
            'message' => 'Data Berhasil Dihapus.'
        ]);
    }

    // ============================================================================================================ LEGALITAS
    public function legalitas(Request $request)
    {
        if ($request->ajax()) {
            $query = Weblayanan::where('kategori', 'LEGALITAS')
                ->get()
                ->toArray();

            return DataTables::of($query)

                ->addColumn('action', function ($row) {

                    $button = '<div class="btn-group">
                        <span class="btn btn-sm btn-warning editLegalitas" data-slug=' . $row['slug'] . '><i class="far fa-edit me-1"></i>Edit</span>
                        <span class="btn btn-sm btn-danger hapusLegalitas" data-id=' . $row['id'] . '><i class="fas fa-trash me-1"></i>Hapus</span>
                    </div>';

                    return $button;
                })

                ->rawColumns(['action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('website.legalitas', [
            'judul' => 'Legalitas Perusahaan'
        ]);
    }

    public function store_legalitas(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title'     => 'required',
                'kategori'  => 'required',
                'shortdesc' => 'required',
            ],
            [
                'title.required'     => 'Nama atau Title Legalitas Perusahaan masih kosong.',
                'kategori.required'  => 'Kategori masih kosong.',
                'shortdesc.required' => 'Keterangan Legalitas Perusahaan masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 401);
        } else {
            $idlegal = $request->idlegal;
            $data = array(
                'title'             => trim(strtoupper($request->title)),
                'kategori'          => trim(strtoupper($request->kategori)),
                'deskripsi_singkat' => trim($request->shortdesc),
                'gambar_layanan'    => 'flaticon-chart',
            );

            if ($idlegal) {
                $update = Weblayanan::where('id', $idlegal);
                $update->update($data);
            } else {
                $save = Weblayanan::create($data);
            }

            return response()->json([
                'message' => 'Legalitas Perusahaan Berhasil Disimpan.'
            ]);
        }
    }

    public function detail_legalitas(Request $request)
    {
        $query = Weblayanan::where('slug', $request->slug)->firstOrFail();
        return response()->json($query, 200);
    }

    public function hapus_legalitas(Request $request)
    {
        $hapus = Weblayanan::find($request->id)->delete();
        return response()->json([
            'message' => 'Legalitas Perusahaan Berhasil Dihapus.'
        ]);
    }

    // ============================================================================================================ PORTOFOLIO
    public function portofolio(Request $request)
    {
        if ($request->ajax()) {
            $query = Weblayanan::where('kategori', 'PORTOFOLIO')
                ->get()
                ->toArray();

            return DataTables::of($query)

                ->addColumn('gambar', function ($row) {
                    $url = '/assets/img/avatars/no-image.png';
                    if ($row['gambar_layanan'] != null) {
                        $url = '/storage/website/layanan/' . $row['gambar_layanan'];
                    }

                    return '
                        <a href="' . $url . '" class="glightbox" data-glightbox="type: image; zoomable: true;" data-title="' . $row['title'] . '" data-description="' . $row['deskripsi_singkat'] . '">
                            <img src="' . $url . '" class="img-thumbnail" alt="image" width="70">
                        </a>
                    ';
                    // return '<img src="' . $url . '" width="70" class="img-thumbnail" />';
                })

                ->addColumn('action', function ($row) {

                    $button = '<div class="btn-group">
                        <span class="btn btn-sm btn-warning edit" data-slug=' . $row['slug'] . '><i class="far fa-edit me-1"></i>Edit</span>
                        <span class="btn btn-sm btn-danger hapus" data-id=' . $row['id'] . '><i class="fas fa-trash me-1"></i>Hapus</span>
                    </div>';

                    return $button;
                })

                ->rawColumns(['gambar' => 'gambar', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('website.portofolio', [
            'judul' => 'Portofolio Perusahaan'
        ]);
    }

    public function store_portofolio(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'gambar' => $request->hasFile('gambar')
                    ? 'required|image|mimes:jpeg,jpg,png|dimensions:width=525,height=808'
                    :  'nullable',
                'title'     => 'required',
                'kategori'  => 'required',
                'shortdesc' => 'required',
            ],
            [
                'gambar.required'    => 'Gambar masih kosong.',
                'gambar.dimensions'  => 'Oops! Ukuran Gambar harus 525 x 808',
                'title.required'     => 'Judul Portofolio masih kosong.',
                'kategori.required'  => 'Kategori masih kosong.',
                'shortdesc.required' => 'Keterangan masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 401);
        } else {
            $idport = $request->idport;
            $data = array(
                'kategori'          => $request->kategori,
                'title'             => trim(strtoupper($request->title)),
                'deskripsi_singkat' => trim($request->shortdesc),
            );

            // Menentukan Nama Gambar
            if ($request->hasFile('gambar')) {

                $image = $request->file('gambar');
                $size = getimagesize($image);

                $width = $size[0];
                $height = $size[1];

                if ($width != 525 || $height != 808) {
                    return response()->json([
                        'message' => 'Ukuran gambar harus 525 x 808 pixel'
                    ], 401);
                }

                $extension = $request->file('gambar')->getClientOriginalExtension();
                $filename = 'portofolio_' . now()->timestamp . '.' . $extension;

                Storage::disk('public')->putFileAs('website/layanan/', $request->file('gambar'), $filename);
                $data['gambar_layanan'] = $filename;
            } else {
                $data['gambar_layanan'] = $request->gambarlama;
            }

            if ($idport) {
                $update = Weblayanan::where('id', $idport);
                $update->update($data);
            } else {
                $save = Weblayanan::create($data);
            }

            return response()->json([
                'message' => 'Portofolio Berhasil Disimpan.'
            ]);
        }
    }

    public function detail_portofolio(Request $request)
    {
        $query = Weblayanan::where('slug', $request->slug)->first();
        return response()->json($query, 200);
    }

    public function hapus_portofolio(Request $request)
    {
        $hapus = Weblayanan::find($request->id)->delete();
        return response()->json([
            'message' => 'Portofolio Berhasil Dihapus.'
        ]);
    }

    // ============================================================================================================ LAYANAN
    public function layanan(Request $request)
    {
        if ($request->ajax()) {
            $query = Weblayanan::where('kategori', 'LAYANAN')
                ->get()
                ->toArray();

            return DataTables::of($query)

                ->addColumn('gambar', function ($row) {
                    $url = '/assets/img/avatars/no-image.png';
                    if ($row['gambar_layanan'] != null) {
                        $url = '/storage/website/layanan/' . $row['gambar_layanan'];
                    }

                    return '
                        <a href="' . $url . '" class="glightbox" data-glightbox="type: image; zoomable: true;" data-title="' . $row['title'] . '" data-description="' . $row['deskripsi_singkat'] . '">
                            <img src="' . $url . '" class="img-thumbnail" alt="image" width="70">
                        </a>
                    ';
                    // return '<img src="' . $url . '" width="70" class="img-thumbnail" />';
                })

                ->addColumn('action', function ($row) {

                    $button = '<div class="btn-group">
                        <span class="btn btn-sm btn-warning edit" data-slug=' . $row['slug'] . '><i class="far fa-edit me-1"></i>Edit</span>
                        <span class="btn btn-sm btn-danger hapus" data-id=' . $row['id'] . '><i class="fas fa-trash me-1"></i>Hapus</span>
                    </div>';

                    return $button;
                })

                ->rawColumns(['gambar' => 'gambar', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('website.layanan', [
            'judul' => 'Layanan Perusahaan'
        ]);
    }

    public function store_layanan(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'gambar' => $request->hasFile('gambar')
                    ? 'required|image|mimes:jpeg,jpg,png|dimensions:width=800,height=500'
                    :  'nullable',
                'title'     => 'required',
                'kategori'  => 'required',
                'shortdesc' => 'required',
                'longdesc'  => 'required',
            ],
            [
                'gambar.required'    => 'Gambar masih kosong.',
                'gambar.dimensions'  => 'Oops! Ukuran Gambar harus 800 x 500 pixel',
                'title.required'     => 'Nama Layanan masih kosong.',
                'kategori.required'  => 'Kategori masih kosong.',
                'shortdesc.required' => 'Keterangan Layanan masih kosong.',
                'longdesc.required'  => 'Deskripsi Lengkap Layanan masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 401);
        } else {

            $idlayanan = $request->idlayanan;

            $data = array(
                'kategori'          => trim(strtoupper($request->kategori)),
                'title'             => trim(strtoupper($request->title)),
                'deskripsi_singkat' => trim($request->shortdesc),
                'deskripsi_lengkap' => trim($request->longdesc),
            );

            // Menentukan Nama Gambar
            if ($request->hasFile('gambar')) {

                $image = $request->file('gambar');
                $size = getimagesize($image);

                $width = $size[0];
                $height = $size[1];

                if ($width != 800 || $height != 500) {
                    return response()->json([
                        'message' => 'Ukuran gambar harus 800 x 500 pixel'
                    ], 401);
                }

                $extension = $request->file('gambar')->getClientOriginalExtension();
                $filename = 'service_' . now()->timestamp . '.' . $extension;

                Storage::disk('public')->putFileAs('website/layanan/', $request->file('gambar'), $filename);
                $data['gambar_layanan'] = $filename;
            } else {
                $data['gambar_layanan'] = $request->gambarlama;
            }

            if ($idlayanan) {
                $update = Weblayanan::where('id', $idlayanan);
                $update->update($data);
            } else {
                $save = Weblayanan::create($data);
            }

            return response()->json([
                'message' => 'Layanan Berhasil Disimpan.'
            ]);
        }
    }

    public function detail_layanan(Request $request)
    {
        $query = Weblayanan::where('slug', $request->slug)->first();
        return response()->json($query, 200);
    }

    public function hapus_layanan($id)
    {
        $hapus = Weblayanan::find($id)->delete();
        return response()->json([
            'message' => 'Layanan Berhasil Dihapus.'
        ]);
    }
}
