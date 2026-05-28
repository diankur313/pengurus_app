<!DOCTYPE html>
<html lang="id" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Undangan Bergabung — Pengurus Yisc Al Azhar</title>
    <style>
        /* Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #0a0f1e;
            color: #e2e8f0;
            -webkit-font-smoothing: antialiased;
        }
        a { text-decoration: none; }

        /* Animations */
        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
    </style>
</head>
<body style="background-color: #0a0f1e; margin: 0; padding: 0;">

    <!-- Email wrapper -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background: linear-gradient(135deg, #0a0f1e 0%, #0f172a 40%, #1a0a2e 100%); min-height: 100vh; padding: 40px 16px;">
        <tr>
            <td align="center" valign="top">

                <!-- Main card -->
                <table width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="max-width: 580px; margin: 0 auto;">

                    <!-- Top decorative bar -->
                    <tr>
                        <td style="height: 4px; background: linear-gradient(90deg, #f59e0b, #d97706, #92400e, #f59e0b); border-radius: 4px 4px 0 0;"></td>
                    </tr>

                    <!-- Card body -->
                    <tr>
                        <td style="
                            background: rgba(15, 23, 42, 0.95);
                            border: 1px solid rgba(245, 158, 11, 0.2);
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
                                        background: linear-gradient(180deg, rgba(245,158,11,0.08) 0%, transparent 100%);
                                        border-bottom: 1px solid rgba(245, 158, 11, 0.12);
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
                                                    border: 3px solid rgba(245, 158, 11, 0.4);
                                                    box-shadow: 0 0 20px rgba(245, 158, 11, 0.2);
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
                                            color: #f59e0b;
                                            letter-spacing: 2px;
                                            text-transform: uppercase;
                                            font-weight: 600;
                                        ">Portal Pengurus</div>
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
                                            background: rgba(245, 158, 11, 0.12);
                                            border: 1px solid rgba(245, 158, 11, 0.3);
                                            border-radius: 100px;
                                            padding: 6px 16px;
                                            font-size: 11px;
                                            font-weight: 700;
                                            color: #f59e0b;
                                            letter-spacing: 1.5px;
                                            text-transform: uppercase;
                                            margin-bottom: 20px;
                                        ">✦ Undangan Resmi</div>

                                        <!-- Greeting -->
                                        <div style="
                                            font-size: 28px;
                                            font-weight: 800;
                                            color: #f8fafc;
                                            line-height: 1.3;
                                            margin-bottom: 16px;
                                        ">Halo, {{ $userName }}! 👋</div>

                                        <!-- Body text -->
                                        <div style="
                                            font-size: 15px;
                                            color: #94a3b8;
                                            line-height: 1.8;
                                        ">
                                            Kamu diundang untuk bergabung pada aplikasi <strong style="color: #e2e8f0;">Pengurus Yisc Al Azhar</strong>.
                                            Berikut adalah informasi akun kamu. Harap jaga akses ini agar tidak disalahgunakan.
                                        </div>

                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 0 40px;">
                                        <div style="height: 1px; background: linear-gradient(90deg, transparent, rgba(245,158,11,0.3), transparent);"></div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Credential Box -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 32px 40px;">

                                        <!-- Section title -->
                                        <div style="
                                            font-size: 11px;
                                            font-weight: 700;
                                            color: #f59e0b;
                                            letter-spacing: 2px;
                                            text-transform: uppercase;
                                            margin-bottom: 16px;
                                        ">🔑 Informasi Akun</div>

                                        <!-- Credential card -->
                                        <div style="
                                            background: rgba(255, 255, 255, 0.03);
                                            border: 1px solid rgba(245, 158, 11, 0.2);
                                            border-radius: 16px;
                                            overflow: hidden;
                                        ">
                                            <!-- Email row -->
                                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td style="
                                                        padding: 18px 24px;
                                                        border-bottom: 1px solid rgba(255,255,255,0.05);
                                                    ">
                                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                            <tr>
                                                                <td style="width: 80px;">
                                                                    <span style="
                                                                        font-size: 11px;
                                                                        font-weight: 600;
                                                                        color: #64748b;
                                                                        text-transform: uppercase;
                                                                        letter-spacing: 1px;
                                                                    ">Email</span>
                                                                </td>
                                                                <td>
                                                                    <span style="
                                                                        font-size: 14px;
                                                                        color: #e2e8f0;
                                                                        font-weight: 500;
                                                                    ">{{ $userEmail }}</span>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>

                                                <!-- Password row -->
                                                <tr>
                                                    <td style="padding: 18px 24px;">
                                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                            <tr>
                                                                <td style="width: 80px; vertical-align: top; padding-top: 4px;">
                                                                    <span style="
                                                                        font-size: 11px;
                                                                        font-weight: 600;
                                                                        color: #64748b;
                                                                        text-transform: uppercase;
                                                                        letter-spacing: 1px;
                                                                    ">Password</span>
                                                                </td>
                                                                <td>
                                                                    <!-- Password chip -->
                                                                    <div style="
                                                                        display: inline-block;
                                                                        background: rgba(245, 158, 11, 0.1);
                                                                        border: 1px solid rgba(245, 158, 11, 0.35);
                                                                        border-radius: 8px;
                                                                        padding: 8px 16px;
                                                                        font-family: 'Courier New', Courier, monospace;
                                                                        font-size: 20px;
                                                                        font-weight: 700;
                                                                        color: #fbbf24;
                                                                        letter-spacing: 4px;
                                                                    ">{{ $userPassword }}</div>
                                                                    <div style="
                                                                        margin-top: 8px;
                                                                        font-size: 11px;
                                                                        color: #475569;
                                                                    ">Segera ubah password setelah login pertama.</div>
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
                                            <!-- Button wrapper for email client compatibility -->
                                            <table cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;">
                                                <tr>
                                                    <td style="
                                                        background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
                                                        border-radius: 14px;
                                                        box-shadow: 0 8px 24px rgba(245, 158, 11, 0.35);
                                                    ">
                                                        <a href="{{ $appUrl }}"
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
                                                            🚀 &nbsp;Masuk ke Portal
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>

                                        <!-- Fallback URL -->
                                        <div style="
                                            font-size: 12px;
                                            color: #475569;
                                            margin-top: 12px;
                                        ">
                                            Atau buka: 
                                            <a href="{{ $appUrl }}" style="color: #f59e0b; word-break: break-all;">{{ $appUrl }}</a>
                                        </div>

                                    </td>
                                </tr>
                            </table>

                            <!-- Warning notice -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 0 40px 36px;">
                                        <div style="
                                            background: rgba(239, 68, 68, 0.06);
                                            border: 1px solid rgba(239, 68, 68, 0.2);
                                            border-radius: 12px;
                                            padding: 16px 20px;
                                            display: flex;
                                            align-items: flex-start;
                                            gap: 12px;
                                        ">
                                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td style="width: 24px; vertical-align: top; padding-top: 1px;">
                                                        <span style="font-size: 14px;">⚠️</span>
                                                    </td>
                                                    <td>
                                                        <div style="
                                                            font-size: 12px;
                                                            color: #fca5a5;
                                                            font-weight: 600;
                                                            margin-bottom: 4px;
                                                        ">Penting: Keamanan Akun</div>
                                                        <div style="font-size: 12px; color: #94a3b8; line-height: 1.6;">
                                                            Jangan bagikan email dan password ini kepada siapapun.
                                                            Segera ubah password setelah login pertama melalui menu profil.
                                                            Jika kamu merasa tidak mendaftar, abaikan email ini.
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
                                            © {{ date('Y') }} Yisc Al Azhar · All rights reserved
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
