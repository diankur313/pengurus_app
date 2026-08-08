<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KTA - {{ $memberName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #1a1a2e; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: Arial, sans-serif; }
        .kta-card {
            position: relative;
            width: 600px;
            height: 380px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .kta-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .kta-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            padding: 2rem 2.5rem;
            gap: 2rem;
        }
        .kta-info {
            flex: 1;
            color: #ffffff;
        }
        .kta-label {
            font-size: 9px;
            font-weight: 900;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.6);
            margin-bottom: 4px;
        }
        .kta-name {
            font-size: 18px;
            font-weight: 900;
            margin-bottom: 12px;
            line-height: 1.2;
        }
        .kta-field {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
            color: rgba(255,255,255,0.85);
        }
        .kta-no {
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 0.06em;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="kta-card">
        @if($bgData)
            <img src="{{ $bgData }}" alt="" class="kta-bg">
        @endif
        <div class="kta-overlay">
            <div class="kta-info">
                <div class="kta-label">Kartu Tanda Anggota</div>
                <div class="kta-name">{{ $memberName }}</div>
                <div class="kta-field">No. Anggota: <span class="kta-no">{{ $memberNoDisplay }}</span></div>
                @if($kelas)
                    <div class="kta-field">Kelas: {{ $kelas }}</div>
                @endif
                @if($angkatan)
                    <div class="kta-field">Angkatan: {{ $angkatan }}</div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>