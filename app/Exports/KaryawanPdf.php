<?php

namespace App\Exports;

use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;

class KaryawanPdf implements FromQuery
{
    /**
     * @return \Illuminate\Support\Collection
     */

    public function query()
    {
        return Karyawan::query()->where('project', 3);
    }
}
