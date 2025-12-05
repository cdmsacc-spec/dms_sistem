<?php

namespace App\Http\Controllers;

use App\Models\AlasanBerhenti;
use App\Models\Crew;
use App\Models\CrewKontrak;
use App\Models\CrewSignOff;
use App\Models\Lookup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;

class GenerateTemplateController extends Controller
{
    public function generateFormInterview(Request $request)
    {
        $record = Crew::findOrFail($request->id);
        $template = new TemplateProcessor(storage_path('app/public/templates/INTERVIEW FORM.docx'));

        $lookups = Lookup::where('type', 'interview')
            ->whereIn('code', [
                'crewing',
                'user(operations/technique)',
                'staff_rekrutmen',
                'manager_crew',
            ])
            ->orderBy('id')
            ->pluck('name', 'code');

        $template->setValue('crewing', $lookups['crewing'] ?? '');
        $template->setValue('user', $lookups['user(operations/technique)'] ?? '');
        $template->setValue('staff_rekrutmen', $lookups['staff_rekrutmen'] ?? '');
        $template->setValue('manager_crew', $lookups['manager_crew'] ?? '');



        $template->setValue('nama', $record->nama_crew);
        $template->setValue('ttl', $record->tempat_lahir . ' ' . Carbon::parse($record->tanggal_lahir)->format('d M Y'));
        $template->setValue('posisi', $record->posisi_dilamar);
        $template->setValue('kapal', $request->nama_kapal ?? '');
        $template->setValue('tanggal',  '');

        $fileName = 'Interview_Form_' . $record->nama_crew . '_' . Carbon::now() . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'word');
        $template->saveAs($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    public function generateFormSignon(Request $request)
    {
        $templates = $request->template_type;
        $kontrak = CrewKontrak::with([
            'wilayah:id,nama_wilayah,ttd_dibuat,ttd_diperiksa,ttd_diketahui_1,ttd_diketahui_2,ttd_disetujui_1,ttd_disetujui_2',
            'jabatan:id,nama_jabatan',
            'crew:id,nama_crew',
            'kapal:id,nama_kapal',
            'perusahaan:id,nama_perusahaan,kode_perusahaan,alamat,telp',
        ])->findOrFail($request->id, [
            'id',
            'id_crew',
            'id_wilayah',
            'id_jabatan',
            'id_kapal',
            'id_perusahaan',
            'nomor_dokumen',
            'gaji',
            'berangkat_dari',
            'start_date',
            'created_at'
        ]);

        // Load template
        $template = $templates == 2 ?  new TemplateProcessor(storage_path('app/public/templates/MUTASI SIGN ON - 2.docx'))
            : new TemplateProcessor(storage_path('app/public/templates/MUTASI SIGN ON.docx'));

        // === HEADER SECTION ===
        $bulanAngka = (int) $kontrak->created_at->format('m');
        $bulanRomawi = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ][$bulanAngka];
        if (preg_match('/^\d+$/', $kontrak->nomor_dokumen)) {
            $kontrak->nomor_dokumen = "{$kontrak->nomor_dokumen}/{$kontrak->perusahaan->kode_perusahaan}-CREW-SON/{$bulanRomawi}.{$kontrak->created_at->format('Y')}";
            $kontrak->save();
        }

        $template->setValue('no_document', $kontrak->nomor_dokumen);
        $template->setValue('perusahaan', $kontrak->perusahaan->nama_perusahaan);
        $template->setValue('code_perusahaan', $kontrak->perusahaan->kode_perusahaan);
        $template->setValue('bulan', $bulanRomawi);
        $template->setValue('tahun', $kontrak->created_at->format('Y'));
        $template->setValue('alamat_perusahaan', $kontrak->perusahaan->alamat);
        $template->setValue('tlp_perusahaan', $kontrak->perusahaan->telp);

        // === BODY SECTION ===
        $template->setValue('nama', $kontrak->crew->nama_crew);
        $template->setValue('jabatan', $kontrak->jabatan->nama_jabatan);
        $template->setValue('gaji', $kontrak->gaji);
        $template->setValue('status', 'Sign On / Crew PKL');
        $template->setValue('berangkat_dari', $kontrak->berangkat_dari);
        $template->setValue('wilayah', $kontrak->wilayah->nama_wilayah);
        $template->setValue('kapal', $kontrak->kapal->nama_kapal);
        $template->setValue('start_date', Carbon::parse($kontrak->start_date)->format('d M Y'));
        $template->setValue('dikeluarkan', Carbon::parse($kontrak->start_date)->copy()->subDay()->format('d M Y'));

        // === FOOTER SECTION ===

        $template->setValue('dibuat',     $kontrak->wilayah->ttd_dibuat ?? '-');
        $template->setValue('diperiksa',  $kontrak->wilayah->ttd_diperiksa ?? '-');
        $template->setValue('diketahui1', $kontrak->wilayah->ttd_diketahui_1 ?? '-');
        $template->setValue('diketahui2', $kontrak->wilayah->ttd_diketahui_2 ?? '-');
        $template->setValue('disetujui1', $kontrak->wilayah->ttd_disetujui_1 ?? '-');
        $template->setValue('disetujui2', $kontrak->wilayah->ttd_disetujui_2 ?? '-');
        $template->setValue('presiden_direktur', $templates == 2 ? $kontrak->wilayah->ttd_disetujui_2 ?? '-' : $kontrak->wilayah->ttd_disetujui_1 ?? '-');

        // === EXPORT FILE ===
        $date = now();
        $crewName = str_replace(' ', '_', strtolower($kontrak->crew->nama_crew));
        $fileName = "Sign_On_Form_{$crewName}_{$date}.docx";

        $tempFile = tempnam(sys_get_temp_dir(), 'word');
        $template->saveAs($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    public function generateFormPromosi(Request $request)
    {
        $kontrak = CrewKontrak::with([
            'wilayah:id,nama_wilayah,ttd_dibuat,ttd_diperiksa,ttd_diketahui_1,ttd_diketahui_2,ttd_disetujui_1,ttd_disetujui_2',
            'jabatan:id,nama_jabatan,devisi,golongan',
            'crew:id,nama_crew',
            'kapal:id,nama_kapal',
            'perusahaan:id,nama_perusahaan,kode_perusahaan,alamat,telp',
        ])->where('id_crew', $request->id);

        $last   = $kontrak->orderBy('created_at', 'desc')->first();        // terbaru
        $second = $kontrak->orderBy('created_at', 'desc')->skip(1)->first(); // terbaru ke-2

        $template = new TemplateProcessor(storage_path('app/public/templates/MUTASI PROMOSI.docx'));
        $bulanRomawi = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ][now()->month];

        if (preg_match('/^\d+$/', $last->nomor_dokumen)) {
            $last->nomor_dokumen =  $last->kategory == 'mutasi' ?
                "{$last->nomor_dokumen}/{$last->perusahaan->kode_perusahaan}-CREW-MTS/{$bulanRomawi}/{$last->created_at->format('Y')}"
                : "{$last->nomor_dokumen}/{$last->perusahaan->kode_perusahaan}-CREW-PRM/{$bulanRomawi}/{$last->created_at->format('Y')}";
            $last->save();
        }

        $template->setValues([
            'nomor_document'      => $last->nomor_dokumen,
            'perusahaan_new'      => $last->perusahaan->nama_perusahaan,
            'kode_perusahaan'     => $last->perusahaan->kode_perusahaan,
            'alamat_perusahaan'   => $last->perusahaan->alamat,
            'telepon_perusahaan'  => $last->perusahaan->telp,
            'bulan'               => $bulanRomawi,
            'tahun'               => now()->format('Y'),
            'kategory'            => Str::upper($last->kategory),
        ]);
        // ================= BODY =================
        $template->setValues([
            'nama_crew'               => $last->crew->nama_crew,
            'jabatan_old'             => $second->jabatan->devisi . ' / ' . $second->jabatan->golongan . ' / ' . $second->jabatan->nama_jabatan,
            'start_date_old'          => Carbon::parse($second->start_date)->format('d M Y'),
            'jabatan_new'             => $last->jabatan->devisi . ' / ' . $last->jabatan->golongan . '-' . $last->jabatan->nama_jabatan,
            'wilayah_operasional'     => $last->wilayah->nama_wilayah,
            'gaji_old'                => $second->gaji,
            'gaji_new'                => $last->gaji,
            'perusahaan_old'          =>  $second->perusahaan->nama_perusahaan,
            'start_date_new'          => Carbon::parse($last->start_date)->format('d M Y'),
            'tanggal'                 => now()->format('d M Y')
        ]);

        // ================= FOOTER =================

        $template->setValue('dibuat_oleh',    $last->wilayah->ttd_dibuat ?? '');
        $template->setValue('diketahui_oleh', $last->wilayah->ttd_diketahui_1 ?? '');
        $template->setValue('disetujui_oleh', $last->wilayah->ttd_disetujui_1 ?? '');


        // === EXPORT FILE ===
        $date = now();
        $crewName = str_replace(' ', '_', strtolower($last->crew->nama_crew));
        $fileName =  $last->kategory == 'mutasi' ? "Mutasi_Form_{$crewName}_{$date}.docx" : "Promosi_Form_{$crewName}_{$date}.docx";

        $tempFile = tempnam(sys_get_temp_dir(), 'word');
        $template->saveAs($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    public function generateFormSignoff(Request $request)
    {
        $kontrak = CrewKontrak::with([
            'wilayah:id,nama_wilayah',
            'jabatan:id,nama_jabatan,devisi,golongan',
            'crew:id,nama_crew',
            'kapal:id,nama_kapal',
            'perusahaan:id,nama_perusahaan,kode_perusahaan,alamat,telp'
        ])->where('id_crew', $request->id);
        $sign_off = CrewSignOff::with([
            'alasanBerhenti:id,nama_alasan',
        ])->where('id', $request->id_sign_off)->first();

        $first = (clone $kontrak)->orderBy('created_at', 'asc')->first();
        $last  = (clone $kontrak)->orderBy('created_at', 'desc')->first();

        if (!$last) {
            abort(404, 'Data kontrak tidak ditemukan untuk crew ini.');
        }
        $lookup = Lookup::where('code', 'sign_on')->first();
        $bulanRomawi = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ][Carbon::parse($sign_off->tanggal)->format('n')];

        $year = Carbon::parse($sign_off->tanggal)->format('Y');
        if (preg_match('/^\d+$/', $sign_off->nomor_dokumen)) {
            $sign_off->nomor_dokumen = "No.{$lookup->name}/MKL-CREW-SOFF/$bulanRomawi/$year";
            $sign_off->save();
        }
        $template = new TemplateProcessor(storage_path('app/public/templates/MUTASI SIGN OFF.docx'));

        $lookups = Lookup::where('type', 'sign_off')
            ->whereIn('code', [
                'crewing_manager',
                'direktur',
            ])
            ->orderBy('id')
            ->pluck('name', 'code');

        $template->setValues([
            'perusahaan' => $last->perusahaan->nama_perusahaan,
            'alamat_perusahaan' => $last->perusahaan->alamat,
            'telepon_perusahaan' => $last->perusahaan->telp,
            'nomor_dokumen' => $sign_off->nomor_dokumen,
            'nama' =>  $last->crew->nama_crew,
            'jabatan' =>  $last->jabatan->nama_jabatan,
            'kapal' =>  $last->kapal->nama_kapal,
            'periode' => Carbon::parse($first->start_date)->format('d M Y') . ' - ' . Carbon::parse($sign_off->tanggal)->format('d M Y'),
            'efektif_sejak' => Carbon::parse($sign_off->tanggal)->format('d M Y'),
            'alasan_berhenti' =>  $sign_off->alasanBerhenti->nama_alasan ?? '',
            'dikeluarkan' => 'Jakarta',
            'pada_tanggal' => Carbon::parse($sign_off->tanggal)->format('d M Y'),
            'crewing_manager' =>  $lookups['crewing_manager'] ?? '',
            'lokasi_penempatan' =>  $last->wilayah->nama_wilayah,
            'divisi' =>  $last->jabatan->devisi . ' - ' . $last->jabatan->nama_jabatan,
            'jabatan_awal' =>   $first->jabatan->nama_jabatan,
            'jabatan_akhir' =>   $last->jabatan->nama_jabatan,
            'direktur' =>   $lookups['direktur'] ?? '',
        ]);

        // === EXPORT FILE ===
        $date = now();
        $crewName = str_replace(' ', '_', strtolower($last->crew->nama_crew));
        $fileName = "Sign_Off_Form_{$crewName}_{$date}.docx";

        $tempFile = tempnam(sys_get_temp_dir(), 'word');
        $template->saveAs($tempFile);

        $lookup->name = $lookup->name + 1;
        $lookup->save();

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
