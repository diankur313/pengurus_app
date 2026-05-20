<?php

$pages = [
    'AllCivitas.php' => ['label' => 'All Civitas'],
    'KartuTandaAnggota.php' => ['label' => 'Kartu Tanda Anggota'],
    'Finance.php' => ['label' => 'Finance'],
    'DaftarDomisili.php' => ['label' => 'Daftar Domisili'],
    'DaftarPekerjaan.php' => ['label' => 'Daftar Pekerjaan'],
    'DaftarFeeTransaksi.php' => ['label' => 'Daftar Fee Transaksi'],
    'DaftarHariLibur.php' => ['label' => 'Daftar Hari Libur'],
    'DaftarNamaBank.php' => ['label' => 'Daftar Nama Bank'],
    'DaftarAngkatan.php' => ['label' => 'Daftar Angkatan'],
];

foreach ($pages as $filename => $data) {
    $path = "/www/wwwroot/app2.yiscalazhar.web.id/app/Filament/Pages/" . $filename;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Bersihkan spasi dari label
        $pattern = '/protected static \?string \$navigationLabel = \'.*?\';/';
        $replacement = "protected static ?string \$navigationLabel = '" . $data['label'] . "';";
        $content = preg_replace($pattern, $replacement, $content);
        
        file_put_contents($path, $content);
    }
}

echo "Labels cleaned successfully.\n";
