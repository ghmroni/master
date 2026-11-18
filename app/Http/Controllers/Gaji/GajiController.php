<?php

namespace App\Http\Controllers\Gaji;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class GajiController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('upload-gaji'), only: ['uploadgaji']),
            new Middleware(PermissionMiddleware::using('proses-gaji'), only: ['prosesgaji']),
            new Middleware(PermissionMiddleware::using('pengajuan-gaji'), only: ['pengajuan_gaji']),
            new Middleware(PermissionMiddleware::using('slip-gaji'), only: ['slipgaji']),
        ];
    }

    public function index(Request $request)
    {
        //
    }

    public function uploadgaji(Request $request)
    {
        return view('gaji.uploads-gaji', [
            'judul' => 'Uploads'
        ]);
    }

    public function prosesgaji(Request $request)
    {
        if ($request->ajax()) {
            return response()->json([]);
        }
        return view('gaji.prores-gaji', [
            'judul' => 'Proses Penggajian'
        ]);
    }

    public function pengajuan_gaji(Request $request)
    {
        if ($request->ajax()) {
            return response()->json([]);
        }
        return view('gaji.pengajuan-gaji', [
            'judul' => 'Pengajuan Gaji'
        ]);
    }

    public function slipgaji(Request $request)
    {
        if ($request->ajax()) {
            return response()->json([]);
        }
        return view('gaji.slip-gaji', [
            'judul' => 'Slip Gaji'
        ]);
    }
}
