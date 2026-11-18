<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Override;

class TemplateUpload implements FromView
{
    /**
     * @return \Illuminate\Support\Collection
     */

    use Exportable;

    protected $datas;
    protected $view;

    public function __construct($datas, $view)
    {
        $this->datas = $datas;
        $this->view = $view;
    }

    public function view(): View
    {
        return view($this->view, [
            'data' => $this->datas
        ]);
    }
}
