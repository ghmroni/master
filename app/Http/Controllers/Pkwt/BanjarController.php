<?php

namespace App\Http\Controllers\Pkwt;

use App\Http\Controllers\Controller;
use App\Models\Kontrak;
use App\Models\Pkwt;
use Illuminate\Http\Request;

class BanjarController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $idkontrak = $request->input('idkontrak');
        $kopsurat = $request->input('kopsurat');

        $pkwtId = Kontrak::findOrFail($idkontrak)->pkwt_id;

        # Jika Belum Pernah Cetak
        if (is_null($pkwtId)) {
            $proses = $this->proses_pkwt($request);
            return $this->cetak_pkwt($proses);
        } else {
            # Jika tidak ada perubahan
            if ($status == 'create') {
                $data = Pkwt::find($pkwtId);
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
        }
    }

    public function proses_pkwt(Request $request)
    {
        $status = $request->input('status');
        $idkontrak = $request->input('idkontrak');
        $kopsurat = $request->input('kopsurat');
        $alasan = $request->input('alasan');

        // $kontrak = Kontrak::where()

        dd($request->all());
    }
}
