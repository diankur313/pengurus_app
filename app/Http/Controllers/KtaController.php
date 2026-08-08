<?php

namespace App\Http\Controllers;

use App\Models\CivitasPendidikan;
use App\Models\MemberLama;
use App\Models\MemberPpab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KtaController extends Controller
{
    public function download(Request $request, string $source, string $id)
    {
        if ($source === 'lama') {
            $member = MemberLama::find($id);

            if (!$member) {
                abort(404, 'Data anggota tidak ditemukan.');
            }

            $memberName      = $member->member_name ?? 'Unknown';
            $memberNoDisplay = $member->member_no;
            // Fallback to member_angk if member_nama_angkatan is empty
            $angkatan        = $member->member_nama_angkatan ?: ($member->member_angk ?? '');

        } elseif ($source === 'ppab') {
            $member = MemberPpab::where('id_member', $id)->first();

            if (!$member) {
                abort(404, 'Data anggota tidak ditemukan.');
            }

            $namaAngkatan = DB::connection('ppab')
                ->table('ppab_nama_angkatans')
                ->where('id', $member->angkatan)
                ->value('nama_angkatan');

            $memberName      = $member->name ?? 'Unknown';
            $memberNoDisplay = $member->id_member;
            $angkatan        = $namaAngkatan ?? '';

        } elseif ($source === 'baru') {
            $civitas = CivitasPendidikan::where('id_ppab', $id)->first();

            if (!$civitas) {
                abort(404, 'Data anggota tidak ditemukan.');
            }

            $memberName      = $civitas->nama ?? 'Unknown';
            $memberNoDisplay = $civitas->id_ppab;
            $angkatan        = $civitas->angkatan ?? '';

        } else {
            abort(404, 'Tipe tidak dikenali.');
        }

        // Use the same template as "download all" 
        $imageContent = generateKtaImageContent($memberName, $angkatan, $memberNoDisplay);
        
        return response()->streamDownload(function () use ($imageContent) {
            echo $imageContent;
        }, 'KTA-' . $memberNoDisplay . '.png', ['Content-Type' => 'image/png']);
    }
}