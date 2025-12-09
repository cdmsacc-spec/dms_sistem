<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArrayResource;
use App\Http\Resources\ListResource;
use App\Models\Dokumen;
use App\Models\JenisDokumen;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Str;

class DokumenController extends Controller
{
    public function show(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $status = $request->status ?? null;
            $kapal = $request->kapal ?? null;
            $perusahaan = $request->perusahaan ?? null;

            $user = User::where('auth_token', $token)->first();
            if (!$user) {
                return new ArrayResource(false, 'token anda tidak valid, silahkan melakukan login ulang', null);
            }

            $data  = Dokumen::select([
                'id',
                'id_kapal',
                'id_jenis_dokumen',
                'id_jenis_dokumen',
                'status',
                'tempat_penerbitan',
                'created_at'
            ])
                ->when(
                    $status,
                    fn($q) =>
                    $q->where('status', $status)
                )
                ->when(
                    $perusahaan,
                    fn($q) =>
                    $q->whereHas('kapal', fn($qq) => $qq->where('id_perusahaan', $perusahaan))
                )
                ->when(
                    $kapal,
                    fn($q) =>
                    $q->whereHas('kapal', fn($qq) => $qq->where('id', $kapal))
                )
                ->with(
                    [
                        'kapal' => fn($q) => $q->select('id', 'id_perusahaan', 'nama_kapal'),
                        'kapal.perusahaan' => fn($q) => $q->select('id', 'nama_perusahaan'),
                        'jenisDokumen' => fn($q) => $q->select('id', 'nama_jenis'),
                        'latestHistory' => function ($q) {
                            $q->select(
                                'history_dokumens.id',
                                'history_dokumens.id_dokumen',
                                'history_dokumens.nomor_dokumen',
                                'history_dokumens.tanggal_expired',
                                'history_dokumens.tanggal_terbit'
                            );
                        },
                    ]
                )->paginate(10);

            $data->getCollection()->transform(function ($item) {
                return [
                    'id'                => $item->id,
                    'kapal'             => $item->kapal->nama_kapal ?? null,
                    'perusahaan'        => $item->kapal->perusahaan->nama_perusahaan ?? null,
                    'jenis_dokumen'     => $item->jenisDokumen->nama_jenis ?? null,
                    'status'            => $item->status,
                    'tempat_penerbitan' => $item->tempat_penerbitan,
                    'created_at'        => $item->created_at,
                    'tanggal_expired'   => $item->latestHistory->tanggal_expired ?? null,
                    'tanggal_terbit'   => $item->latestHistory->tanggal_terbit ?? null,
                    'nomor_dokumen'     => $item->latestHistory->nomor_dokumen ?? null,
                ];
            });
            return new ListResource(true, 'list dokumen', $data);
        } catch (\Throwable $th) {
            return new ArrayResource(false, $th->getMessage(), null);
        }
    }

    public function detail(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $data = User::where('auth_token', $token)->first();

            if (!$data) {
                return new ArrayResource(false, 'token anda tidak valid, silahkan melakukan login ulang', null);
            }

            $id = $request->id;
            $data = Dokumen::with([
                'kapal' => fn($q) => $q->select('id', 'id_perusahaan', 'nama_kapal'),
                'kapal.perusahaan' => fn($q) => $q->select('id', 'nama_perusahaan'),
                'jenisDokumen' => fn($q) => $q->select('id', 'nama_jenis'),
                'author' => fn($q) => $q->select('id', 'name', 'email'),
                'latestHistory' => function ($q) {
                    $q->select(
                        'history_dokumens.id',
                        'history_dokumens.id_dokumen',
                        'history_dokumens.nomor_dokumen',
                        'history_dokumens.tanggal_terbit',
                        'history_dokumens.tanggal_expired',
                        'history_dokumens.file',
                    );
                },
                'historyDokumen' => function ($q) {
                    $q->select(
                        'history_dokumens.id_dokumen',
                        'history_dokumens.nomor_dokumen',
                        'history_dokumens.tanggal_terbit',
                        'history_dokumens.tanggal_expired',
                        'history_dokumens.file',
                        'history_dokumens.created_at'
                    )->orderBy('created_at', 'desc');
                },
                'reminderDokumen' => function ($q) {
                    $q->select(
                        'reminder_dokumens.id_dokumen',
                        'reminder_dokumens.reminder_hari',
                        'reminder_dokumens.reminder_jam',
                    )->orderBy('created_at', 'desc');
                },
                'toReminderDokumen' => function ($q) {
                    $q->select(
                        'to_reminder_dokumens.id_dokumen',
                        'to_reminder_dokumens.nama',
                        'to_reminder_dokumens.send_to',
                        'to_reminder_dokumens.type',
                    )->orderBy('created_at', 'desc');
                },
            ])->findOrFail($id);

            $data->historyDokumen->transform(function ($item) {
                $item->file = asset('storage/' . $item->file);
                return $item;
            });

            $response = [
                'id'                => $data->id,
                'kapal'             => $data->kapal->nama_kapal ?? null,
                'perusahaan'        => $data->kapal->perusahaan->nama_perusahaan ?? null,
                'jenis_dokumen'     => $data->jenisDokumen->nama_jenis ?? null,
                'status'            => $data->status,
                'tempat_penerbitan' => $data->tempat_penerbitan,
                'created_at'        => $data->created_at,
                'author' => $data->author,
                'tanggal_terbit'   => $data->latestHistory->tanggal_terbit ?? null,
                'tanggal_expired'   => $data->latestHistory->tanggal_expired ?? null,
                'nomor_dokumen'     => $data->latestHistory->nomor_dokumen ?? null,
                'file' => url('storage/' .  $data->latestHistory->file) ?? null,
                'history_dokumen'   => $data->historyDokumen,
                'reminder_dokumen'  => $data->reminderDokumen,
                'to_reminder_dokumen'  => $data->toReminderDokumen,

            ];

            return new ArrayResource(true, 'data dokumen', $response);
        } catch (\Throwable $th) {
            return new ArrayResource(false, $th, null);
        }
    }

    public function dashboard(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $data = User::where('auth_token', $token)->first();

            if (!$data) {
                return new ArrayResource(false, 'token anda tidak valid, silahkan melakukan login ulang', null);
            }
            $perusahaan    = $request->perusahaan ?? null;
            $kapal         = $request->kapal ?? null;
            $jenis         = $request->jenis ?? null;
            $dari_tanggal  = $request->dari_tanggal ?? null;
            $sampai_tanggal = $request->sampai_tanggal ?? null;

            $count = Dokumen::query()

                ->when($jenis, fn($q) => $q->where('id_jenis_dokumen', $jenis))
                ->when(
                    $perusahaan,
                    fn($q) =>
                    $q->whereHas('kapal', fn($qq) => $qq->where('id_perusahaan', $perusahaan))
                )
                ->when(
                    $kapal,
                    fn($q) =>
                    $q->whereHas('kapal', fn($qq) => $qq->where('id', $kapal))
                )
                ->when(
                    $dari_tanggal && $sampai_tanggal,
                    fn($q) =>
                    $q->whereBetween('created_at', [$dari_tanggal, $sampai_tanggal])
                );

            $count_dokumen = $count->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $count_jenis_dokumen_status = $count
                ->join('jenis_dokumens', 'jenis_dokumens.id', '=', 'dokumens.id_jenis_dokumen')
                ->selectRaw('
                    jenis_dokumens.nama_jenis as nama_jenis_dokumen,
                    dokumens.status,
                    COUNT(*) as total
                ')
                ->groupBy('dokumens.id_jenis_dokumen', 'jenis_dokumens.nama_jenis', 'dokumens.status')
                ->get();



            $response = [
                'count_dokumen' => [
                    'total_document'             => $count_dokumen->sum(),
                    'total_document_uptodate'    => $count_dokumen['uptodate']    ?? 0,
                    'total_document_expired'     => $count_dokumen['expired']     ?? 0,
                    'total_document_near_expiry' => $count_dokumen['near expiry'] ?? 0,
                ],
                'count_status_jenis_dokumen' => $count_jenis_dokumen_status,
            ];

            return new ArrayResource(true, 'data dashboard', $response);
        } catch (\Throwable $th) {
            return new ArrayResource(false, $th, null);
        }
    }
}
