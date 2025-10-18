<?php

namespace App\Http\Controllers;

use App\Models\CrewApplicants;
use App\Models\CrewPkl;
use App\Models\Jabatan;
use App\Models\Lookup;
use App\Models\NamaKapal;
use App\Models\WilayahOperasional;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;

class GenerateFormController extends Controller
{

    public function generateFormInterview(Request $request)
    {

        $record = CrewApplicants::findOrFail($request->id);
        $template = new TemplateProcessor(storage_path('app/public/templates/INTERVIEW FORM.docx'));

        $interview1 = Lookup::where('kategori', 'Interview')
            ->where('code', 'Disetujui 1')
            ->value('value');

        $interview2 = Lookup::where('kategori', 'Interview')
            ->where('code', 'Disetujui 2')
            ->value('value');

        $interview3 = Lookup::where('kategori', 'Interview')
            ->where('code', 'Disetujui 3')
            ->value('value');

        $template->setValue('interview1', $interview1 ?? '');
        $template->setValue('interview2', $interview2 ?? '');
        $template->setValue('interview3', $interview3 ?? '');

        $template->setValue('nama', $record->nama_crew);
        $template->setValue('ttl', $record->tempat_lahir . ' ' . \Carbon\Carbon::parse($record->tanggal_lahir)->format('d M Y'));
        $template->setValue('posisi', $record->posisi_dilamar);
        $template->setValue('kapal', $request->nama_kapal ?? '');

        $fileName = 'Interview_Form_' . $record->nama_crew . ' ' . $request->tanggal_interview . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'word');
        $template->saveAs($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    public function generateFormSignon(Request $request)
    {
        // Ambil data PKL dengan relasi (select kolom yang dibutuhkan saja)
        $pkl = CrewPkl::with([
            'wilayah:id,nama_wilayah',
            'jabatan:id,nama_jabatan',
            'crew:id,nama_crew',
            'kapal:id,nama_kapal',
            'perusahaan:id,nama_perusahaan,kode_perusahaan,alamat,telepon',
        ])->findOrFail($request->id, [
            'id',
            'crew_id',
            'wilayah_id',
            'jabatan_id',
            'kapal_id',
            'perusahaan_id',
            'nomor_document',
            'gaji',
            'berangkat_dari',
            'start_date',
            'created_at'
        ]);

        // Load template
        $template = new TemplateProcessor(storage_path('app/public/templates/MUTASI SIGN ON.docx'));

        // === HEADER SECTION ===
        $bulanAngka = (int) $pkl->created_at->format('m');
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
        if (preg_match('/^\d+$/', $pkl->nomor_document)) {
            $pkl->nomor_document = "{$pkl->nomor_document}/{$pkl->perusahaan->kode_perusahaan}-CRW-MTS/{$bulanRomawi}/{$pkl->created_at->format('Y')}";
            $pkl->save();
        }

        $template->setValue('no_document', $pkl->nomor_document);
        $template->setValue('perusahaan', $pkl->perusahaan->nama_perusahaan);
        $template->setValue('code_perusahaan', $pkl->perusahaan->kode_perusahaan);
        $template->setValue('bulan', $bulanRomawi);
        $template->setValue('tahun', $pkl->created_at->format('Y'));
        $template->setValue('alamat_perusahaan', $pkl->perusahaan->alamat);
        $template->setValue('tlp_perusahaan', $pkl->perusahaan->telepon);

        // === BODY SECTION ===
        $template->setValue('nama', $pkl->crew->nama_crew);
        $template->setValue('jabatan', $pkl->jabatan->nama_jabatan);
        $template->setValue('gaji', $pkl->gaji);
        $template->setValue('status', 'Sign On / PKL');
        $template->setValue('berangkat_dari', $pkl->berangkat_dari);
        $template->setValue('wilayah', $pkl->wilayah->nama_wilayah);
        $template->setValue('kapal', $pkl->kapal->nama_kapal);
        $template->setValue('start_date', \Carbon\Carbon::parse($pkl->start_date)->format('d M Y'));

        // === FOOTER SECTION ===
        $lookups = Lookup::where('kategori', 'Sign On')
            ->whereIn('code', ['Dibuat Oleh', 'Diperiksa Oleh', 'Diketahui Oleh', 'Disetujui Oleh'])
            ->orderBy('id')
            ->get()
            ->groupBy('code');

        $template->setValue('dibuat', $lookups['Dibuat Oleh'][0]->value ?? '');
        $template->setValue('diperiksa', $lookups['Diperiksa Oleh'][0]->value ?? '');
        $template->setValue('diketahui1', $lookups['Diketahui Oleh'][0]->value ?? '');
        $template->setValue('diketahui2', $lookups['Diketahui Oleh'][1]->value ?? '');
        $template->setValue('disetujui1', $lookups['Disetujui Oleh'][0]->value ?? '');
        $template->setValue('disetujui2', $lookups['Disetujui Oleh'][1]->value ?? '');

        // === EXPORT FILE ===
        $date = now()->format('d-m-Y');
        $crewName = str_replace(' ', '_', strtolower($pkl->crew->nama_crew));
        $fileName = "Sign_On_Form_{$crewName}_{$date}.docx";

        $tempFile = tempnam(sys_get_temp_dir(), 'word');
        $template->saveAs($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    public function generateFormSignoff(Request $request)
    {
        try {
            // Ambil PKL paling awal & paling akhir untuk crew
            $pklQuery = CrewPkl::with([
                'wilayah:id,nama_wilayah',
                'jabatan:id,nama_jabatan,devisi,golongan',
                'crew:id,nama_crew',
                'kapal:id,nama_kapal',
                'perusahaan:id,nama_perusahaan,kode_perusahaan,alamat,telepon'
            ])->where('crew_id', $request->id);

            $firstPkl = (clone $pklQuery)->orderBy('created_at', 'asc')->first();
            $lastPkl  = (clone $pklQuery)->orderBy('created_at', 'desc')->first();


            if (!$lastPkl) {
                abort(404, 'Data PKL tidak ditemukan untuk crew ini.');
            }

            $template = new TemplateProcessor(storage_path('app/public/templates/MUTASI SIGN OFF.docx'));

            // ================= HEADER =================
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
            ][Carbon::parse($request->tanggal_signoff)->format('n')];
            $template->setValues([
                'nomor_document'      => $lastPkl->nomor_document,
                'perusahaan'          => $lastPkl->perusahaan->nama_perusahaan,
                'kode_perusahaan'     => $lastPkl->perusahaan->kode_perusahaan,
                'alamat_perusahaan'   => $lastPkl->perusahaan->alamat,
                'telepon_perusahaan'  => $lastPkl->perusahaan->telepon,
                'bulan'               => $bulanRomawi,
                'tahun'               => Carbon::parse($request->tanggal_signoff)->format('Y'),
            ]);

            // ================= BODY =================
            $template->setValues([
                'nama'             => $lastPkl->crew->nama_crew,
                'jabatan'          =>  $lastPkl->jabatan->devisi . ' / ' . $lastPkl->jabatan->golongan . '-' . $lastPkl->jabatan->nama_jabatan,
                'status'           => 'Sign Off',
                'wilayah_operasional' => $lastPkl->wilayah->nama_wilayah,
                'kapal'            => $lastPkl->kapal->nama_kapal,
                'start_date'       => \Carbon\Carbon::parse($lastPkl->start_date)->format('d M Y'),
                'end_date'         => \Carbon\Carbon::parse($lastPkl->end_date)->format('d M Y'),
                'tanggal'          => Carbon::parse($request->tanggal_signoff)->format('d M Y'),
                'alasan_berhenti'  => $request->alasan_berhenti,
                'jabatan_awal'     => optional($firstPkl?->jabatan)->nama_jabatan,
                'jabatan_akhir'    => $lastPkl->jabatan->nama_jabatan,
            ]);

            // ================= FOOTER =================
            $lookups = Lookup::where('kategori', 'Sign Off')
                ->whereIn('code', ['Crewing Manager', 'Direktur'])
                ->pluck('value', 'code');

            $template->setValues([
                'manager'  => $lookups['Crewing Manager'] ?? '-',
                'direktur' => $lookups['Direktur'] ?? '-',
            ]);

            // ================= GENERATE FILE =================
            $fileName = 'Sign_Off_Form_' . $lastPkl->crew->nama_crew . '_' . now()->format('d-m-Y') . '.docx';
            $tempFile = tempnam(sys_get_temp_dir(), 'word');
            $template->saveAs($tempFile);

            return response()->download($tempFile, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $th) {
            report($th);
            return back()->with('error', 'Gagal generate dokumen: ' . $th->getMessage());
        }
    }

    public function generateFormPromosi(Request $request)
    {
        $pklQuery = CrewPkl::with([
            'wilayah:id,nama_wilayah',
            'jabatan:id,nama_jabatan,devisi,golongan',
            'crew:id,nama_crew',
            'kapal:id,nama_kapal',
            'perusahaan:id,nama_perusahaan,kode_perusahaan,alamat,telepon'
        ])->where('crew_id', $request->id);
        $lastPkl   = $pklQuery->orderBy('created_at', 'desc')->first();        // terbaru
        $secondPkl = $pklQuery->orderBy('created_at', 'desc')->skip(1)->first(); // terbaru ke-2

        $template = new TemplateProcessor(storage_path('app/public/templates/MUTASI PROMOSI.docx'));
        // ================= HEADER =================
       
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

        if (preg_match('/^\d+$/', $lastPkl->nomor_document)) {
            $lastPkl->nomor_document = "{$lastPkl->nomor_document}/{$lastPkl->perusahaan->kode_perusahaan}-CRW-MTS/{$bulanRomawi}/{$lastPkl->created_at->format('Y')}";
            $lastPkl->save();
        }
        $template->setValues([
            'nomor_document'      => $lastPkl->nomor_document,
            'perusahaan_new'      => $lastPkl->perusahaan->nama_perusahaan,
            'kode_perusahaan'     => $lastPkl->perusahaan->kode_perusahaan,
            'alamat_perusahaan'   => $lastPkl->perusahaan->alamat,
            'telepon_perusahaan'  => $lastPkl->perusahaan->telepon,
            'bulan'               => $bulanRomawi,
            'tahun'               => now()->format('Y'),
        ]);

        // ================= BODY =================
        $template->setValues([
            'nama_crew'             => $lastPkl->crew->nama_crew,
            'jabatan_old'          => $secondPkl->jabatan->devisi . ' / ' . $secondPkl->jabatan->golongan . '-' . $secondPkl->jabatan->nama_jabatan,
            'start_date_old'          => \Carbon\Carbon::parse($secondPkl->start_date)->format('d M Y'),
            'jabatan_new'          => $lastPkl->jabatan->devisi . ' / ' . $lastPkl->jabatan->golongan . '-' . $lastPkl->jabatan->nama_jabatan,
            'wilayah_operasional' => $lastPkl->wilayah->nama_wilayah,
            'gaji_old' => $secondPkl->gaji,
            'gaji_new' => $lastPkl->gaji,
            'perusahaan_old' =>  $secondPkl->perusahaan->nama_perusahaan,
            'start_date_new'       => \Carbon\Carbon::parse($lastPkl->start_date)->format('d M Y'),
            'tanggal' => now()->format('d M Y')
        ]);

        // ================= FOOTER =================
        $lookups = Lookup::where('kategori', 'Mutasi Promosi')
            ->whereIn('code', ['Dibuat Oleh', 'Diketahui Oleh', 'Disetujui Oleh'])
            ->pluck('value', 'code');

        $template->setValues([
            'dibuat_oleh'  => $lookups['Dibuat Oleh'] ?? '-',
            'diketahui_oleh' => $lookups['Diketahui Oleh'] ?? '-',
            'disetujui_oleh' => $lookups['Disetujui Oleh'] ?? '-',
        ]);

        // ================= GENERATE FILE =================
        $fileName = 'Mutasi_Promosi_' . $lastPkl->crew->nama_crew . '_' . now()->format('d-m-Y') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'word');
        $template->saveAs($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }
}
