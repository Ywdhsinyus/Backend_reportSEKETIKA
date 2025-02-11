<?php

namespace App\Http\Controllers;

use App\Models\ReportSeminar;
use App\Models\PendaftarProyek;
use App\Models\PendaftarSeminar;
use App\Models\Proyek;
use PDF;
use App\Models\Seminar;
use Illuminate\Http\Request;

class PendaftarController extends Controller
{
    //Seminar
    public function seminar()
    {
        $user = auth()->user();
        $seminars = PendaftarSeminar::with(['user', 'seminar'])
                                    ->where('user_id', $user->id)
                                    ->get()
                                    ->groupBy('seminar_id');
        return view('admin.pendaftar.seminar', compact('seminars'));
    }

    public function regisseminar($seminar_id)
    {
        $user = auth()->user();
    
        $seminar = Seminar::find($seminar_id);
    
        if (!$seminar) {
            return redirect()->back()->with('error', 'Seminar tidak ditemukan.');
        }
    
        return view('admin.pendaftar.regisseminar', compact('user', 'seminar'));
    }

    public function storeSeminar(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'string|max:255',
            'no_identitas' => 'integer',
            'judul' => 'string|max:255',
            'email' => 'required|string|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'no_whatsapp' => 'required|numeric|min:1|regex:/^[0-9]+$/'
        ],[
            'no_whatsapp' => 'No whatsapp tidak valid.',
        ]);

        $pendaftarSeminar = PendaftarSeminar::create([
            'user_id' => auth()->user()->id,
            'seminar_id' => $request->seminar_id,
            'email' => $request->email,
            'no_whatsapp' => $request->no_whatsapp,
        ]);

        if ($pendaftarSeminar) {
            return redirect()->route('dashboard.showSeminar',  ['id' => $request->seminar_id])->with('success', 'Pendaftaran seminar berhasil!');
        } else {
            return redirect()->back()->with('error', 'Gagal menyimpan data pendaftaran seminar.');
        }
    }
    public function downloadPdfSeminar($seminar_id)
    {
        $seminarGroup = PendaftarSeminar::with(['user', 'seminar'])
            ->where('seminar_id', $seminar_id)
            ->get();

        if ($seminarGroup->isEmpty()) {
            return redirect()->back()->with('error', 'Seminar tidak ditemukan.');
        }

        $pdf = PDF::loadView('admin.pendaftar.pdfSeminar', compact('seminarGroup'));

        $pdf->setPaper('A4', 'landscape');

        $seminarJudul = $seminarGroup->first()->seminar->judul ?? 'Seminar_Tidak_Ditemukan';

        return $pdf->download('laporan_' . str_replace(' ', '_', $seminarJudul) . '.pdf');
    }

    // Proyek
    public function proyek()
    {
        $user = auth()->user();
        $proyeks = PendaftarProyek::with(['user', 'proyek'])
                                ->where('user_id', $user->id)
                                ->get()
                                ->groupBy('proyek_id');
        return view('admin.pendaftar.proyek', compact('proyeks'));
    }

    public function regisproyek($proyek_id)
    {
        $user = auth()->user();
    
        $proyek = Proyek::find($proyek_id);
    
        if (!$proyek) {
            return redirect()->back()->with('error', 'Proyek tidak ditemukan.');
        }
    
        return view('admin.pendaftar.regisproyek', compact('user', 'proyek'));
    }

    public function storeProyek(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'string|max:255',
            'no_identitas' => 'integer',
            'no_telepon' => 'required|numeric|min:1|regex:/^[0-9]+$/',
            'judul' => 'string|max:255',
            'portofolio' => 'required|regex:/^(https?:\/\/)?(www\.)?(drive\.google\.com\/[^\s]+)$/',
        ], [
            'portofolio.regex' => 'Portofolio harus berupa link Google Drive yang valid.',
            'no_telepon.min' => 'No telepon tidak valid.',
        ]);

        $pendaftarProyek = PendaftarProyek::create([
            'user_id' => auth()->user()->id,
            'proyek_id' => $request->proyek_id,
            'portofolio' => $request->portofolio,
            'status' => 'mendaftar',
        ]);

        if ($pendaftarProyek) {
            return redirect()->route('dashboard.showProyek',  ['id' => $request->proyek_id])->with('success', 'Pendaftaran proyek berhasil!');
        } else {
            return redirect()->back()->with('error', 'Gagal menyimpan data pendaftaran proyek.');
        }
    }

    public function downloadPdfProyek($proyek_id)
    {
        $proyekGroup = PendaftarProyek::with(['user', 'proyek'])
            ->where('proyek_id', $proyek_id)
            ->get();

        if ($proyekGroup->isEmpty()) {
            return redirect()->back()->with('error', 'Proyek tidak ditemukan.');
        }

        $pdf = PDF::loadView('admin.pendaftar.pdfProyek', compact('proyekGroup'));

        $pdf->setPaper('A4', 'landscape');

        $proyekJudul = $proyekGroup->first()->proyek->judul ?? 'Proyek_Tidak_Ditemukan';

        return $pdf->download('laporan_' . str_replace(' ', '_', $proyekJudul) . '.pdf');
    }
}