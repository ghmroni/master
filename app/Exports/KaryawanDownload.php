<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class KaryawanDownload implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */

    use Exportable;

    protected $datas;

    public function __construct(array $datas)
    {
        $this->datas = $datas;
    }

    public function view(): View
    {
        return view('karyawan.download-template', ['data' => $this->datas]);
    }
}
