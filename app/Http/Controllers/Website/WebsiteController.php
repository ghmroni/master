<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Webklienkami;
use App\Models\Webkontak;
use App\Models\Weblayanan;
use App\Models\Webslider;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;


class WebsiteController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            // new Middleware(PermissionMiddleware::using('edit-website'), only: ['index']),
            // new Middleware(PermissionMiddleware::using('edit-website'), except: ['index']),
            new Middleware(PermissionMiddleware::using('kelola-website')),
        ];
    }

    public function slider(Request $request)
    {
        if ($request->ajax()) {
            $orderby = ['1', '2', '3', '4', '5', '6', '0'];
            $query = Webslider::orderByRaw('FIELD(no_urut, ' . implode(',', $orderby) . ')')
                ->get()
                ->toArray();

            return DataTables::of($query)

                ->addColumn('gambar', function ($row) {
                    $url = '/assets/img/avatars/no-image.png';
                    if ($row['image'] != null) {
                        $url = '/storage/website/slider/' . $row['image'];
                    }

                    return '
                        <a href="' . $url . '" class="glightbox" data-glightbox="type: image; zoomable: true;" data-title="' . $row['title'] . '" data-description="' . $row['short_deskripsi'] . '">
                            <img src="' . $url . '" class="img-thumbnail" alt="image" width="70">
                        </a>
                    ';
                    // return '<img src="' . $url . '" width="70" class="img-thumbnail" />';
                })

                ->addColumn('urutan', function ($row) {
                    if ($row['no_urut'] != 0) {
                        $urutan = '<span class="badge rounded-pill bg-label-success text-dark"><i class="fas fa-check-circle me-1 text-success"></i>KE - ' . $row['no_urut'] . '</span>';
                    } else {
                        $urutan = '<span class="badge rounded-pill bg-label-danger"><i class="fas fa-times-circle me-1 text-danger"></i>KE - ' . $row['no_urut'] . '</span>';
                    }
                    return $urutan;
                })

                ->addColumn('action', function ($row) {
                    $button = '<div class="btn-group">
                        <button class="btn btn-sm btn-info Detail" data-id=' . $row['slug'] . '><i class="far fa-edit me-1"></i>Edit</button>
                    </div>';

                    return $button;
                })

                ->rawColumns(['gambar' => 'gambar', 'urutan' => 'urutan', 'action' => 'action'])
                ->addIndexColumn()
                ->toJson();
        }

        return view('website.slider', [
            'judul' => 'Website Slider',
        ]);
    }

    public function slider_tambah(Request $request)
    {
        return view('website.slider-tambah', [
            'judul' => 'Tambah Slider',
        ]);
    }

    public function slider_detail($slug)
    {
        $query = Webslider::where('slug', $slug)->firstOrFail();

        return view('website.slider-detail', [
            'judul' => 'Tambah Slider',
            'data'  => $query
        ]);
    }

    public function slider_store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'gambar' => $request->hasFile('gambar')
                    ? 'required|image|mimes:jpeg,jpg,png|dimensions:min_width=1024,min_height=600'
                    :  'nullable',
                'judul'     => 'required',
                'nourut'    => 'required',
                'shortdesc' => 'required',
                'longdesc'  => 'required',
            ],
            [
                'gambar.required'    => 'Gambar Slider masih kosong.',
                'gambar.dimensions'  => 'Oops! Ukuran Gambar minimal atau 1024 x 600',
                'judul.required'     => 'Judul Slider masih kosong.',
                'nourut.required'    => 'Nomor Urut Slider masih kosong.',
                'shortdesc.required' => 'Deskripsi Singkat Slider masih kosong.',
                'longdesc.required'  => 'Deskripsi Singkat Slider masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 401);
        } else {
            $idslider = $request->idslider;
            $data = array(
                // 'image'             => $filename,
                'title'           => trim(strtoupper($request->input('judul'))),
                'short_deskripsi' => trim($request->input('shortdesc')),
                'long_deskripsi'  => trim($request->input('longdesc')),
            );

            // Menentukan Nama Gambar
            if ($request->hasFile('gambar')) {

                $extension = $request->file('gambar')->getClientOriginalExtension();
                $filename = Str::random(10) . now()->timestamp . '.' . $extension;

                Storage::disk('public')->putFileAs('website/slider/', $request->file('gambar'), $filename);
                $data['image'] = $filename;
            } else {
                $data['image'] = $request->gambar_lama;
            }

            $nourut = $request->input('nourut');
            if ($nourut > 0) {
                $cek = Webslider::where('no_urut', $request->nourut)->first();
                if ($cek) {
                    $model = Webslider::where('id', $cek->id);
                    $cek->update([
                        'no_urut' => 0,
                    ]);
                }

                $data['no_urut'] = $nourut;
            } else {
                $data['no_urut'] = $nourut;
            }

            if ($idslider) {
                $model = Webslider::where('id', $idslider);
                $model->update($data);
            } else {
                $simpan = Webslider::create($data);
            }

            return response()->json([
                'message' => 'Data Slider Berhasil Disimpan.'
            ]);
        }
    }

    public function detail_slider(Request $request)
    {
        if ($request->ajax()) {
            $query = Webslider::find($request->id);
            return response()->json($query, 200);
        }
    }

    public function kontak(Request $request)
    {
        if ($request->ajax()) {
            $query = Webkontak::whereNull('deleted_at')
                ->orderBy('kategori', 'ASC')
                ->orderBy('name', 'ASC')
                ->get()
                ->toArray();
            return response()->json($query, 200);
        }

        return view('website.kontak', [
            'judul' => 'Kontak Kami'
        ]);
    }

    public function tambah_kontak(Request $request)
    {
        return view('website.kontak-tambah', [
            'judul' => 'Tambah Kontak'
        ]);
    }

    public function detail_kontak(Request $request, $slug)
    {
        if ($request->ajax()) {
            $query = Webkontak::whereNull('deleted_at')
                ->where('slug', $slug)
                ->first();
            return response()->json($query, 200);
        }
        $query = Webkontak::whereNull('deleted_at')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('website.kontak-detail', [
            'judul' => 'Detail Kontak',
            'data' => $query
        ]);
    }

    public function store_kontak(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'kategori' => 'required|in:PUSAT,PERWAKILAN',
                'name'     => 'required',
                'email'    => 'required',
                'hp'       => 'required',
                'tlp'      => 'required',
                'alamat'   => 'required',
                'latInput' => 'required',
                'lngInput' => 'required',
            ],
            [
                'latInput.required' => 'Lokasi belum dipilih.',
                'lngInput.required' => 'Lokasi belum dipilih.',
                'name.required'     => 'Nama Kantor masih kosong.',
                'email.required'    => 'Email masih kosong.',
                'hp.required'       => 'Nomor Handphone masih kosong.',
                'tlp.required'      => 'Nomor Telephone masih kosong.',
                'alamat.required'   => 'Alamat Perusahaan masih kosong.',
                'kategori.required' => 'Kategori Perusahaan masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $id = $request->id;

            $data = array(
                'name'      => trim(strtoupper($request->input('name'))),
                'kategori'  => strtoupper($request->input('kategori')),
                'email'     => trim(strtolower($request->input('email'))),
                'tlp'       => trim(str_replace('-', '', $request->input('tlp'))),
                'fax'       => trim(str_replace('-', '', $request->input('fax'))),
                'handphone' => trim(str_replace(' ', '', $request->input('hp'))),
                'alamat'    => trim($request->input('alamat')),
                'latitude'  => trim($request->input('latInput')),
                'longitude' => trim($request->input('lngInput')),
            );

            if ($id) {
                // Cek apakah kategori PUSAT sedang id update
                if ($request->kategori === 'PUSAT') {
                    $existingPusat = Webkontak::whereNull('deleted_at')
                        ->where('kategori', 'PUSAT')
                        ->where('id', '!=', $id)
                        ->exists();

                    if ($existingPusat) {
                        return response()->json([
                            'message' => 'Kategori Kantor Pusat sudah pernah dibuat.',
                        ], 401);
                    }
                }

                $update = Webkontak::find($id);
                $update->update($data);
            } else {
                // Cek apakah sudah ada kategori PUSAT di database
                if ($request->kategori === 'PUSAT') {
                    $existingPusat = Webkontak::whereNull('deleted_at')
                        ->where('kategori', 'PUSAT')
                        ->exists();

                    if ($existingPusat) {
                        return response()->json([
                            'message' => 'Kategori Kantor Pusat sudah pernah dibuat.',
                        ], 401);
                    }
                }

                $update = Webkontak::create($data);
            }


            return response()->json([
                'message' => 'Data Kontak Berhasil Disimpan.'
            ]);
        }
    }

    public function visi_misi(Request $request)
    {
        if ($request->ajax()) {
            $query = Weblayanan::whereNull('deleted_at')
                ->where('kategori', 'VISIMISI')
                ->first();

            return response()->json($query, 200);
        }
        return view('website.visi-misi', [
            'judul' => 'Visi Misi GHM',
        ]);
    }

    public function store_visi_misi(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'kategori'  => 'required',
                'shortdesc' => 'required',
                'visi'      => 'required',
                'misi'      => 'required',
            ],
            [
                'shortdesc.required' => 'Fokus Perusahaan masih kosong.',
                'visi.required'      => 'Visi Perusahaan masih kosong.',
                'misi.required'      => 'Misi Perusahaan masih kosong.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $id = $request->idvisi;

            $data = array(
                'kategori'          => $request->kategori,
                'title'             => $request->title,
                'deskripsi_singkat' => trim($request->shortdesc),
                'deskripsi_lengkap' => implode('|', array(
                    trim($request->visi),
                    trim($request->misi),
                )),
                'gambar_layanan' => $request->gambar_layanan,
            );

            if ($id) {
                $model = Weblayanan::find($id);
                $model->update($data);
            } else {
                $save = Weblayanan::create($data);
            }

            return response()->json([
                'message' => 'Data Visi Misi Perusahaan Berhasil Disimpan.'
            ]);
        }
    }

    public function klien(Request $request)
    {
        if ($request->ajax()) {
            $query = Webklienkami::whereNull('deleted_at')
                ->where('keterangan', 'klien')
                ->orderBy('id', 'desc')
                ->get();
            return response()->json($query, 200);
        }
        return view('website.klien', [
            'judul' => 'Klien GHM'
        ]);
    }

    public function store_klien(Request $request)
    {
        if ($request->hasFile('file')) {

            $validator = Validator::make(
                $request->all(),
                [
                    'file' => 'required|image|mimes:jpeg,jpg,png|dimensions:width=400,height=300',
                ],
                [
                    'file.required'   => 'Gambar masih kosong.',
                    'file.dimensions' => 'Oops! Ukuran Gambar harus 400 x 300 pixel',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->messages()->first()
                ], 401);
            } else {
                $file = $request->file('file');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                Storage::disk('public')->putFileAs('website/klien/', $request->file('file'), $filename);

                Webklienkami::create([
                    'name' => $filename,
                    'keterangan' => 'klien',
                ]);

                return response()->json([
                    'success' => true,
                ]);
            }
        }
    }

    public function destroy_klien($slug)
    {
        $model = Webklienkami::findOrFail($slug);
        $model->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus dari daftar klien.'
        ]);
    }
}
