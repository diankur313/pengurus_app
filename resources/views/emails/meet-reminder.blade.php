<!DOCTYPE html>
<html lang="id" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Pengingat Jadwal Pembelajaran Online — {{ $appName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #0a0f1e;
            color: #e2e8f0;
            -webkit-font-smoothing: antialiased;
        }
        a { text-decoration: none; }
    </style>
</head>
<body style="background-color: #0a0f1e; margin: 0; padding: 0;">

    <!-- Email wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background: linear-gradient(135deg, #0a0f1e 0%, #0f172a 40%, #0a1a2e 100%); min-height: 100vh; padding: 40px 16px;">
        <tr>
            <td align="center" valign="top">

                <!-- Main card -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="max-width: 580px; margin: 0 auto;">

                    <!-- Top decorative bar -->
                    <tr>
                        <td style="height: 4px; background: linear-gradient(90deg, #3b82f6, #2563eb, #1d4ed8, #3b82f6); border-radius: 4px 4px 0 0;"></td>
                    </tr>

                    <!-- Card body -->
                    <tr>
                        <td style="
                            background: rgba(15, 23, 42, 0.95);
                            border: 1px solid rgba(59, 130, 246, 0.2);
                            border-top: none;
                            border-radius: 0 0 20px 20px;
                            padding: 0;
                            overflow: hidden;
                        ">

                            <!-- Header: Logo + Brand -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="
                                        padding: 36px 40px 28px;
                                        background: linear-gradient(180deg, rgba(59,130,246,0.08) 0%, transparent 100%);
                                        border-bottom: 1px solid rgba(59, 130, 246, 0.12);
                                        text-align: center;
                                    ">
                                        <!-- Logo -->
                                        <div style="margin-bottom: 16px;">
                                            <img src="{{ $logoUrl }}" alt="Yisc Al Azhar Logo"
                                                width="80" height="80"
                                                style="
                                                    width: 80px;
                                                    height: 80px;
                                                    border-radius: 50%;
                                                    border: 3px solid rgba(59, 130, 246, 0.4);
                                                    box-shadow: 0 0 20px rgba(59, 130, 246, 0.2);
                                                    display: block;
                                                    margin: 0 auto;
                                                    object-fit: cover;
                                                ">
                                        </div>
                                        <!-- Brand name -->
                                        <div style="
                                            font-size: 22px;
                                            font-weight: 800;
                                            letter-spacing: 0.5px;
                                            color: #f8fafc;
                                            margin-bottom: 4px;
                                        ">Yisc Al Azhar</div>
                                        <div style="
                                            font-size: 12px;
                                            color: #3b82f6;
                                            letter-spacing: 2px;
                                            text-transform: uppercase;
                                            font-weight: 600;
                                        ">Portal Pendidikan</div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Hero: Greeting -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 40px 40px 24px;">

                                        <!-- Badge -->
                                        <div style="
                                            display: inline-block;
                                            background: rgba(59, 130, 246, 0.12);
                                            border: 1px solid rgba(59, 130, 246, 0.3);
                                            border-radius: 100px;
                                            padding: 6px 16px;
                                            font-size: 11px;
                                            font-weight: 700;
                                            color: #60a5fa;
                                            letter-spacing: 1.5px;
                                            text-transform: uppercase;
                                            margin-bottom: 20px;
                                        ">📅 Pengingat Jadwal</div>

                                        <!-- Greeting -->
                                        <div style="
                                            font-size: 26px;
                                            font-weight: 800;
                                            color: #f8fafc;
                                            line-height: 1.3;
                                            margin-bottom: 16px;
                                        ">Assalamu'alaikum, {{ $recipientName }}!</div>

                                        <!-- Body text -->
                                        <div style="
                                            font-size: 15px;
                                            color: #94a3b8;
                                            line-height: 1.8;
                                        ">
                                            Jadwal pembelajaran online akan dimulai dalam
                                            <strong style="color: #60a5fa;">{{ $reminderLabel }}</strong>.
                                            Silakan bersiap dan masuk ke portal untuk bergabung ke sesi.
                                        </div>

                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 0 40px;">
                                        <div style="height: 1px; background: linear-gradient(90deg, transparent, rgba(59,130,246,0.3), transparent);"></div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Schedule Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 32px 40px;">

                                        <!-- Section title -->
                                        <div style="
                                            font-size: 11px;
                                            font-weight: 700;
                                            color: #3b82f6;
                                            letter-spacing: 2px;
                                            text-transform: uppercase;
                                            margin-bottom: 16px;
                                        ">🎓 Detail Jadwal</div>

                                        <!-- Schedule card -->
                                        <div style="
                                            background: rgba(255, 255, 255, 0.03);
                                            border: 1px solid rgba(59, 130, 246, 0.2);
                                            border-radius: 16px;
                                            overflow: hidden;
                                        ">

                                            <!-- Row: Judul -->
                                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td style="padding: 14px 24px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                            <tr>
                                                                <td style="width: 90px; vertical-align: top; padding-top: 1px;">
                                                                    <span style="font-size: 11px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 1px;">Judul</span>
                                                                </td>
                                                                <td>
                                                                    <span style="font-size: 14px; color: #e2e8f0; font-weight: 600;">{{ $scheduleTitle }}</span>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>

                                                <!-- Row: Tipe -->
                                                <tr>
                                                    <td style="padding: 14px 24px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                            <tr>
                                                                <td style="width: 90px;">
                                                                    <span style="font-size: 11px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 1px;">Tipe</span>
                                                                </td>
                                                                <td>
                                                                    <span style="font-size: 14px; color: #e2e8f0;">{{ $scheduleType }}</span>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>

                                                <!-- Row: Angkatan -->
                                                <tr>
                                                    <td style="padding: 14px 24px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                            <tr>
                                                                <td style="width: 90px;">
                                                                    <span style="font-size: 11px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 1px;">Angkatan</span>
                                                                </td>
                                                                <td>
                                                                    <span style="font-size: 14px; color: #e2e8f0;">{{ $scheduleLevel }}</span>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>

                                                <!-- Row: Ustadz -->
                                                <tr>
                                                    <td style="padding: 14px 24px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                            <tr>
                                                                <td style="width: 90px;">
                                                                    <span style="font-size: 11px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 1px;">Ustadz</span>
                                                                </td>
                                                                <td>
                                                                    <span style="font-size: 14px; color: #e2e8f0;">{{ $teacherName }}</span>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>

                                                <!-- Row: Waktu -->
                                                <tr>
                                                    <td style="padding: 14px 24px;">
                                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                            <tr>
                                                                <td style="width: 90px;">
                                                                    <span style="font-size: 11px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 1px;">Waktu</span>
                                                                </td>
                                                                <td>
                                                                    <span style="
                                                                        font-size: 14px;
                                                                        color: #60a5fa;
                                                                        font-weight: 700;
                                                                    ">{{ $startAt }} – {{ $endAt }} WIB</span>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>

                                            </table>
                                        </div>

                                    </td>
                                </tr>
                            </table>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 0 40px 40px;" align="center">

                                        <div style="margin-bottom: 20px;">
                                            <table cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;">
                                                <tr>
                                                    <td style="
                                                        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
                                                        border-radius: 14px;
                                                        box-shadow: 0 8px 24px rgba(59, 130, 246, 0.35);
                                                    ">
                                                        <a href="{{ $portalUrl }}"
                                                            style="
                                                                display: inline-block;
                                                                padding: 16px 48px;
                                                                font-size: 16px;
                                                                font-weight: 800;
                                                                color: #ffffff;
                                                                text-decoration: none;
                                                                letter-spacing: 0.5px;
                                                                white-space: nowrap;
                                                            ">
                                                            🌐 &nbsp;Masuk ke Portal
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>

                                        <div style="
                                            font-size: 12px;
                                            color: #475569;
                                            margin-top: 12px;
                                            line-height: 1.6;
                                        ">
                                            Login ke portal menggunakan akun kamu, lalu pilih jadwal yang aktif untuk bergabung ke sesi Google Meet.
                                        </div>

                                    </td>
                                </tr>
                            </table>

                            <!-- Info notice -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 0 40px 36px;">
                                        <div style="
                                            background: rgba(59, 130, 246, 0.06);
                                            border: 1px solid rgba(59, 130, 246, 0.15);
                                            border-radius: 12px;
                                            padding: 16px 20px;
                                        ">
                                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td style="width: 24px; vertical-align: top; padding-top: 1px;">
                                                        <span style="font-size: 14px;">ℹ️</span>
                                                    </td>
                                                    <td style="padding-left: 8px;">
                                                        <div style="font-size: 12px; color: #93c5fd; font-weight: 600; margin-bottom: 4px;">Link akan tersedia setelah login</div>
                                                        <div style="font-size: 12px; color: #64748b; line-height: 1.6;">
                                                            Link Google Meet hanya dapat diakses melalui portal e-SII setelah login dengan akun kamu.
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Footer -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="
                                        padding: 24px 40px;
                                        border-top: 1px solid rgba(255,255,255,0.05);
                                        text-align: center;
                                        background: rgba(0,0,0,0.2);
                                        border-radius: 0 0 20px 20px;
                                    ">
                                        <div style="font-size: 13px; color: #334155; margin-bottom: 4px;">
                                            Dikirim oleh sistem otomatis · Jangan balas email ini
                                        </div>
                                        <div style="font-size: 12px; color: #1e293b;">
                                            © {{ date('Y') }} {{ $appName }} · All rights reserved
                                        </div>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                </table>
                <!-- /Main card -->

            </td>
        </tr>
    </table>

</body>
</html>
