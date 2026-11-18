<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = User::with('roles')->where('id', Auth::user()->id)->first()->toArray();

            return response()->json($user, 200);
        }
        return view('profile.profile', [
            'judul' => 'Profile'
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'  => 'required',
                'email' => [
                    'required',
                    Rule::unique('users', 'email')->ignore($request->id),
                ],
            ],
            [
                'name.required'  => 'Nama Lengkap masih kosong.',
                'email.required' => 'Alamat Email masih kosong.',
                'email.unique'   => 'Alamat Email sudah digunakan oleh pengguna lain.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $update = User::where('id', $request->id)
                ->update([
                    'name'  => strtoupper($request->input('name')),
                    'email' => strtolower($request->input('email')),
                ]);

            return response()->json([
                'message' => 'Data Berhasil Disimpan.'
            ], 200);
        }
    }

    public function store_password(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'oldPass'  => 'required',
                'password' => [
                    'required',
                    'same:confirmed',
                    Password::min(6)
                        ->letters()
                        ->numbers()
                ],
                'confirmed' => 'required',
            ],
            [
                'oldPass.required'   => 'Password Lama wajib diisin.',
                'password.required'  => 'Password Baru wajib diisin.',
                'password.confirmed' => 'Konfirmasi password tidak sesuai',
                'password.min'       => 'Password Baru minimal 6 karakter.',
                'password.letters'   => 'Password Baru harus berisi Huruf.',
                'password.numbers'   => 'Password Baru harus berisi Angka.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            $old = $request->oldPass;
            if (! Hash::check($old, Auth::user()->password)) {
                return response()->json([
                    'errors' => 'Password lama tidak sesuai.'
                ], 405);
            }

            $update = User::where('id', Auth::user()->id)
                ->update([
                    'password' => Hash::make($request->password)
                ]);

            return response()->json([
                'message' => 'Password Berhasil Dirubah.'
            ], 200);
        }
    }

    public function store_image(Request $request)
    {
        $rules = array(
            'foto' => 'mimes:jpeg,png,jpg|max:5000',
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->messages()->first()
            ], 401);
        } else {
            $id = Auth::user()->id;
            if ($request->hasFile('foto')) {
                $extension = $request->file('foto')->getClientOriginalExtension();
                $filename = str_replace(' ', '-', strtolower(Auth::user()->name)) . '-' . $id . '.' . $extension;

                Storage::disk('public')->putFileAs('hris/profile/', $request->file('foto'), $filename);

                $update = User::where('id', $id)
                    ->update([
                        'image' => $filename
                    ]);

                return response()->json([
                    'message' => 'Photo Profile Berhasil Diperbaharui',
                ], 200);
            }
        }
    }
}
