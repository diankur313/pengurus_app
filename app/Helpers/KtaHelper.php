<?php

use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Typography\FontFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;

if (!function_exists('generateKtaImageContent')) {
    /**
     * Generate KTA image string (PNG binary data)
     */
    function generateKtaImageContent(string $nama, string $angkatan, string $nomor): string
    {
        $manager = new ImageManager(new Driver());
        $image   = $manager->decode(public_path('kta_raw.png'));

        ktaTextWithBorder($image, $nama,     40, 420, 40);
        ktaTextWithBorder($image, $angkatan, 40, 480, 40);
        ktaTextWithBorder($image, $nomor,    40, 540, 40);

        $qrOptions = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64'    => false,
            'eccLevel'        => EccLevel::H,
            'scale'           => 8,
            'addQuietzone'    => true,
            'imageTransparent'=> false,
        ]);

        $qrPng = (new QRCode($qrOptions))->render($nomor);
        $qrImage = $manager->decode($qrPng);
        $image->insert($qrImage, 120, 150);

        return $image->encodeUsingFileExtension('png')->toString();
    }
}

if (!function_exists('ktaTextWithBorder')) {
    /**
     * Tulis teks dengan efek border/shadow menggunakan Intervention Image v4
     */
    function ktaTextWithBorder(ImageInterface $image, string $text, int $x, int $y, int $size): void
    {
        $fontPath = storage_path('fonts/Kadwa-Regular.ttf');
        $borderColor = '#9E9E9E';

        // Tulis border (4 arah)
        foreach ([[-1, 0], [1, 0], [0, -1], [0, 1]] as [$dx, $dy]) {
            $image->text($text, $x + $dx, $y + $dy, function (FontFactory $font) use ($fontPath, $size, $borderColor) {
                $font->filename($fontPath);
                $font->size($size);
                $font->color($borderColor);
                $font->align('left', 'top');
            });
        }

        // Tulis teks utama (putih)
        $image->text($text, $x, $y, function (FontFactory $font) use ($fontPath, $size) {
            $font->filename($fontPath);
            $font->size($size);
            $font->color('#ffffff');
            $font->align('left', 'top');
        });
    }
}

if (!function_exists('profilePhotoUrl')) {
    /**
     * Resolve URL foto profil dengan dukungan semua format:
     * - /storage/avatars/xxx.jpg  (dari join-ppab Google SSO via Storage::url())
     * - http://...                (URL eksternal)
     * - filename.jpg              (file lokal via route /profile-picture/)
     *
     * @param  string|null $photo   Nilai kolom photo dari DB
     * @param  string|null $name    Nama untuk fallback avatar
     * @return string
     */
    function profilePhotoUrl(?string $photo, ?string $name = null): string
    {
        $fallback = 'https://ui-avatars.com/api/?name=' . urlencode($name ?? 'U') . '&color=FFFFFF&background=09090b';

        if (blank($photo) || $photo === 'avatar.png') {
            return $fallback;
        }

        // URL eksternal langsung (http/https)
        if (str_starts_with($photo, 'http')) {
            return $photo;
        }

        // Path dari Storage::url() join-ppab: /storage/avatars/xxx.jpg
        // Kita tetap kirim via route /profile-picture/ supaya auth check tetap jalan
        return url('/profile-picture/' . ltrim($photo, '/'));
    }
}
