<?php

namespace App\Http\Controllers\Pkwt;

use App\Http\Controllers\Controller;
use App\Models\Kontrak;
use App\Models\Pkwt;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Request;
use Milon\Barcode\Facades\DNS2DFacade;

class AutoController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $idkontrak = $request->input('idkontrak');
        $kopsurat = $request->input('kopsurat');

        $kontrak = Kontrak::findOrFail($idkontrak);

        # Jika Sudah pernah cetak
        if (!is_null($kontrak->pkwt_id)) {
            # Jika tidak ada perubahan
            if ($status == 'create') {
                $data = Pkwt::find($kontrak->pkwt_id);
                if ($data) {
                    ## jika ada data di tabel pkwt
                    $data->kopsurat = $kopsurat;
                    return $this->cetak_pkwt($data);
                } else {
                    ## jika di tabel pkwt kosong
                    $proses = $this->proses_pkwt($request);
                    return $this->cetak_pkwt($proses);
                }
            } else {
                # jika ada perubahan
                $proses = $this->proses_pkwt($request);
                return $this->cetak_pkwt($proses);
            }
        } else {
            # Jika Null
            $proses = $this->proses_pkwt($request);
            return $this->cetak_pkwt($proses);
        }
    }

    public function proses_pkwt(Request $request)
    {
        $status = $request->input('status');
        $idkontrak = $request->input('idkontrak');
        $kopsurat = $request->input('kopsurat');
        $alasan = $request->input('alasan');

        $kontrak = Kontrak::with([
            'statuskontrak:id,name',
            'karyawan:id,nip,nama,tempat_lahir,tanggal_lahir,alamat_domisili,handphone',
        ])
            ->find($idkontrak);


        # Cek File di Views
        $path = resource_path('views/pkwt/auto');
        # Cek File dan folder benar2 ada
        if (!File::exists($path) || !is_dir($path)) {
            abort(404, 'Direktori tidak ditemukan.');
        }

        $files = File::files($path);

        $filtered = collect($files)
            ->map(fn($file) => $file->getFilename())
            ->filter(fn($name) => str_contains($name, strtolower($kontrak->statuskontrak->name)))
            ->sortByDesc(function ($name) {
                # ambil angka setelah "rev"
                preg_match('/rev(\d+)/', $name, $matches);
                if (!empty($matches[1])) {
                    return (int) $matches[1]; // urutkan berdasarkan angka revisi
                }
                return $name; // fallback: urutkan alfabetis kalau tidak ada angka
            })
            ->values();

        $latestFile = $filtered->first(); // ambil file terbaru

        if (!$latestFile) {
            abort(404, 'Template PKWT tidak ditemukan.');
        }

        $namaFile = str_replace('.blade.php', '',  $latestFile);

        $data = array(
            'kontrak_id' => $idkontrak,
            'nomor'      => $kontrak->nomor_kontrak,
            'nip'        => $kontrak->karyawan->nip,
            'nama'       => $kontrak->karyawan->nama,
            'ttl'        => $kontrak->karyawan->tempat_lahir . ', ' . Carbon::parse($kontrak->karyawan->tanggal_lahir)->translatedFormat('d F Y'),
            'alamat'     => $kontrak->karyawan->alamat_domisili,
            'hp'         => $kontrak->karyawan->handphone,
            'template'   => $namaFile,
            'alasan'     => $alasan,
            'barcode'    => implode(',', array(
                $kontrak->statuskontrak->name,
                'Nomor:' . $kontrak->nomor_kontrak,
                'Template:' . $namaFile,
                'User:' . auth()->user()->name,
                'Tanggal Cetak:' . date('Y-m-d H:i:s'),
                'Status:' . strtoupper($status),
            )),
        );

        ## Jika Status Update
        if ($status != "create") {
            $pkwt = Pkwt::find($kontrak->pkwt_id);
            $pkwt->update([
                'status' => 'UPDATE'
            ]);
        }

        ## Simpan dan ambil idpkwt untuk update di kontrak
        $idpkwt = Pkwt::create($data)->id;
        ## Update Kontrak
        $updateKontrak = Kontrak::find($idkontrak);
        $updateKontrak->update([
            'pkwt_id' => $idpkwt
        ]);

        $dataPkwt           = Pkwt::find($idpkwt);
        $dataPkwt->kopsurat = $kopsurat;

        return $dataPkwt;
    }

    public function cetak_pkwt($datas)
    {
        $data = $datas;
        $kopsurat = $datas->kopsurat;
        $barcode = DNS2DFacade::getBarcodePNG($datas->barcode, 'QRCODE');;

        $margin = [
            'top'    => $kopsurat === 'kop' ? '10mm' : '35mm',
            'right'  => '20mm',
            'bottom' => $kopsurat === 'kop' ? '10mm' : '35mm',
            'left'   => '20mm'
        ];

        // $pdf = Pdf::loadView('pkwt.auto.' . $datas->template, compact('data', 'kopsurat', 'margin', 'barcode', 'cc'))
        //     ->setPaper('A4', 'portrait');
        // return $pdf->stream($datas->nama . '.pdf');

        $cetak = array('Untuk Arsip Perusahaan', 'Untuk Tenaga Kerja');

        $html = '';

        foreach ($cetak as $cc) {
            $html .= view('pkwt.auto.' . $datas->template, compact('data', 'kopsurat', 'margin', 'barcode', 'cc'))->render();
            // $html .= '<div style="page-break-after: always;"></div>';
        }

        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');
        return $pdf->stream($datas->nama . '.pdf');
    }
}
