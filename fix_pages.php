<?php

$pages = [
    'AllCivitas.php' => ['label' => '   All Civitas', 'sort' => 3],
    'KartuTandaAnggota.php' => ['label' => '   Kartu Tanda Anggota', 'sort' => 4],
    'Finance.php' => ['label' => '   Finance', 'sort' => 5],
    'DaftarDomisili.php' => ['label' => '   Daftar Domisili', 'sort' => 7],
    'DaftarPekerjaan.php' => ['label' => '   Daftar Pekerjaan', 'sort' => 8],
    'DaftarFeeTransaksi.php' => ['label' => '   Daftar Fee Transaksi', 'sort' => 9],
    'DaftarHariLibur.php' => ['label' => '   Daftar Hari Libur', 'sort' => 10],
    'DaftarNamaBank.php' => ['label' => '   Daftar Nama Bank', 'sort' => 11],
    'DaftarAngkatan.php' => ['label' => '   Daftar Angkatan', 'sort' => 12],
];

foreach ($pages as $filename => $data) {
    $path = "/www/wwwroot/app2.yiscalazhar.web.id/app/Filament/Pages/" . $filename;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Pastikan ada Trait
        if (!str_contains($content, 'use HasPageShield;')) {
            $content = str_replace('class ' . str_replace('.php', '', $filename) . ' extends Page', "class " . str_replace('.php', '', $filename) . " extends Page\n{\n    use HasPageShield;", $content);
        }
        
        // Pastikan label memiliki 3 spasi non-breaking
        $pattern = '/protected static \?string \$navigationLabel = \'.*?\';/';
        $replacement = "protected static ?string \$navigationLabel = '" . $data['label'] . "';";
        $content = preg_replace($pattern, $replacement, $content);
        
        file_put_contents($path, $content);
    }
}

echo "All pages updated successfully.\n";
