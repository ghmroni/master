<?php

namespace App\Http\Controllers\Psikotest;

use App\Http\Controllers\Controller;
use App\Models\CfitHasil;
use App\Models\CfitMaster;
use App\Models\CfitNorma;
use App\Models\DiscDefinisi;
use App\Models\DiscHasil;
use App\Models\DiscRules;
use App\Models\DiscScores;
use App\Models\DiscSoal;
use App\Models\MbtiHasil;
use App\Models\MbtiMaster;
use App\Models\MbtiSoal;
use App\Models\PapiHasil;
use App\Models\PapiMaster;
use App\Models\PapiNorma;
use App\Models\Pelamar;
use App\Models\Pemanggilan;
use App\Models\Wawancara;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Milon\Barcode\Facades\DNS2DFacade;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Yajra\DataTables\DataTables;

class PsikotestController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('psikotest-input'), only: ['cfit_input']),
            new Middleware(PermissionMiddleware::using('psikotest-hasil'), only: ['data_psikotest']),
        ];
    }

    ## ==================================================================================================================== CFIT
    public function cfit_input(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $pemanggilan = Pemanggilan::select('id', 'no_registrasi', 'tgl_panggil', 'cfit_id')
                ->whereNull('deleted_at')
                ->whereNull('cfit_id')
                ->where([
                    'id' => $id,
                    'status' => 'PROSES',
                ])
                ->whereHas('lowonganApply.pelamar', function ($q) {
                    $q->where('status', 'PROGRESS');
                })
                ->with([
                    'lowonganApply.pelamar:id_pelamar,nama,tgllahir,jenis_kelamin'
                ])
                ->firstOrFail();

            $master = CfitMaster::select('id', 'sub_test', 'nomor_soal')->get();

            $peserta = $pemanggilan->lowonganApply->pelamar;
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }

        return view('psikotest.cfit-input', [
            'judul' => 'CFIT (Culture Fair Intelligence)'
        ], compact('pemanggilan', 'peserta', 'master'));
    }

    public function cfitRealtime(Request $request)
    {
        $input_jawaban = $request->input('jawaban');
        dd($input_jawaban);
        if (empty($input_jawaban)) {
            echo json_encode(['status' => 'empty', 'iq' => '-', 'ws' => '-', 'kategori' => '-']);
            return;
        }

        // 1. Hitung total skor benar (skor mentah)
        $total_benar = 0;
        foreach ($input_jawaban as $id_master => $jawaban_user) {
            $soal = CfitMaster::where('id', $id_master)->first();
            if ($soal && strtoupper($soal->kunci_jawaban) == strtoupper($jawaban_user)) {
                $total_benar++;
            }
        }

        $norma = CfitNorma::where('skor_mentah', $total_benar)->first();

        return response()->json([
            'iq'       => $norma->skor_standar, // Mengisi kolom IQ
            'nilai'    => $norma->skor_mentah,  // Mengisi kolom Nilai (Skor Mentah)
            'kategori' => $norma->keterangan
        ]);
    }

    public function cfitStore(Request $request)
    {
        $nomorForm = $request->input('nomor');
        $dtPanggil = Pemanggilan::select('id', 'cfit_id')->where('no_registrasi', 'LIKE', '%' . $nomorForm)->first();
        if (!empty($dtPanggil->cfit_id)) {
            return response()->json([
                'errors' => 'Data tes ini sudah diproses sebelumnya oleh pengguna lain.',
            ], 405);
        }

        DB::transaction(function () use ($request, $dtPanggil, $nomorForm) {

            $input_jawaban = $request->input('jawaban');
            $total_benar = 0;
            $data_jawaban = array();

            foreach ($input_jawaban as $id_master => $jawaban_user) {
                $jawaban_clean = strtoupper(trim($jawaban_user));
                // Ambil kunci jawaban asli dari cfit_master
                $soal = CfitMaster::where('id', $id_master)->first();

                // Tentukan status kebenaran jawaban (1 = Benar, 0 = Salah)
                $is_correct = 0;
                $nomor_soal = '-';

                if ($soal) {
                    $nomor_soal = $soal->nomor_soal;

                    if (strtoupper($soal->kunci_jawaban) == $jawaban_clean) {
                        $is_correct = 1;
                        $total_benar++;
                    }
                }

                # Masukan ke data lampiran
                $data_jawaban[$soal->sub_test][] = [
                    'nomor_soal' => $nomor_soal,
                    'jawaban'    => $jawaban_clean,
                    'status'     => ($is_correct == 1) ? 'Benar' : 'Salah'
                ];
            }

            $norma = CfitNorma::where('skor_mentah', $total_benar)->first();

            $skor_standar = $norma ? $norma->skor_standar : 0;
            $keterangan   = $norma ? $norma->keterangan : 'TIDAK TERDEFINISI';

            $usia = Carbon::parse($request->input('tgllahir'))->diff(Carbon::now());
            $tahun = $usia->y; // Mengambil total tahun
            $bulan = $usia->m; // Mengambil sisa bulan

            $idHasil = CfitHasil::create([
                'user_id'      => auth()->id(),
                'pelamar_id'   => $request->input('idpel'),
                'noreg'        => $nomorForm,
                'tglpanggil'   => $request->input('tgltest'),
                'nama'         => $request->input('nama'),
                'tgllahir'     => $request->input('tgllahir'),
                'kelamin'      => $request->input('kelamin'),
                'usia'         => $tahun . ' Tahun ' . $bulan . ' Bulan',
                'skor_mentah'  => $total_benar,
                'skor_standar' => $skor_standar,
                'keterangan'   => $keterangan,
                'data_jawaban' => $data_jawaban ?? [],
                'created_at'   => now(),
                'updated_at'   => now()
            ])->id;

            $testCfit = Pemanggilan::findOrFail($dtPanggil->id);
            $testCfit->update([
                'cfit_id' => $idHasil,
            ]);
        });

        return response()->json([
            'message' => 'Data Hasil Test Berhasil Disimpan.'
        ], 200);
    }

    public function cetak_cfit(Request $request)
    {
        $token   = $request->input('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $data = CfitHasil::findOrFail($id);

        $pdf = Pdf::loadView('psikotest.pdf_cfit', [
            'data' => $data,
        ]);

        $filename = 'HASIL PSIKOTEST CFIT - ' . $data->nama . '.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    ## ========================================================================================================================================== MBTI
    public function mbti_input(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $pemanggilan = Pemanggilan::select('id', 'no_registrasi', 'tgl_panggil', 'mbti_id')
                ->whereNull('deleted_at')
                ->whereNull('mbti_id')
                ->where([
                    'id' => $id,
                    'status' => 'PROSES',
                ])
                ->whereHas('lowonganApply.pelamar', function ($q) {
                    $q->where('status', 'PROGRESS');
                })
                ->with([
                    'lowonganApply.pelamar:id_pelamar,nama,tgllahir,jenis_kelamin'
                ])
                ->firstOrFail();

            $soals = MbtiSoal::select('id', 'nomor_soal', 'soal', 'pilihan_a', 'pilihan_b')->get();

            $peserta = $pemanggilan->lowonganApply->pelamar;
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }

        return view('psikotest.mbti-input', [
            'judul' => 'MBTI (Myers-Briggs Type Indicator)'
        ], compact('pemanggilan', 'peserta', 'soals'));
    }

    public function mbtiRealtime(Request $request)
    {
        $jawaban_user = $request->input('jawaban');

        $jawaban_valid = array_filter($jawaban_user, function ($val) {
            return $val !== null && trim($val) !== '';
        });

        if (empty($jawaban_valid)) {
            echo json_encode(array(
                'persentase' => array(
                    'persent_e' => 0,
                    'persent_i' => 0,
                    'persent_s' => 0,
                    'persent_n' => 0,
                    'persent_t' => 0,
                    'persent_f' => 0,
                    'persent_j' => 0,
                    'persent_p' => 0,
                ),
                'dimensi' => ['I', 'N', 'F', 'P'],
                'analisis' => null // Set null agar di jquery variable resume Anda bernilai null
            ));
            return;
        }

        $skor = array('E' => 0, 'I' => 0, 'S' => 0, 'N' => 0, 'T' => 0, 'F' => 0, 'J' => 0, 'P' => 0);
        $total_terjawab = 0;

        foreach ($jawaban_user as $id_soal => $pilihan) {
            $pilihan = strtoupper(trim($pilihan));
            if ($pilihan !== 'A' && $pilihan !== 'B') {
                continue; // Skip jika bukan A atau B
            }

            // Ambil data soal berdasarkan id_mbti_soal
            $soal = MbtiSoal::where('id', $id_soal)->first();
            if ($soal) {
                $total_terjawab++;
                // Cek target dimensi berdasarkan pilihan user (A atau B)
                if ($pilihan === 'A') {
                    $dimensi_target = strtoupper($soal->target_a);
                } else {
                    $dimensi_target = strtoupper($soal->target_b);
                }

                // Tambah skor ke dimensi yang sesuai
                if (isset($skor[$dimensi_target])) {
                    $skor[$dimensi_target]++;
                }
            }
        }

        $pct_E = round(($skor['E'] / 10) * 100);
        $pct_I = round(($skor['I'] / 10) * 100);

        // Dimensi S, N, T, F, J, P dibagi 20
        $pct_S = round(($skor['S'] / 20) * 100);
        $pct_N = round(($skor['N'] / 20) * 100);

        $pct_T = round(($skor['T'] / 20) * 100);
        $pct_F = round(($skor['F'] / 20) * 100);

        $pct_J = round(($skor['J'] / 20) * 100);
        $pct_P = round(($skor['P'] / 20) * 100);

        $dt_persentase = array(
            'persent_e' => $pct_E,
            'persent_i' => $pct_I,
            'persent_s' => $pct_S,
            'persent_n' => $pct_N,
            'persent_t' => $pct_T,
            'persent_f' => $pct_F,
            'persent_j' => $pct_J,
            'persent_p' => $pct_P,
        );

        // 4. Tentukan hasil sementara 4 huruf dimensi MBTI
        $dimensi_hasil = '';
        $dimensi_hasil .= ($skor['E'] > $skor['I']) ? 'E' : 'I';
        $dimensi_hasil .= ($skor['S'] > $skor['N']) ? 'S' : 'N';
        $dimensi_hasil .= ($skor['T'] > $skor['F']) ? 'T' : 'F';
        $dimensi_hasil .= ($skor['J'] > $skor['P']) ? 'J' : 'P';

        // Pecah tipe 4 dimensi menjadi huruf individual
        $huruf_1 = substr($dimensi_hasil, 0, 1); // E atau I
        $huruf_2 = substr($dimensi_hasil, 1, 1); // S atau N
        $huruf_3 = substr($dimensi_hasil, 2, 1); // T atau F
        $huruf_4 = substr($dimensi_hasil, 3, 1); // J atau P

        $data_dimensi = [$huruf_1, $huruf_2, $huruf_3, $huruf_4];

        $total_nilai_norma = 0;
        // Aturan Huruf Ke-1 (E/I): Jika I maka nilainya 9, selain itu (E) nilainya 5
        $total_nilai_norma += ($huruf_1 === 'I') ? 9 : 5;
        $total_nilai_norma += ($huruf_2 === 'N') ? 14 : 19;
        $total_nilai_norma += ($huruf_3 === 'F') ? 6 : 20;
        $total_nilai_norma += ($huruf_4 === 'P') ? 16 : 10;

        // Ambil data analisis berdasarkan total skor akhir dari tabel master norma
        $dt_analisis = MbtiMaster::with('analisis')
            ->where('total_skor', $total_nilai_norma)
            ->first();

        return response()->json([
            'persentase' => $dt_persentase,
            'dimensi'    => $data_dimensi,
            'analisis'   => $dt_analisis->analisis,
        ]);
    }

    public function mbtiStore(Request $request)
    {
        $nomorForm = $request->input('nomor');
        $dtPanggil = Pemanggilan::select('id', 'mbti_id')->where('no_registrasi', 'LIKE', '%' . $nomorForm)->first();
        if (!empty($dtPanggil->mbti_id)) {
            return response()->json([
                'errors' => 'Data tes ini sudah diproses sebelumnya oleh pengguna lain.',
            ], 405);
        }

        DB::transaction(function () use ($request, $nomorForm, $dtPanggil) {
            $usia = Carbon::parse($request->input('tgllahir'))->diff(Carbon::now());
            $tahun = $usia->y; // Mengambil total tahun
            $bulan = $usia->m; // Mengambil sisa bulan

            $idHasil = MbtiHasil::create([
                'user_id'    => auth()->id(),
                'pelamar_id' => $request->input('idpel'),
                'noreg'      => $nomorForm,
                'tglpanggil' => $request->input('tgltest'),
                'nama'       => $request->input('nama'),
                'tgllahir'   => $request->input('tgllahir'),
                'kelamin'    => $request->input('kelamin'),
                'usia'       => $tahun . ' Tahun ' . $bulan . ' Bulan',
                'jawaban'    => $request->jawaban ?? [],
                'dimensi'    => $request->hasil_dimensi ?? [],
                'persentase' => $request->hasil_persentase ?? [],
                'analisis'   => $request->hasil_analisis ?? [],
                'created_at' => now(),
                'updated_at' => now()
            ])->id;

            $testMbti = Pemanggilan::findOrFail($dtPanggil->id);
            $testMbti->update([
                'mbti_id' => $idHasil,
            ]);
        });

        return response()->json([
            'message' => 'Data Hasil Test Berhasil Disimpan.'
        ], 200);
    }

    public function cetak_mbti(Request $request)
    {
        $token   = $request->input('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $data = MbtiHasil::findOrFail($id);

        $pdf = Pdf::loadView('psikotest.pdf_mbti', [
            'data'  => $data,
        ]);

        $filename = 'HASIL PSIKOTEST MBTI - ' . $data->nama . '.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    ## =========================================================================================================================================== PAPI
    public function papi_input(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $pemanggilan = Pemanggilan::select('id', 'no_registrasi', 'tgl_panggil', 'papi_id')
                ->whereNull('deleted_at')
                ->whereNull('papi_id')
                ->where([
                    'id' => $id,
                    'status' => 'PROSES',
                ])
                ->whereHas('lowonganApply.pelamar', function ($q) {
                    $q->where('status', 'PROGRESS');
                })
                ->with([
                    'lowonganApply.pelamar:id_pelamar,nama,tgllahir,jenis_kelamin'
                ])
                ->firstOrFail();

            $peserta = $pemanggilan->lowonganApply->pelamar;
            $norma = PapiNorma::where('skor_min', 0)->get()->groupBy('factor_code');
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }

        return view('psikotest.papi-input', [
            'judul' => 'PAPI KOSTICK (Personality and Preference Inventory)'
        ], compact('pemanggilan', 'peserta', 'norma'));
    }

    public function papiRealtime(Request $request)
    {
        $jawaban = $request->input('jawaban');
        // Definisikan 20 aspek/faktor PAPI Kostick default (Wajib huruf besar)
        $skor_aspek = array(
            'G' => 0,
            'L' => 0,
            'I' => 0,
            'T' => 0,
            'V' => 0,
            'S' => 0,
            'R' => 0,
            'D' => 0,
            'C' => 0,
            'E' => 0,
            'N' => 0,
            'A' => 0,
            'P' => 0,
            'X' => 0,
            'B' => 0,
            'O' => 0,
            'Z' => 0,
            'K' => 0,
            'F' => 0,
            'W' => 0
        );

        if (!empty($jawaban)) {
            foreach ($jawaban as $nomor_soal => $pilihan) {
                if (!empty($pilihan)) {
                    $row = PapiMaster::select('factor_code')
                        ->where('nomor_soal', $nomor_soal)
                        ->where('pilihan', strtoupper($pilihan))
                        ->first();

                    if ($row && isset($skor_aspek[$row->factor_code])) {
                        $skor_aspek[$row->factor_code]++;
                    }
                }
            }
        }

        $total_atas = 0;
        $total_bawah = 0;

        // 2. Ambil data norma dan hasil analisis dari database berdasarkan skor total masing-masing aspek
        $hasil_faktor = array();
        foreach ($skor_aspek as $aspek => $total_skor) {
            // Fungsi get_analisis_norma() mencocokkan ($aspek, $total_skor) dengan rentang BETWEEN skor_min AND skor_max
            $norma = $this->getAnalisisNormaPapi($aspek, $total_skor);

            if ($norma) {
                $kategori_db = strtoupper($norma->kategori); // Ambil teks ATAS / BAWAH

                if ($total_skor > 0) {
                    if ($kategori_db === 'ATAS') {
                        $total_atas += $total_skor; // Menjumlahkan nilai skor aspeknya
                    } elseif ($kategori_db === 'BAWAH') {
                        $total_bawah += $total_skor; // Menjumlahkan nilai skor aspeknya
                    }
                }

                $hasil_faktor[$aspek] = array(
                    'bidang' => $norma->bidang,
                    'aspek'  => $norma->aspek,
                    'skor'       => $total_skor,
                    'kategori'   => $norma->kategori,
                    'analisis' => $norma->analisis,
                );
            } else {
                // Nilai default jika range skor tidak terdaftar di DB norma               
                $hasil_faktor[$aspek] = array(
                    'bidang'   => null,
                    'aspek'    => null,
                    'skor'     => $total_skor,
                    'kategori' => 'Tidak ada',
                    'analisis' => '',
                );
            }
        }

        return response()->json([
            'faktor'      => $hasil_faktor,
            'total_atas'  => $total_atas,
            'total_bawah' => $total_bawah
        ]);
    }

    public function getAnalisisNormaPapi($kode_aspek, $skor)
    {
        return DB::table('papi_norma')
            ->select('bidang', 'aspek', 'factor_code', 'kategori', 'analisis')
            ->where('factor_code', strtoupper($kode_aspek))
            ->whereRaw('? BETWEEN skor_min AND skor_max', [$skor])
            ->first();
    }

    public function papiStore(Request $request)
    {
        $nomorForm = $request->input('nomor');
        $dtPanggil = Pemanggilan::select('id', 'papi_id')->where('no_registrasi', 'LIKE', '%' . $nomorForm)->first();
        if (!empty($dtPanggil->papi_id)) {
            return response()->json([
                'errors' => 'Data tes ini sudah diproses sebelumnya oleh pengguna lain.',
            ], 405);
        }

        DB::transaction(function () use ($request, $nomorForm, $dtPanggil) {
            $usia = Carbon::parse($request->input('tgllahir'))->diff(Carbon::now());
            $tahun = $usia->y; // Mengambil total tahun
            $bulan = $usia->m; // Mengambil sisa bulan

            $idHasil = PapiHasil::create([
                'user_id'     => auth()->id(),
                'pelamar_id'  => $request->input('idpel'),
                'noreg'       => $nomorForm,
                'tglpanggil'  => $request->input('tgltest'),
                'nama'        => $request->input('nama'),
                'tgllahir'    => $request->input('tgllahir'),
                'kelamin'     => $request->input('kelamin'),
                'usia'        => $tahun . ' Tahun ' . $bulan . ' Bulan',
                'jawaban'     => $request->jawaban ?? [],
                'total_atas'  => $request->hasil_atas,
                'total_bawah' => $request->hasil_bawah,
                'analisis'    => $request->hasil_faktor ?? [],
                'created_at'  => now(),
                'updated_at'  => now()
            ])->id;

            $testPapi = Pemanggilan::findOrFail($dtPanggil->id);
            $testPapi->update([
                'papi_id' => $idHasil,
            ]);
        });

        return response()->json([
            'message' => 'Data Hasil Test Berhasil Disimpan.'
        ], 200);
    }

    public function cetak_papi(Request $request)
    {
        $token   = $request->input('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $data = PapiHasil::findOrFail($id);
        $norma = PapiNorma::where('skor_min', 0)->get()->groupBy('factor_code');

        $pdf = Pdf::loadView('psikotest.pdf_papi', [
            'data'  => $data,
            'norma' => $norma,
        ]);

        $filename = 'HASIL PSIKOTEST PAPI - ' . $data->nama . '.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    ## ========================================================================================================================================================= DISC
    public function disc_input(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $pemanggilan = Pemanggilan::select('id', 'no_registrasi', 'tgl_panggil', 'disc_id')
                ->whereNull('deleted_at')
                ->whereNull('disc_id')
                ->where([
                    'id' => $id,
                    'status' => 'PROSES',
                ])
                ->whereHas('lowonganApply.pelamar', function ($q) {
                    $q->where('status', 'PROGRESS');
                })
                ->with([
                    'lowonganApply.pelamar:id_pelamar,nama,tgllahir,jenis_kelamin'
                ])
                ->firstOrFail();

            $dataSoals = DiscSoal::select('id', 'nomor_soal', 'nomor_baris', 'pernyataan')->get();
            $soals = array();
            foreach ($dataSoals as $row) {
                $soals[$row->nomor_soal][] = $row;
            }

            $peserta = $pemanggilan->lowonganApply->pelamar;
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }

        return view('psikotest.disc-input', [
            'judul' => 'DISC (Dominance, Influence, Steadiness, dan Compliance)'
        ], compact('pemanggilan', 'peserta', 'soals'));
    }

    public function discRealtime(Request $request)
    {
        $nomorSoal  = $request->input('nomor_soal');
        $nomorBaris = $request->input('nomor_baris');
        $isMost     = $request->input('is_most');

        // Pastikan session berupa array kosong jika belum disetel (Inisialisasi)
        if (!session()->has('disc_most')) {
            session(['disc_most' => []]);
        }
        if (!session()->has('disc_least')) {
            session(['disc_least' => []]);
        }

        $sessionMost  = session('disc_most');
        $sessionLeast = session('disc_least');

        // Buat variabel default untuk menampung info faktor yang baru diklik
        $currentPaling = '*';
        $currentKurang = '*';

        // 1. CEK REALTIME DATA KOSONG (TOGGLE OFF) VS INPUT DATA BARU
        if (empty($nomorBaris) || $nomorBaris == '-') {

            if ($isMost == 1) {
                unset($sessionMost[$nomorSoal]);
            } else {
                unset($sessionLeast[$nomorSoal]);
            }
            session(['disc_most' => $sessionMost, 'disc_least' => $sessionLeast]);
        } else {
            // Ambil data master untuk mencatat click aktif saat ini via Eloquent
            $queryHasil = DiscSoal::where('nomor_soal', $nomorSoal)
                ->where('nomor_baris', $nomorBaris)
                ->first();

            if (!$queryHasil) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Soal tidak ditemukan'
                ], 404);
            }

            $currentPaling = strtoupper($queryHasil->faktor_paling);
            $currentKurang = strtoupper($queryHasil->faktor_kurang);

            if ($isMost == 1) {
                $sessionMost[$nomorSoal] = $nomorBaris;
            } else {
                $sessionLeast[$nomorSoal] = $nomorBaris;
            }
            session(['disc_most' => $sessionMost, 'disc_least' => $sessionLeast]);
        }

        // 2. HITUNG AKUMULASI SKOR GLOBAL BERDASARKAN MEMORI SESSION YANG SUDAH BERSIH
        $rawMost  = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0, '*' => 0];
        $rawLeast = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0, '*' => 0];

        $isianTabel = [];
        for ($i = 1; $i <= 24; $i++) {
            $isianTabel[$i] = ['p_baris' => '-', 'k_baris' => '-', 'p_faktor' => '*', 'k_faktor' => '*'];
        }

        foreach ($sessionMost as $noSoal => $noBaris) {
            $q = DiscSoal::where('nomor_soal', $noSoal)->where('nomor_baris', $noBaris)->first();
            if ($q) {
                $fPaling = strtoupper($q->faktor_paling);
                if (isset($rawMost[$fPaling])) {
                    $rawMost[$fPaling]++;
                }
                $isianTabel[$noSoal]['p_baris']  = (string) $q->nomor_baris;
                $isianTabel[$noSoal]['p_faktor'] = $fPaling;
            }
        }

        foreach ($sessionLeast as $noSoal => $noBaris) {
            $q = DiscSoal::where('nomor_soal', $noSoal)->where('nomor_baris', $noBaris)->first();
            if ($q) {
                $fKurang = strtoupper($q->faktor_kurang);
                if (isset($rawLeast[$fKurang])) {
                    $rawLeast[$fKurang]++;
                }
                $isianTabel[$noSoal]['k_baris']  = (string) $q->nomor_baris;
                $isianTabel[$noSoal]['k_faktor'] = $fKurang;
            }
        }

        $rawChange = [
            'D' => $rawMost['D'] - $rawLeast['D'],
            'I' => $rawMost['I'] - $rawLeast['I'],
            'S' => $rawMost['S'] - $rawLeast['S'],
            'C' => $rawMost['C'] - $rawLeast['C']
        ];

        // 3. KONVERSI KE KOORDINAT DESIMAL GRAFIK (RULES NORMA)
        // Catatan: Panggil method ini sesuai tempat Anda meletakkan logika model CI m_psikotest lawas di Laravel.
        // Di sini saya asumsikan Anda memanggil class Helper/Service atau menggunakan static method.
        $graphMost   = $this->getNormaDecimal(1, $rawMost);
        $graphLeast  = $this->getNormaDecimal(2, $rawLeast);
        $graphChange = $this->getNormaDecimal(3, $rawChange);

        $dataEvalMost = [
            'D' => $graphMost['D'] ?? ($graphMost[0] ?? 0),
            'I' => $graphMost['I'] ?? ($graphMost[1] ?? 0),
            'S' => $graphMost['S'] ?? ($graphMost[2] ?? 0),
            'C' => $graphMost['C'] ?? ($graphMost[3] ?? 0),
        ];

        $dataEvalLeast = [
            'D' => $graphLeast['D'] ?? ($graphLeast[0] ?? 0),
            'I' => $graphLeast['I'] ?? ($graphLeast[1] ?? 0),
            'S' => $graphLeast['S'] ?? ($graphLeast[2] ?? 0),
            'C' => $graphLeast['C'] ?? ($graphLeast[3] ?? 0),
        ];

        $dataEvalChange = [
            'D' => $graphChange['D'] ?? ($graphChange[0] ?? 0),
            'I' => $graphChange['I'] ?? ($graphChange[1] ?? 0),
            'S' => $graphChange['S'] ?? ($graphChange[2] ?? 0),
            'C' => $graphChange['C'] ?? ($graphChange[3] ?? 0),
        ];

        $descMask   = $this->getDefinitionByGraph($dataEvalMost, 'MASK');
        $descCore   = $this->getDefinitionByGraph($dataEvalLeast, 'CORE');
        $descMirror = $this->getDefinitionByGraph($dataEvalChange, 'MIRROR');

        // hitung TOTAL hanya untuk data_line
        $withTotal = function ($arr) {
            $arr['TOTAL'] = ($arr['D'] ?? 0) + ($arr['I'] ?? 0) + ($arr['S'] ?? 0) + ($arr['C'] ?? 0) + ($arr['*'] ?? 0);
            return $arr;
        };
        // 4. LEMPARKAN KEMBALI MENGGUNAKAN FORMAT JSON LARAVEL RESPONSE
        return response()->json([
            'status'        => 'success',
            'faktor_paling' => $currentPaling,
            'faktor_kurang' => $currentKurang,
            'isian_tabel'   => $isianTabel,
            'data_line'     => [
                'line1' => $withTotal($rawMost),
                'line2' => $withTotal($rawLeast),
                'line3' => $withTotal($rawChange)
            ],
            'data_grafik'   => [
                'most'   => [$graphMost['D'] ?? 0, $graphMost['I'] ?? 0, $graphMost['S'] ?? 0, $graphMost['C'] ?? 0],
                'least'  => [$graphLeast['D'] ?? 0, $graphLeast['I'] ?? 0, $graphLeast['S'] ?? 0, $graphLeast['C'] ?? 0],
                'change' => [$graphChange['D'] ?? 0, $graphChange['I'] ?? 0, $graphChange['S'] ?? 0, $graphChange['C'] ?? 0]
            ],
            'hasil_teks'    => [
                'mask_public'      => $descMask,
                'core_private'     => $descCore,
                'mirror_perceived' => $descMirror
            ]
        ]);
    }

    private function withTotal(array $arr): array
    {
        $arr['TOTAL'] = ($arr['D'] ?? 0) + ($arr['I'] ?? 0) + ($arr['S'] ?? 0) + ($arr['C'] ?? 0);
        return $arr;
    }

    public function getNormaDecimal($lineType, $rawScores)
    {
        $result = ['D' => 0.00, 'I' => 0.00, 'S' => 0.00, 'C' => 0.00];
        $dimensions = ['D', 'I', 'S', 'C'];

        foreach ($dimensions as $dim) {
            $score = isset($rawScores[$dim]) ? $rawScores[$dim] : 0;
            $columnName = 'score_' . strtolower($dim);

            $query = DiscScores::select($columnName . ' as nilai_desimal')
                ->where('line_type', $lineType)
                ->where('raw_score', (int)$score)
                ->first();

            if ($query) {
                // Mengubah koma menjadi titik (jika ada data format Indonesia)
                $cleanDesimal = str_replace(',', '.', $query->nilai_desimal);
                $result[$dim] = (float) $cleanDesimal;
            }
        }
        return $result;
    }


    public function getDefinitionByGraph($graphScores, $tipeGrafik = 'MASK')
    {
        // Ambil nilai desimal tiap dimensi grafik milik user
        $userD = $graphScores['D'] ?? 0.00;
        $userI = $graphScores['I'] ?? 0.00;
        $userS = $graphScores['S'] ?? 0.00;
        $userC = $graphScores['C'] ?? 0.00;

        $rules = DiscRules::all()->toArray();

        $matchedProfileNumber = null;

        // Lakukan perulangan untuk mencari pola yang cocok
        foreach ($rules as $rule) {
            // 1. Evaluasi Batas Garis Nol Dasar (>0 atau <=0)
            $checkD = ($rule['operator_d'] == '>') ? ($userD > 0) : ($userD <= 0);
            $checkI = ($rule['operator_i'] == '>') ? ($userI > 0) : ($userI <= 0);
            $checkS = ($rule['operator_s'] == '>') ? ($userS > 0) : ($userS <= 0);
            $checkC = ($rule['operator_c'] == '>') ? ($userC > 0) : ($userC <= 0);

            // 2. Evaluasi Kondisi Khusus Hierarki Urutan Tinggi Grafik (D, I, S, C)
            $checkSpecial = true;

            if (!empty($rule['special_rule'])) {
                switch ($rule['special_rule']) {
                    // Urutan 2 Dimensi
                    case 'c>=d':
                        $checkSpecial = ($userC >= $userD);
                        break;
                    case 'd>c':
                        $checkSpecial = ($userD > $userC);
                        break;
                    case 'c>=s':
                        $checkSpecial = ($userC >= $userS);
                        break;
                    case 's>=c':
                        $checkSpecial = ($userS >= $userC);
                        break;
                    case 'i>=s':
                        $checkSpecial = ($userI >= $userS);
                        break;
                    case 'i>=c':
                        $checkSpecial = ($userI >= $userC);
                        break;
                    case 's>=d':
                        $checkSpecial = ($userS >= $userD);
                        break;
                    case 's>=i':
                        $checkSpecial = ($userS >= $userI);
                        break;
                    case 'c>=i':
                        $checkSpecial = ($userC >= $userI);
                        break;

                    // Urutan 3 Dimensi Berantai
                    case 'i>=d>=c':
                        $checkSpecial = ($userI >= $userD && $userD >= $userC);
                        break;
                    case 'i>=d>=s':
                        $checkSpecial = ($userI >= $userD && $userD >= $userS);
                        break;
                    case 'i>=s>=d':
                        $checkSpecial = ($userI >= $userS && $userS >= $userD);
                        break;
                    case 's>=d>=c':
                        $checkSpecial = ($userS >= $userD && $userD >= $userC);
                        break;
                    case 'd>=i>=s':
                        $checkSpecial = ($userD >= $userI && $userI >= $userS);
                        break;
                    case 'c>=i>=s':
                        $checkSpecial = ($userC >= $userI && $userI >= $userS);
                        break;
                    case 'c>=s>=i':
                        $checkSpecial = ($userC >= $userS && $userS >= $userI);
                        break;
                    case 'd>=i>=c':
                        $checkSpecial = ($userD >= $userI && $userI >= $userC);
                        break;
                    case 'd>=s>=i':
                        $checkSpecial = ($userD >= $userS && $userS >= $userI);
                        break;
                    case 'd>=s>=c':
                        $checkSpecial = ($userD >= $userS && $userS >= $userC);
                        break;
                    case 'd>=c>=i':
                        $checkSpecial = ($userD >= $userC && $userC >= $userI);
                        break;
                    case 'd>=c>=s':
                        $checkSpecial = ($userD >= $userC && $userC >= $userS);
                        break;
                    case 'i>=c>=d':
                        $checkSpecial = ($userI >= $userC && $userC >= $userD);
                        break;
                    case 'i>=c>=s':
                        $checkSpecial = ($userI >= $userC && $userC >= $userS);
                        break;
                    case 's>=d>=i':
                        $checkSpecial = ($userS >= $userD && $userD >= $userI);
                        break;
                    case 's>=i>=d':
                        $checkSpecial = ($userS >= $userI && $userI >= $userD);
                        break;
                    case 's>=i>=c':
                        $checkSpecial = ($userS >= $userI && $userI >= $userC);
                        break;
                    case 's>=c>=d':
                        $checkSpecial = ($userS >= $userC && $userC >= $userD);
                        break;
                    case 's>=c>=i':
                        $checkSpecial = ($userS >= $userC && $userC >= $userI);
                        break;
                    case 'c>=d>=i':
                        $checkSpecial = ($userC >= $userD && $userD >= $userI);
                        break;
                    case 'c>=d>=s':
                        $checkSpecial = ($userC >= $userD && $userD >= $userS);
                        break;
                    case 'c>=i>=d':
                        $checkSpecial = ($userC >= $userI && $userI >= $userD);
                        break;

                    default:
                        $checkSpecial = true;
                        break;
                }
            }

            // 3. JIKA LOLOS ATURAN DASAR DAN ATURAN KHUSUS
            if ($checkD && $checkI && $checkS && $checkC && $checkSpecial) {
                $matchedProfileNumber = $rule['profile_number'];
                if ($tipeGrafik !== 'MASK') {
                    break;
                }
            }
        }

        // Fallback ke profil default jika tidak ada pola cocok
        if ($matchedProfileNumber === null) {
            $matchedProfileNumber = 40;
        }

        $definition = DiscDefinisi::where('profile_number', $matchedProfileNumber)->first();

        // Jika data definisi ditemukan, map nama kolom ke field bahasa Indonesia terbaru Anda
        if ($definition) {
            return [
                'profile_number' => $definition->profile_number,
                'profile_code'   => $definition->profile_code,
                'profile_title'  => $definition->profile_title,
                // Jika kolom karakter disimpan dalam bentuk JSON, decode otomatis menjadi array PHP
                'karakter'       => $definition->karakter ?? [],
                'kepribadian'    => $definition->kepribadian,
                'pekerjaan'      => $definition->pekerjaan
            ];
        }

        // Fallback jika seeder data master belum masuk ke database
        return [
            'profile_number' => $matchedProfileNumber,
            'profile_code'   => '-',
            'profile_title'    => 'Profile ' . $matchedProfileNumber,
            'karakter'       => [],
            'kepribadian'    => 'Data deskripsi belum di-seeder di database untuk nomor ' . $matchedProfileNumber,
            'pekerjaan'      => '-'
        ];
    }

    public function discStore(Request $request)
    {
        $nomorForm = $request->input('nomor');
        $dtPanggil = Pemanggilan::select('id', 'disc_id')->where('no_registrasi', 'LIKE', '%' . $nomorForm)->first();
        if (!empty($dtPanggil->disc_id)) {
            return response()->json([
                'errors' => 'Data tes ini sudah diproses sebelumnya oleh pengguna lain.',
            ], 405);
        }

        DB::transaction(function () use ($request, $nomorForm, $dtPanggil) {
            $usia = Carbon::parse($request->input('tgllahir'))->diff(Carbon::now());
            $tahun = $usia->y; // Mengambil total tahun
            $bulan = $usia->m; // Mengambil sisa bulan

            $idHasil = DiscHasil::create([
                'user_id'       => auth()->id(),
                'pelamar_id'    => $request->input('idpel'),
                'noreg'         => $nomorForm,
                'tglpanggil'    => $request->input('tgltest'),
                'nama'          => $request->input('nama'),
                'tgllahir'      => $request->input('tgllahir'),
                'kelamin'       => $request->input('kelamin'),
                'usia'          => $tahun . ' Tahun ' . $bulan . ' Bulan',
                'most'          => $request->most ?? [],
                'least'         => $request->least ?? [],
                'data_line'     => $request->hasil_line ?? [],
                'data_grafik'   => $request->hasil_grafik ?? [],
                'data_faktor'   => $request->hasil_tabel ?? [],
                'data_karakter' => $request->hasil_definisi ?? [],
                'kepribadian'   => $request->hasil_kepribadian,
                'job_match'     => $request->hasil_job,
                'created_at'    => now(),
                'updated_at'    => now()
            ])->id;

            $testPapi = Pemanggilan::findOrFail($dtPanggil->id);
            $testPapi->update([
                'disc_id' => $idHasil,
            ]);
        });

        return response()->json([
            'message' => 'Data Hasil Test Berhasil Disimpan.'
        ], 200);
    }

    public function cetak_disc(Request $request)
    {
        $token   = $request->input('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $data = DiscHasil::findOrFail($id);

        $pdf = Pdf::loadView('psikotest.pdf_disc', [
            'data'  => $data,
        ]);

        $filename = 'HASIL PSIKOTEST DISC - ' . $data->nama . '.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    ##================================================================================================================================================ WAWANCARA
    public function wawancara(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $pemanggilan = Pemanggilan::select('id', 'no_registrasi', 'tgl_panggil', 'wawancara_id')
                ->whereNull('deleted_at')
                ->whereNull('wawancara_id')
                ->where([
                    'id'     => $id,
                    'status' => 'PROSES',
                ])
                ->whereHas('lowonganApply.pelamar', function ($q) {
                    $q->where('status', 'PROGRESS');
                })
                ->with([
                    'lowonganApply.pelamar:id_pelamar,nama,tgllahir,jenis_kelamin,jenjang_pendidikan,jurusan_sekolah,berat_badan,tinggi_badan,referensi'
                ])
                ->firstOrFail();
            $peserta = $pemanggilan->lowonganApply->pelamar;
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }

        return view('psikotest.wawancara', compact('pemanggilan', 'peserta'));
    }

    public function store_wawancara(Request $request)
    {
        try {
            $idpanggil = $request->input('pemanggilanid');
            $idpel = $request->input('pelamarid');
            $kategori = $request->input('kategori');

            $cek = Pemanggilan::whereNull('wawancara_id')
                ->where([
                    'id'     => $idpanggil,
                    'status' => 'PROSES',
                ])->exists();
            if (!$cek) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Wawancara sudah pernah diinput.'
                ], 409);
            }

            DB::transaction(function () use ($request, $idpanggil, $idpel, $kategori) {
                $pemanggilan = Pemanggilan::findOrFail($idpanggil);
                $pelamar = Pelamar::findOrFail($idpel);

                $wawancaraId = Wawancara::create([
                    'pemanggilan_id'     => $idpanggil,
                    'pelamar_id'         => $idpel,
                    'user_id'            => auth()->user()->id,
                    'tglpanggil'         => $pemanggilan->tgl_panggil,
                    'nama'               => $pelamar->nama,
                    'tgllahir'           => $pelamar->tgllahir,
                    'jenjang_pendidikan' => $pelamar->jenjang_pendidikan . ' (' . $pelamar->jurusan_sekolah . ')',
                    'kategori'           => $kategori,
                    'bb'                 => $pelamar->berat_badan,
                    'tb'                 => $pelamar->tinggi_badan,
                    'referensi'          => $request->referensi,
                    'pendidikan'         => $request->pendidikan,
                    'pengalaman'         => $request->pengalaman,
                    'pengetahuan'        => $request->pengetahuan,
                    'keterampilan'       => $request->keterampilan,
                    'motivasi'           => $request->motivasi,
                    'kerjasama'          => $request->kerjasama,
                    'etika'              => $request->etika,
                    'komunikasi'         => $request->komunikasi,
                    'bahasa'             => $request->bahasa,
                    'penampilan'         => $request->penampilan,
                    'memimpin'           => $request->memimpin,
                    'bahasa_asing'       => trim(strtoupper($request->bahasa_asing)),
                    'hasil'              => $request->hasil,
                    'catatan'            => trim($request->catatan),
                ])->id;

                $pemanggilan->update(['wawancara_id' => $wawancaraId]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi error: ' . $e->getMessage()
            ]);
        }
    }

    public function cetak_wawancara(Request $request)
    {
        $token   = $request->input('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $data = Wawancara::with(['user:id,name'])->findOrFail($id);
        // dd($data);
        $pdf = Pdf::loadView('psikotest.pdf_wawancara', [
            'data'  => $data,
        ]);

        $filename = 'HASIL INTERVIEW - ' . $data->nama . '.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    ## ===================================================================================================================================== HASIL
    public function data_psikotest(Request $request)
    {
        if ($request->ajax()) {
            $role = auth()->user()->roles()->first()?->id;
            $query = Pemanggilan::whereNull('deleted_at')
                ->with([
                    'user:id,name',
                    'lowonganApply.pelamar:id_pelamar,nama,status',
                    'lowonganApply.lowongan.project' => function ($q) {
                        $q->select('id', 'name', 'logo');
                    }
                ])
                ->where(function ($q) {
                    $q->whereNotNull('cfit_id')
                        ->orWhereNotNull('mbti_id')
                        ->orWhereNotNull('papi_id')
                        ->orWhereNotNull('disc_id')
                        ->orWhereNotNull('wawancara_id');
                })
                ->when(
                    $role === 6, // PROJECT LEADER
                    fn($q) => $q->where('user_id', auth()->id())
                )
                ->get()
                ->toArray();

            return DataTables::of($query)
                ->addColumn('tglpanggil', function ($row) {
                    return Carbon::parse($row['tgl_panggil'])->translatedFormat('l, d F Y');
                })

                ->addColumn('cfit', function ($row) {
                    $payload = [
                        'id'  => $row['cfit_id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $tokenCfit = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    return ($row['cfit_id']
                        ? '<i class="ti ti-cloud-down fa-2x lihatHasil text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Hasil" data-token="' . $tokenCfit . '" data-url="/cetak-hasil-cfit" style="cursor:pointer"></i>'
                        : '<i class="ti ti-minus text-danger fa-2x"></i>'
                    );
                })

                ->addColumn('mbti', function ($row) {
                    $payload = [
                        'id'  => $row['mbti_id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $tokenMbti = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    return ($row['mbti_id']
                        ? '<i class="ti ti-cloud-down fa-2x lihatHasil text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Hasil" data-token="' . $tokenMbti . '" data-url="/cetak-hasil-mbti" style="cursor:pointer"></i>'
                        : '<i class="ti ti-minus text-danger fa-2x"></i>'
                    );
                })

                ->addColumn('papi', function ($row) {
                    $payload = [
                        'id'  => $row['papi_id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $tokenPapi = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    return ($row['papi_id']
                        ? '<i class="ti ti-cloud-down fa-2x lihatHasil text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Hasil" data-token="' . $tokenPapi . '" data-url="/cetak-hasil-papi" style="cursor:pointer"></i>'
                        : '<i class="ti ti-minus text-danger fa-2x"></i>'
                    );
                })

                ->addColumn('disc', function ($row) {
                    $payload = [
                        'id'  => $row['disc_id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $tokenDisc = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    return ($row['disc_id']
                        ? '<i class="ti ti-cloud-down fa-2x lihatHasil text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Hasil" data-token="' . $tokenDisc . '" data-url="/cetak-hasil-disc" style="cursor:pointer"></i>'
                        : '<i class="ti ti-minus text-danger fa-2x"></i>'
                    );
                })

                ->addColumn('wawancara', function ($row) {
                    $payload = [
                        'id'  => $row['wawancara_id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $tokenWawancara = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    return ($row['wawancara_id']
                        ? '<i class="ti ti-cloud-down fa-2x lihatHasil text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Hasil" data-token="' . $tokenWawancara . '" data-url="/cetak-hasil-wawancara" style="cursor:pointer"></i>'
                        : '<i class="ti ti-minus text-danger fa-2x"></i>'
                    );
                })

                ->addColumn('analisis', function ($row) {
                    $payload = [
                        'id'  => $row['id'],
                        'exp' => time() + 3600  // optional, expired 1 jam
                    ];

                    $tokenResume = JWT::encode($payload, config('jwt.secret'), 'HS256');

                    return ($row['data_analisis']
                        ? '<button class="btn btn-primary btn-xs cetakAnalisis"  data-token="' . $tokenResume . '"><i class="ti ti-printer me-1"></i>Cetak</button>'
                        : ($row['lowongan_apply']['pelamar']['status'] == "PROGRESS"
                            ? '<button class="btn btn-warning btn-xs createAnalisis" data-token="' . $tokenResume . '" data-bs-toggle="tooltip" data-bs-placement="top" title="Create Analisis"><i class="ti ti-pencil me-1"></i>Create</button>'
                            : '')
                    );
                })

                ->rawColumns([
                    'tglpanggil' => 'tglpanggil',
                    'cfit'       => 'cfit',
                    'mbti'       => 'mbti',
                    'papi'       => 'papi',
                    'disc'       => 'disc',
                    'wawancara'  => 'wawancara',
                    'analisis'   => 'analisis',
                ])
                ->addIndexColumn()
                ->toJson();
        }

        return view('psikotest.hasil-psikotest', [
            'judul' => 'Hasil Psikotest'
        ]);
    }

    public function create_analisis(Request $request)
    {
        try {
            $token   = $request->query('token');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $pemanggilan = Pemanggilan::select('id', 'no_registrasi', 'tgl_panggil', 'cfit_id', 'mbti_id', 'papi_id', 'disc_id', 'wawancara_id')
                ->whereNull('deleted_at')
                ->whereNull('data_analisis')
                ->where('id', $id)
                ->whereHas('lowonganApply.pelamar', function ($q) {
                    $q->where('status', 'PROGRESS');
                })
                ->with([
                    'lowonganApply.lowongan:id,judul,pekerjaan_dilamar,penempatan,project_id',
                    'lowonganApply.lowongan.Project:id,name,inisial',
                    'lowonganApply.pelamar:id_pelamar,nama,tgllahir,jenis_kelamin,jenjang_pendidikan',
                    'cfit',
                    'mbti',
                    'papi',
                    'disc',
                    'wawancara',
                ])
                ->firstOrFail();

            $lowongan = $pemanggilan->lowonganApply->lowongan;
            $pelamar = $pemanggilan->lowonganApply->pelamar;

            $norma = PapiNorma::all();
            $hasilPapi = $pemanggilan->papi->analisis ?? null;
            $hasilPapi = $hasilPapi ? json_decode($hasilPapi, true) : [];

            $papiResult = $norma->groupBy('bidang')->map(function ($items) use ($hasilPapi) {
                $analisis = $items->map(function ($row) use ($hasilPapi) {
                    return $hasilPapi[$row->factor_code]['analisis'] ?? null;
                })->filter()->unique()->values()->toArray(); // hilangkan duplikat

                return $analisis;
            });

            if ($request->ajax()) {
                return response()->json([
                    'disc'        => $pemanggilan->disc->data_karakter ?? null,
                    'kepribadian' => $pemanggilan->disc->kepribadian ?? null,
                    'job'         => $pemanggilan->disc->job_match ?? null,
                    'papi'        => $papiResult,
                ]);
            }
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }

        return view('psikotest.analisis-create', [
            'judul' => 'Create Analisis'
        ], compact('pemanggilan', 'pelamar', 'lowongan', 'token'));
    }

    public function store_analisis(Request $request)
    {
        try {
            $token   = $request->input('idpanggil');
            $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
            $id      = $decoded->id;

            $data = $request->only([
                'tgltest',
                'keperluan',
                'nama',
                'pendidikan',
                'cfit',
                'disc',
                'papi',
                'interview',
                'petugas',
                'created',
            ]);

            $model = Pemanggilan::findOrFail($id);
            $model->update([
                'data_analisis' => $data
            ]);

            return response()->json([
                'message' => 'Data Berhasil Disimpan.'
            ], 200);
        } catch (ExpiredException $e) {
            return back()->withErrors(['token_expired' => 'Oops! Token sudah kadaluarsa.']);
        }
    }

    public function cetak_analisis(Request $request)
    {
        $token   = $request->input('token');
        $decoded = JWT::decode($token, new Key(config('jwt.secret'), 'HS256'));
        $id      = $decoded->id;

        $data = Pemanggilan::select('id', 'data_analisis', 'cfit_id', 'mbti_id', 'papi_id', 'disc_id', 'wawancara_id')
            ->where('id', $id)
            ->with([
                'cfit',
                'mbti',
                'papi',
                'disc',
                'wawancara',
            ])
            ->firstOrFail();

        $norma = PapiNorma::where('skor_min', 0)->get()->groupBy('factor_code');
        // dd($data->papi);
        $barcode = DNS2DFacade::getBarcodePNG('Hasil Psikotest ini buat oleh ' . ucwords(strtolower($data->data_analisis['petugas'])) . ' dan dicetak pada ' . Carbon::parse(date('Y-m-d H:i:s'))->translatedFormat('l, d F Y'), 'QRCODE');

        $pdf = Pdf::loadView('psikotest.pdf_hasil_psikotest', [
            'data'      => $data->data_analisis,
            'cfit'      => $data->cfit,
            'mbti'      => $data->mbti,
            'papi'      => $data->papi,
            'disc'      => $data->disc,
            'wawancara' => $data->wawancara,
            'barcode'   => $barcode,
            'norma'     => $norma,
        ]);

        $filename = 'HASIL PSIKOTEST - ' . $data->data_analisis['nama'] . '.pdf';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
