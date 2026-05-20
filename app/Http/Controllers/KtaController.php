<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;

class KtaController extends Controller
{
    /**
     * Generate dan download KTA sebagai PNG
     *
     * @param string $source  'lama' | 'ppab'
     * @param string $id       member_no (lama) atau id_member (ppab)
     */
    public function download(string $source, string $id)
    {
        // 1. Ambil data member sesuai source
        if ($source === 'lama') {
            $user = DB::connection('yisic_db_lama')
                ->table('member')
                ->where('member_no', $id)
                ->first();

            abort_if(is_null($user), 404, 'Member tidak ditemukan');

            $nama     = (string) $user->member_name;
            $angkatan = (string) $user->member_nama_angkatan;
            $nomor    = (string) $user->member_no;
        } else {
            $user = DB::connection('ppab')
                ->table('ppab_member')
                ->where('id_member', $id)
                ->first();

            abort_if(is_null($user), 404, 'Member tidak ditemukan');

            $nama     = (string) $user->name;
            $angkatan = (string) $user->nama_angkatan;
            $nomor    = (string) $user->id_member;
        }

        // 2. Generate KTA via helper
        set_time_limit(180);
        ini_set('memory_limit', '512M');
        $imageContent = generateKtaImageContent($nama, $angkatan, $nomor);

        // 3. Return PNG untuk download
        $filename = 'KTA_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nama) . '.png';

        return response($imageContent, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
