<!DOCTYPE html>
<html>
<head>
    <title>Segera Selesaikan Pembayaran PPAB</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="color: #10b981; text-align: center;">YISC Al-Azhar</h2>
        <p>Assalamu'alaikum {{ $user->name }},</p>
        <p>Anda telah memilih tiket <strong>{{ $ticketType }}</strong> untuk pendaftaran PPAB. Tiket ini telah kami simpan sementara untuk Anda.</p>
        <p style="color: #ef4444; font-weight: bold;">Waktu Anda untuk menyelesaikan pembayaran tersisa kurang dari 30 Menit!</p>
        <p>Segera selesaikan pembayaran Anda sebelum tiket ini otomatis dikembalikan ke sistem dan dapat diambil oleh peserta lain.</p>
        <div style="text-align: center; margin: 30px 0;">
            <a href="https://join-ppab.yiscalazhar.web.id/complete-profile" style="background-color: #10b981; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">Lanjutkan Pembayaran</a>
        </div>
        <p>Terima kasih,<br>Panitia PPAB YISC Al-Azhar</p>
    </div>
</body>
</html>
