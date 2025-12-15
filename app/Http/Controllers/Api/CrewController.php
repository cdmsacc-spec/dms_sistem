<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArrayResource;
use App\Http\Resources\ListResource;
use App\Models\Crew;
use App\Models\CrewKontrak;
use App\Models\CrewSignOff;
use App\Models\Dokumen;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Str;

class CrewController extends Controller
{
    public function show(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $status = $request->status ?? null;
            $search  = $request->search ?? null;

            $user = User::where('auth_token', $token)->first();
            if (!$user) {
                return new ArrayResource(false, 'token anda tidak valid, silahkan melakukan login ulang', null);
            }

            $data  = Crew::select([
                'id',
                'nama_crew',
                'jenis_kelamin',
                'no_hp',
                'email',
                'status_identitas',
                'status',
                'avatar'
            ])
                ->when($search, function ($q) use ($search) {
                    $q->where('nama_crew', 'ILIKE', "%{$search}%");
                })

                ->when(
                    $status,
                    fn($q) =>
                    $q->where('status', $status)
                )->latest()->paginate(10);

            $data->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_crew' => $item->nama_crew,
                    'jenis_kelamin' => $item->jenis_kelamin,
                    'no_hp' => $item->no_hp,
                    'email' => $item->email,
                    'status_identitas' => $item->status_identitas,
                    'status' => $item->status,
                    'avatar' => $item->avatar == null ?  url('storage/crew/avatar/default.jpg')  : asset('storage/' . $item->avatar),
                ];
            });

            return new ListResource(true, 'list crew', $data);
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
            $data = Crew::with([
                'dokumen' => fn($q) => $q->select(
                    'id',
                    'id_crew',
                    'kategory',
                    'jenis_dokumen',
                    'nomor_dokumen',
                    'tempat_dikeluarkan',
                    'tanggal_terbit',
                    'tanggal_expired',
                    'status',
                    'file',
                ),
                'sertifikat' => fn($q) => $q->select(
                    'id',
                    'id_crew',
                    'kategory',
                    'nama_sertifikat',
                    'nomor_sertifikat',
                    'tempat_dikeluarkan',
                    'tanggal_terbit',
                    'tanggal_expired',
                    'status',
                    'file',
                ),
                'experience' => fn($q) => $q->select(
                    'id',
                    'id_crew',
                    'nama_kapal',
                    'tipe_kapal',
                    'nama_perusahaan',
                    'posisi',
                    'gt_kw',
                    'bendera',
                    'masa_kerja',
                    'periode_awal',
                    'periode_akhir',
                ),
                'nok' => fn($q) => $q->select(
                    'id',
                    'id_crew',
                    'nama',
                    'hubungan',
                    'alamat',
                    'no_hp',
                ),
                'kontrak' => fn($q) => $q
                    ->select(
                        'id',
                        'id_crew',
                        'id_perusahaan',
                        'id_jabatan',
                        'id_wilayah',
                        'id_kapal',
                        'kategory',
                        'nomor_dokumen',
                        'berangkat_dari',
                        'gaji',
                        'start_date',
                        'end_date',
                        'kontrak_lanjutan',
                        'status_kontrak',
                        'file',
                    )
                    ->where('status_kontrak', 'active')
                    ->with([
                        'perusahaan:id,nama_perusahaan',
                        'jabatan:id,nama_jabatan',
                        'wilayah:id,nama_wilayah',
                        'kapal:id,nama_kapal'
                    ]),


            ])->findOrFail($id);

            $data->avatar =  $data->avatar == null ?  url('storage/crew/avatar/default.jpg')  : asset('storage/' . $data->avatar);

            $data->dokumen->transform(function ($item) {
                if (!$item) return $item;

                $item->file = optional($item)->file ? asset('storage/' . $item->file) : null;
                return $item;
            });

            $data->sertifikat->transform(function ($item) {
                if (!$item) return $item;

                $item->file = optional($item)->file ? asset('storage/' . $item->file) : null;
                return $item;
            });


            $data->kontrak->transform(function ($item) {
                return [
                    "id" => $item["id"],
                    "kategory" => $item["kategory"],
                    "nomor_dokumen" => $item["nomor_dokumen"],
                    "berangkat_dari" => $item["berangkat_dari"],
                    "gaji" => $item["gaji"],
                    "start_date" => $item["start_date"],
                    "end_date" => $item["end_date"],
                    "kontrak_lanjutan" => $item["kontrak_lanjutan"],
                    "status_kontrak" => $item["status_kontrak"],
                    "file" =>  $item->file ? asset('storage/' . $item->file) : null,
                    "perusahaan" => $item["perusahaan"] == null ? null : $item["perusahaan"]["nama_perusahaan"],
                    "jabatan" => $item["jabatan"] == null ? null : $item["jabatan"]["nama_jabatan"],
                    "wilayah" => $item["wilayah"] == null ? null : $item["wilayah"]["nama_wilayah"],
                    "kapal" => $item["kapal"] == null ? null : $item["kapal"]["nama_kapal"],
                ];
            });

            $response = [
                "crew" => $data,
            ];
            return new ArrayResource(true, 'Data crew', $response);
        } catch (\Throwable $th) {
            return new ArrayResource(false, $th->getMessage(), null);
        }
    }

    public function dashboard(Request $request)
    {

        try {
            $periode  = $request->peridoe ?? Carbon::now();
            $carbonDate = $periode instanceof Carbon ? $periode : Carbon::parse($periode);


            //==>jumlah crew
            $counts = Crew::query()
                ->whereNot('status', 'rejected')
                ->select('jenis_kelamin', DB::raw('COUNT(*) as total'))
                ->whereIn('jenis_kelamin', ['Laki Laki', 'Perempuan'])
                ->groupBy('jenis_kelamin')
                ->pluck('total', 'jenis_kelamin');

            $lakiLaki  = $counts['Laki Laki'] ?? 0;
            $perempuan = $counts['Perempuan'] ?? 0;
            $total     = $lakiLaki + $perempuan;


            $totalStatusActive =  Crew::where('status', 'active')->count();
                        $totalStatusDraft =  Crew::where('status', 'draft')->count();

            $totalStatusInactive =  Crew::where('status', 'inactive')->count();
            $totalStatusStandby =  Crew::where('status', 'standby')->count();
            $totalStatusReadyInterview =  Crew::where('status', 'ready for interview')->count();
            $totalStatusRejected =  Crew::where('status', 'rejected')->count();


            //==>activity berjalanF

            $crewMutasiPerMonth = CrewKontrak::select(
                DB::raw("EXTRACT(MONTH FROM start_date) as bulan"),
                DB::raw("COUNT(*) as total")
            )
                ->whereYear('start_date', $carbonDate->year)
                ->where('kategory', 'promosi')
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->pluck('total', 'bulan')
                ->toArray();

            $crewSignonPerMonth = CrewKontrak::select(
                DB::raw("EXTRACT(MONTH FROM start_date) as bulan"),
                DB::raw("COUNT(*) as total")
            )
                ->whereYear('start_date', $carbonDate->year)
                ->where('kategory', 'signon')
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->pluck('total', 'bulan')
                ->toArray();

            $crewSignoffPerMonth = CrewSignOff::select(
                DB::raw("EXTRACT(MONTH FROM tanggal) as bulan"),
                DB::raw("COUNT(*) as total")
            )
                ->whereYear('tanggal', $carbonDate->year)
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->pluck('total', 'bulan')
                ->toArray();

            $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            $seriesMutasi = [];
            foreach (range(1, 12) as $bulan) {
                $seriesMutasi[] = ['total' => $crewMutasiPerMonth[$bulan] ?? 0, "bulan" => $labels[$bulan - 1]];
            }

            $seriesDataSignon = [];
            foreach (range(1, 12) as $bulan) {
                $seriesDataSignon[] = ['total' => $crewSignonPerMonth[$bulan] ?? 0, "bulan" => $labels[$bulan - 1]];
            }

            $seriesDataSignoff = [];
            foreach (range(1, 12) as $bulan) {
                $seriesDataSignoff[] = ['total' => $crewSignoffPerMonth[$bulan] ?? 0, "bulan" => $labels[$bulan - 1]];
            }

            $response = [
                "crew_count" => [
                    "laki_laki" => $lakiLaki,
                    "perempuan" => $perempuan,
                    "total" => $total
                ],
                "crew_status_count" => [
                    "active" => $totalStatusActive,
                    "inactive" => $totalStatusInactive,
                    "draft" => $totalStatusDraft,
                    "standby" => $totalStatusStandby,
                    "ready_interview" => $totalStatusReadyInterview,
                    "rejected" => $totalStatusRejected,
                ],

                "crew_activity" => [
                    "sign_on" => $seriesDataSignon,
                    "sign_off" => $seriesDataSignoff,
                    "mutasi_promosi" => $seriesMutasi,
                ],
            ];

            return new ArrayResource(true, 'data dashboard', $response);
        } catch (\Throwable $th) {
            return new ArrayResource(false, $th->getMessage(), null);
        }
    }

    public function crewHistoryKontrak(Request $request)
    {
        try {
            $idCrew = $request->id_crew;

            $data = CrewKontrak::with([
                'crew:id,nama_crew',
                'perusahaan:id,nama_perusahaan',
                'jabatan:id,nama_jabatan',
                'wilayah:id,nama_wilayah',
                'kapal:id,nama_kapal'
            ])->where('id_crew', $idCrew)
                ->orderBy('start_date', 'desc')
                ->paginate(10);

            $data->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_crew' => $item->crew->nama_crew,
                    'perusahaan' => $item->perusahaan->nama_perusahaan,
                    'jabatan' => $item->jabatan->nama_jabatan,
                    'kapal' => $item->Kapal->nama_kapal,
                    'wilayah' => $item->wilayah->nama_wilayah,
                    'nomor_dokumen' => $item->nomor_dokumen,
                    'start_date' => $item->start_date,
                    'end_date' => $item->end_date,
                    'kategory' => $item->kategory,
                    'status_kontrak' => $item->status_kontrak,
                    'file' => $item->file == null ? null : asset('storage/' . $item->file),
                ];
            });
            return new ListResource(true, 'list history kontrak', $data);
        } catch (\Throwable $th) {
            return new ArrayResource(false, $th->getMessage(), null);
        }
    }

    public function crewHistorySignOff(Request $request)
    {
        try {
            $idCrew = $request->id_crew;

            $data = CrewSignOff::with([
                'crew:id,nama_crew',
                'alasanBerhenti:id,nama_alasan',
            ])->where('id_crew', $idCrew)
                ->orderBy('start_date', 'desc')
                ->paginate(10);

            $data->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_crew' => $item->crew->nama_crew,
                    'nomor_dokumen' => $item->nomor_dokumen,
                    'alasan_berhenti' => $item->alasanBerhenti->nama_alasan,
                    'tanggal' => $item->tanggal,
                    'keterangan' => $item->keterangan,
                    'file' => $item->file == null ? null : asset('storage/' . $item->file),
                ];
            });
            return new ListResource(true, 'list history signoff', $data);
        } catch (\Throwable $th) {
            return new ArrayResource(false, $th->getMessage(), null);
        }
    }
}
