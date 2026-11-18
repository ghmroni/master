<?php

namespace App\Http\Controllers\Notifikasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Route;

class NotifikasiController extends Controller
{
    public function asRead($id)
    {
        $notif = DatabaseNotification::findOrFail($id);
        $notif->update(['read_at' => now()]);

        $url = $notif->data[0]['url'] ?? null;

        if ($url) {
            # cek apakah $url adalah nama route atau path
            if (Route::has($url)) {
                # kalau nama route valid
                return redirect()->route($url);
            } else {
                # kalau berupa path/url langsung
                return redirect()->to($url);
            }
        }

        # kalau tidak ada url, tetap di halaman (misalnya reload dropdown)
        if (request()->ajax()) {
            return response()->json([
                'status' => 'ok',
                'id' => $id,
            ]);
        }

        return redirect()->back();
    }

    public function markAsRead(Request $request)
    {
        $notif = DatabaseNotification::findOrFail($request->id);
        $notif->update(['read_at' => now()]);
        $count = count(auth()->user()->unreadNotifications);
        return response()->json(['status' => 'ok', 'jumlah' => $count]);
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        // reload user agar relasi fresh
        $user = auth()->user()->fresh();

        $count = $user->unreadNotifications()->count();

        return response()->json(['status' => 'ok', 'jumlah' => $count]);
    }
}
