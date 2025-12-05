<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArrayResource;
use App\Models\Jabatan;
use App\Models\JenisDokumen;
use App\Models\Kapal;
use App\Models\Perusahaan;
use App\Models\User;
use App\Models\WilayahOperasional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => ['required'],
                ],
                [
                    'email.required' => 'Email wajib diisi.',
                    'email.email' => 'Format email tidak valid.',
                    'password.required' => 'Password wajib diisi.',
                ]
            );

            if ($validator->fails()) {
                return new ArrayResource(false, $validator->messages()->all(), null);
            }

            $email = $request->email;
            $password = $request->password;

            $data = User::with('roles')->where('email', $email)->first();
            if ($data == null) {
                return new ArrayResource(false, 'email anda tidak valid', null);
            }
            if (!Hash::check($password, $data->password)) {
                return new ArrayResource(false, 'password anda tidak valid', null);
            }

            $data->auth_token =  Str::random(60);
            $data->save();

            $perusahaan = Perusahaan::select('id', 'nama_perusahaan')->get();
            $jenis_dokumen = JenisDokumen::select('id', 'nama_jenis')->get();
            $kapal = Kapal::select('id', 'nama_kapal')->get();
            $jabatan = Jabatan::select('id', 'nama_jabatan')->get();
            $wilayah = WilayahOperasional::select('id', 'nama_wilayah')->get();


            $response = [
                "id" => $data['id'],
                "name" => $data["name"],
                "email" => $data["email"],
                "email_verified_at" => $data["email_verified_at"],
                "avatar" => $data["avatar"] == null ?  url('storage/crew/avatar/default.jpg') : url('storage/' .  $data["avatar"]) ?? null,
                "auth_token" => $data["auth_token"],
                "roles" => $data["roles"]->pluck('name'),

                "perusahaan" => $perusahaan,
                "jenis_dokumen" => $jenis_dokumen,
                "kapal" => $kapal,
                "jabatan" => $jabatan,
                "wilayah_operasional" => $wilayah,
            ];
            return new ArrayResource(true, 'login berhasil', $response);
        } catch (\Throwable $th) {
            return new ArrayResource(false, $th->getMessage(), null);
        }
    }


    public function autologin(Request $request)
    {
        try {

            #validasi token
            $token = $request->bearerToken();

            $data = User::with('roles')->where('auth_token', $token)->first();
            if ($data == null) {
                return new ArrayResource(false, 'sesi login anda telah berakhir, silahkan login kembali', null);
            }

            $perusahaan = Perusahaan::select('id', 'nama_perusahaan')->get();
            $jenis_dokumen = JenisDokumen::select('id', 'nama_jenis')->get();
            $kapal = Kapal::select('id', 'nama_kapal')->get();
            $jabatan = Jabatan::select('id', 'nama_jabatan')->get();
            $wilayah = WilayahOperasional::select('id', 'nama_wilayah')->get();


            $response = [
                "id" => $data['id'],
                "name" => $data["name"],
                "email" => $data["email"],
                "email_verified_at" => $data["email_verified_at"],
                "avatar" => $data["avatar"] == null ?  url('storage/crew/avatar/default.jpg') : url('storage/' .  $data["avatar"]) ?? null,
                "auth_token" => $data["auth_token"],
                "roles" => $data["roles"]->pluck('name'),

                "perusahaan" => $perusahaan,
                "jenis_dokumen" => $jenis_dokumen,
                "kapal" => $kapal,
                "jabatan" => $jabatan,
                "wilayah_operasional" => $wilayah,
            ];
            return new ArrayResource(true, 'Login berhasil', $response);
        } catch (\Throwable $th) {
            return new ArrayResource(false, $th, null);
        }
    }
}
