<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>{{ $title ?? 'Sertifikat' }}</title>
    <style>
        @page { margin: 0; }
        body { margin: 0; padding: 0; font-family: "DejaVu Sans", Arial, sans-serif; color: #081326; }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 555px;
            overflow: hidden;
            /* Use user's background image while keeping a subtle overlay for text contrast */
            background-image: linear-gradient(rgba(255,255,255,0.88), rgba(255,255,255,0.88)), url('{{ public_path('assets/images/cert_bg.jpeg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .content {
            position: relative;
            z-index: 2;
            padding: 22px 42px 14px;
            text-align: center;
        }

        .watermark {
            position: absolute;
            top: 115px;
            left: 50%;
            margin-left: -168px;
            width: 336px;
            height: 336px;
            border-radius: 50%;
            border: 3px solid rgba(23, 52, 92, 0.08);
            z-index: 1;
        }

        .watermark:before {
            content: "";
            position: absolute;
            top: 16px; left: 16px; right: 16px; bottom: 16px;
            border-radius: 50%;
            border: 2px solid rgba(23, 52, 92, 0.06);
        }

        .watermark-text {
            position: absolute; top: 48%; left: 50%; width: 240px; margin-left: -120px; text-align: center;
            font-size: 22px; font-weight: bold; color: rgba(16,51,95,0.08); line-height: 1.2;
        }

        .left-ornament, .right-ornament { position: absolute; top: 34px; width: 116px; z-index: 0; }
        .left-ornament { left: 0; }
        .right-ornament { right: 0; }

        .tile { width: 58px; height: 58px; float: left; background: #00196a; position: relative }
        .tile.light { background: #0f67dc }
        .tile.white { background: transparent }
        .tile.cut:before { content: ""; position: absolute; width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.9); right: -1px; bottom: -1px }
        .tile.star:before, .tile.star:after { content: ""; position: absolute; background: rgba(255,255,255,0.9) }
        .tile.star:before { width: 16px; height: 58px; left: 21px; top: 0 }
        .tile.star:after { width: 58px; height: 16px; left: 0; top: 21px }
        .clearfix { clear: both }

        .logos { margin-bottom: 10px; font-size: 0 }
        .logo { display: inline-block; width: 36px; height: 36px; border-radius: 50%; margin: 0 4px; border: 2px solid #c8d0de; background: #fff; position: relative }
        .logo:after { content: ""; position: absolute; top: 7px; left: 7px; width: 18px; height: 18px; border-radius: 50%; background: #203f86; opacity: .75 }

        .title { font-size: 60px; line-height: 1; margin: 0; letter-spacing: 1px; font-weight: 900; color: #040d1d }
        .subtitle { margin-top: 8px; font-size: 43px; font-weight: bold; font-style: italic; font-family: "DejaVu Serif", serif }
        .name { margin-top: 8px; font-size: 56px; font-family: "DejaVu Serif", serif; line-height: 1.08 }
        .subtitle-2 { margin-top: 16px; font-size: 34px; font-weight: bold; font-style: italic; font-family: "DejaVu Serif", serif }
        .role { margin-top: 6px; font-size: 52px; line-height: 1.12; font-family: "DejaVu Serif", serif }

        .description { margin: 18px auto 0; max-width: 88%; font-size: 18px; line-height: 1.35; font-weight: bold }
        .number { margin-top: 10px; font-size: 14px; color: #2c3c58; letter-spacing: 0.3px }
        .date { margin-top: 6px; font-size: 12px; color: #37455f }

        .bottom { margin-top: 42px; position: relative; min-height: 142px }
        .qr { position: absolute; left: 0; bottom: 0; width: 95px; height: 95px; border: 6px solid #111; background: #fff }

        .sign-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-left: 112px }
        .sign-table td { width: 33.33%; text-align: center; vertical-align: bottom; font-size: 12px; padding: 0 6px; color: #0f1626 }
        .sig-title { font-size: 12px; font-weight: bold; margin-bottom: 40px }
        .sig-line { border-top: 1px solid #222; margin: 0 auto 5px; width: 88% }
        .sig-name { font-size: 11px; font-weight: bold; line-height: 1.25 }
    </style>
</head>
<body>
    <div class="page">
        <div class="left-ornament">
            <div class="tile light star"></div>
            <div class="tile cut"></div>
            <div class="tile"></div>
            <div class="tile star"></div>
            <div class="tile cut"></div>
            <div class="tile"></div>
            <div class="clearfix"></div>
        </div>

        <div class="right-ornament">
            <div class="tile"></div>
            <div class="tile star"></div>
            <div class="tile cut"></div>
            <div class="tile"></div>
            <div class="tile light star"></div>
            <div class="tile cut"></div>
            <div class="clearfix"></div>
        </div>

        <div class="watermark">
            <div class="watermark-text">HIMA D4<br>TEKNIK INFORMATIKA</div>
        </div>

        <div class="content">
            <div class="logos">
                <span class="logo"></span>
                <span class="logo"></span>
                <span class="logo"></span>
            </div>

            <h1 class="title">{{ $title ?? 'SERTIFIKAT' }}</h1>
            <div class="subtitle">Diberikan Kepada</div>
            <div class="name">{{ $name ?? '' }}</div>

            <div class="description">
                {{ $event ?? '' }}<br>
                Fakultas Vokasi, Universitas Airlangga
            </div>

            <div class="number">Nomor: {{ $number ?? '' }}</div>
            <div class="date">{{ $date ?? '' }}</div>

            <div class="bottom">
                <div class="qr"></div>

                <table class="sign-table">
                    <tr>
                        <td>
                            <div class="sig-title">Dekan</div>
                            <div class="sig-line"></div>
                            <div class="sig-name">Prof. Arviansyah<br>NIP. 4332327819</div>
                        </td>
                        <td>
                            <div class="sig-title">Kaprodi</div>
                            <div class="sig-line"></div>
                            <div class="sig-name">Nadin<br>NIP. 97675678889</div>
                        </td>
                        <td>
                            <div class="sig-title">Ketua HIMA</div>
                            <div class="sig-line"></div>
                            <div class="sig-name">Arvi<br>NIM. 32446576</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
