<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>{{ $title ?? 'Pengumuman' }}</title>
    <style>
        @page { margin: 40px 40px; }
        body { font-family: "DejaVu Sans", Arial, sans-serif; color: #111; margin: 0; }

        .header {
            text-align: center;
            margin-bottom: 6px;
        }

        .header .branding {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
        }

        .logo {
            width: 78px;
            height: 78px;
            display: inline-block;
            background-image: url('{{ public_path('assets/images/unair_logo.png') }}');
            background-size: contain;
            background-position: center;
            background-repeat: no-repeat;
        }

        .unair-title { font-size: 20px; font-weight: bold; margin: 0; }
        .directorate { font-size: 18px; font-weight: 800; margin: 0; }
        .contact { font-size: 10.5px; margin-top: 4px; }

        hr.sep { border: 0; border-top: 1px solid #111; margin: 12px 0 18px; }

        .meta {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .meta .left { width: 65%; }
        .meta .right { text-align: right; width: 35%; }

        .body {
            font-size: 12.5px;
            line-height: 1.55;
            text-align: justify;
        }

        .signature {
            margin-top: 36px;
            text-align: center;
            font-size: 12px;
        }

        .signature .director-name { font-weight: bold; margin-top: 2px; }
        .signature .nip { font-size: 11px; color: #222; }

        .footer-notes { margin-top: 18px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="branding">
            <div class="logo" aria-hidden="true"></div>
            <div>
                <div class="unair-title">UNIVERSITAS AIRLANGGA</div>
                <div class="directorate">DIREKTORAT PENDIDIKAN</div>
                <div class="contact">Kampus C Mulyorejo Surabaya 60115 • Telp. (031) 5914042, 5914043 Fax (031) 5962875</div>
            </div>
        </div>
    </div>

    <hr class="sep">

    <div class="meta">
        <div class="left">
            <div>Nomor : {{ $number ?? '-' }}</div>
            <div>Hal &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $subject ?? '-' }}</div>
        </div>
        <div class="right">{{ $date ?? date('d F Y') }}</div>
    </div>

    <div class="body">
        <div>Yth.</div>
        @if(!empty($recipients) && is_array($recipients))
            <ol>
                @foreach($recipients as $r)
                    <li style="margin-bottom:4px">{{ $r }}</li>
                @endforeach
            </ol>
        @elseif(!empty($recipient))
            <div>{{ $recipient }}</div>
        @endif

        <p style="margin-top:8px;">{!! $intro ?? 'Bersama ini diberitahukan hal-hal sebagai berikut:' !!}</p>

        @if(!empty($points) && is_array($points))
            <ol>
                @foreach($points as $pt)
                    <li style="margin-bottom:6px">{!! $pt !!}</li>
                @endforeach
            </ol>
        @else
            <p>{!! $body ?? '-' !!}</p>
        @endif

        <p>Demikian pemberitahuan ini untuk diketahui dan dipergunakan sebagaimana mestinya.</p>
    </div>

    <div class="signature">
        <div>Direktur,</div>
        <div style="height:68px">@if(!empty($signature_image))<img src="{{ public_path($signature_image) }}" style="height:68px">@endif</div>
        <div class="director-name">{{ $director_name ?? 'Dr. Nama Direktur' }}</div>
        <div class="nip">NIP. {{ $director_nip ?? '000000000000000000' }}</div>
    </div>

    @if(!empty($cc) && is_array($cc))
        <div class="footer-notes">
            <strong>Tembusan:</strong>
            <ol>
                @foreach($cc as $c)
                    <li>{{ $c }}</li>
                @endforeach
            </ol>
        </div>
    @endif

</body>
</html>
