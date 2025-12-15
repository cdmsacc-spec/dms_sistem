<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArrayResource;
use App\Http\Resources\ListResource;
use App\Models\Jabatan;
use App\Models\JenisDokumen;
use App\Models\Kapal;
use App\Models\Notification;
use App\Models\Perusahaan;
use App\Models\User;
use App\Models\WilayahOperasional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $fcmToken = $request->fcm_token;

            $data = User::with('roles')->where('email', $email)->first();
            $roles = $data->roles->pluck('name')->toArray();

            $isDokumenRole = collect($roles)->intersect([
                'staff_dokumen',
                'manager_dokumen',
                'operation_dokumen',
                'super_admin',
            ])->isNotEmpty();

            $isCrewRole = collect($roles)->intersect([
                'staff_crew',
                'manager_crew',
                'super_admin',
            ])->isNotEmpty();

            if ($data == null) {
                return new ArrayResource(false, 'email anda tidak valid', null);
            }
            if (!Hash::check($password, $data->password)) {
                return new ArrayResource(false, 'password anda tidak valid', null);
            }

            $data->auth_token =  Str::random(60);
            $data->fcm_token = $fcmToken ?? null;
            $data->save();

            $perusahaan = $isDokumenRole ? Perusahaan::query()
                ->leftJoin('kapals', 'kapals.id_perusahaan', '=', 'perusahaans.id')
                ->leftJoin('dokumens', 'dokumens.id_kapal', '=', 'kapals.id')
                ->select(
                    'perusahaans.id',
                    'perusahaans.nama_perusahaan',
                    'perusahaans.kode_perusahaan',
                    DB::raw('COUNT(DISTINCT kapals.id) as total_kapal'),
                    DB::raw('COUNT(dokumens.id) as total_dokumen')
                )
                ->groupBy(
                    'perusahaans.id',
                    'perusahaans.nama_perusahaan',
                    'perusahaans.kode_perusahaan'
                )
                ->get() : Perusahaan::query()
                ->leftJoin('kapals', 'kapals.id_perusahaan', '=', 'perusahaans.id')
                ->leftJoin('crew_kontraks', 'crew_kontraks.id_kapal', '=', 'kapals.id')
                ->select(
                    'perusahaans.id',
                    'perusahaans.nama_perusahaan',
                    'perusahaans.kode_perusahaan',
                    DB::raw('COUNT(DISTINCT kapals.id) as total_kapal'),
                    DB::raw("COUNT(CASE WHEN crew_kontraks.status_kontrak = 'active' THEN 1 END) as total_active")
                )
                ->groupBy(
                    'perusahaans.id',
                    'perusahaans.nama_perusahaan',
                    'perusahaans.kode_perusahaan'
                )
                ->get();
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
                "fcm_token" => $data["fcm_token"],

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
                "fcm_token" => $data["fcm_token"],

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

    public function showNotification(Request $request)
    {
        try {
            $token = $request->bearerToken();

            $user = User::where('auth_token', $token)->first();
            if (!$user) {
                return new ArrayResource(false, 'token anda tidak valid, silahkan melakukan login ulang', null);
            }

            $data = Notification::where('notifiable_id', $user->id)->latest()->paginate(10);
            $data->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->data['title'] ?? null,
                    'body' => $item->data['body'] ?? null,
                    'read_at' => $item->read_at ?? null,
                    'url' => $item->data['actions'][0]['url'] ?? null,
                    'created_at' => $item->created_at,

                ];
            });
            return new ListResource(true, 'list notification', $data);
        } catch (\Throwable $th) {
            return new ArrayResource(false, $th->getMessage(), null);
        }
    }

    public function showBadgeNotification(Request $request)
    {
        try {
            $token = $request->bearerToken();

            $user = User::where('auth_token', $token)->first();
            if (!$user) {
                return new ArrayResource(false, 'token anda tidak valid, silahkan melakukan login ulang', null);
            }

            $data = Notification::where('notifiable_id', $user->id)->whereNull('read_at')->count();

            return new ArrayResource(true, 'badge notification', $data);
        } catch (\Throwable $th) {
            return new ArrayResource(false, $th->getMessage(), null);
        }
    }
}
