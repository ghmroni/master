<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Email\EmailController;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class LoginController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.home');
        } else {
            return view('login.login');
        }
    }

    public function actionlogin(Request $request)
    {
        $data = [
            'email'    => strtolower($request->input('email')),
            'password' => $request->input('password'),
            'status'   => "ENABLE",
        ];

        if (Auth::attempt($data)) {
            return redirect()->route('dashboard.home');
        }

        $request->session()->flash('error', 'Oops! Email atau Password salah.');
        return redirect('login');
    }

    public function forgot()
    {
        return view('login.forgot');
    }

    public function send_forgot_password(Request $request)
    {
        $email = trim(strtolower($request->input('email')));
        $cek = User::whereNull('deleted_at')
            ->where([
                'email'  => $email,
                'status' => 'ENABLE',
            ])->first();

        if ($cek) {
            $token = Str::random(225);

            $createToken = User::where('id', $cek->id)
                ->update([
                    'token'         => $token,
                    'created_token' => now(),
                ]);

            $mailController = new EmailController;
            $kirim = $mailController->send_email_forgot([
                'nama'  => strtoupper($cek->name),
                'email' => $cek->email,
                'url'   => URL::to('reset-password/' . $token),
            ]);

            return redirect()->route('login')->with('message', 'Link Reset Password Berhasil dikirim ke email ' . $this->maskEmail($email));
        } else {
            return back()->withErrors(['email' => 'Oops! Alamat Email tidak ditemukan.']);
        }
    }

    function maskEmail($email)
    {
        [$name, $domain] = explode('@', $email);

        $first = substr($name, 0, 1);
        $last = substr($name, -2); // ambil 2 huruf terakhir
        $masked = str_repeat('*', max(strlen($name) - strlen($first) - strlen($last), 3));

        return $first . $masked . $last . '@' . $domain;
    }

    public function reset_password(Request $request, string $token)
    {
        $cek = User::where('token', $token)
            ->firstOrFail();
        if ($cek) {
            $expiresAt = Carbon::parse($cek->created_token)->addMinutes(30);
            if ($expiresAt->isPast()) {
                return redirect()->route('forgot')->withErrors(['error' => 'Oops! Link Reset Password sudah kadaluarsa.']);
            } else {
                return view('login.reset-password', [
                    'token_reset' => $token,
                    'email'       => $cek->email,
                    'mask'        => $this->maskEmail($cek->email)
                ]);
            }
        } else {
            return redirect()->route('password.notfound');
        }
    }

    public function send_reset_password(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'password' => [
                    'required',
                    'same:confirm-password',
                    Password::min(6)
                        ->letters()
                        ->numbers()
                ],
                'confirm-password' => 'required',
            ],
            [
                'password.required'         => 'Password Baru wajib diisi.',
                'password.confirmed'        => 'Konfirmasi password tidak sesuai',
                'password.min'              => 'Password Baru minimal 6 karakter.',
                'password.letters'          => 'Password Baru harus berisi Huruf.',
                'password.numbers'          => 'Password Baru harus berisi Angka.',
                'confirm-password.required' => 'Konfirmasi password wajib diisi.',
            ]
        );

        if ($validator->fails()) {
            return back()->withErrors(['error' => $validator->messages()->first()]);
        } else {
            $cek = User::where([
                'token' => $request->token_reset,
                'email' => $request->email,
            ])
                ->first();

            if ($cek) {
                $expiresAt = Carbon::parse($cek->created_token)->addMinutes(30);
                if ($expiresAt->isPast()) {
                    return redirect()->route('login')->withErrors(['error' => 'Oops! Link sudah kadaluarsa.']);
                }

                $update = User::where('id', $cek->id)
                    ->update([
                        'token'         => null,
                        'created_token' => null,
                        'password'      => Hash::make($request->password)
                    ]);

                return redirect()->route('login')->with('message', 'Password Berhasil Disimpan.');
            } else {
                return redirect()->route('password.notfound');
            }
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
