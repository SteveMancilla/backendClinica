<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Informe — {{ $patientName }}</title>
    <style>
        @page {
            margin: 11mm 12mm 14mm 12mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 8.5pt;
            color: #000000;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            padding-bottom: 5px;
            margin-bottom: 6px;
            border-bottom: 2.5px solid #1a4f8a;
        }
        .header h1 {
            font-size: 12pt;
            font-weight: bold;
            color: #1a4f8a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin: 0 0 3px 0;
        }
        .header .tagline {
            font-size: 7.5pt;
            color: #000000;
            font-style: italic;
            margin: 0;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 6px 0;
            font-size: 8pt;
        }
        .meta-table td {
            padding: 1px 0;
            vertical-align: top;
            color: #000000;
        }
        .meta-label {
            width: 34%;
            font-weight: bold;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .meta-value {
            width: 66%;
        }
        .meta-value.uppercase {
            text-transform: uppercase;
        }
        .meta-value.study {
            font-weight: bold;
            font-style: italic;
            text-transform: uppercase;
        }
        .meta-value.datetime {
            text-transform: none;
        }
        .divider {
            border-top: 1px solid #1a4f8a;
            margin: 5px 0;
        }
        .findings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .findings-table td {
            padding: 0 0 2px 0;
            vertical-align: top;
            color: #000000;
        }
        .finding-label {
            width: 22%;
            font-weight: bold;
            padding-right: 6px;
            white-space: nowrap;
            font-size: 8pt;
            text-transform: uppercase;
        }
        .finding-text {
            width: 78%;
            text-align: justify;
            line-height: 1.18;
            font-size: 7.5pt;
            text-transform: none;
        }
        .findings-narrative {
            margin-top: 2px;
            font-size: 7.5pt;
            color: #000000;
            text-align: justify;
            line-height: 1.18;
            white-space: pre-wrap;
        }
        .findings-narrative .intro {
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            font-size: 8pt;
            display: block;
            margin-bottom: 4px;
        }
        .impression-block {
            margin-top: 8px;
        }
        .impression-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            color: #000000;
            margin: 0 0 4px 0;
        }
        .impression-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .impression-list li {
            font-weight: bold;
            margin: 0 0 2px 0;
            font-size: 7.5pt;
            color: #000000;
            text-align: justify;
            line-height: 1.18;
        }
        .signature {
            margin-top: 16px;
            text-align: center;
            page-break-inside: avoid;
        }
        .signature .name {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
            margin: 0;
        }
        .signature .specialty {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
            margin: 2px 0 0 0;
        }
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 2.5px solid #1a4f8a;
            padding-top: 5px;
            font-size: 7.5pt;
            color: #000000;
        }
        .page-footer table {
            width: 100%;
            border-collapse: collapse;
        }
        .page-footer td {
            vertical-align: middle;
        }
        .footer-address {
            width: 75%;
            line-height: 1.2;
        }
        .footer-brand {
            width: 25%;
            text-align: right;
            font-weight: bold;
            color: #1a4f8a;
            font-size: 9pt;
        }
        .body-content {
            margin-bottom: 18mm;
        }
    </style>
</head>
<body>
    <div class="body-content">
        <header class="header">
            <h1>{{ $centerName }}</h1>
            <p class="tagline">"{{ $tagline }}"</p>
        </header>

        <table class="meta-table">
            <tr>
                <td class="meta-label">Apellidos y nombres:</td>
                <td class="meta-value uppercase">{{ $patientName }}</td>
            </tr>
            <tr>
                <td class="meta-label">Edad:</td>
                <td class="meta-value">{{ $patientAge }} años</td>
            </tr>
            <tr>
                <td class="meta-label">Estudio:</td>
                <td class="meta-value study">{{ $studyName }}</td>
            </tr>
            <tr>
                <td class="meta-label">Solicitado por:</td>
                <td class="meta-value uppercase">{{ $origin }}</td>
            </tr>
            <tr>
                <td class="meta-label">Fecha y hora:</td>
                <td class="meta-value datetime">{{ $attentionDateTime }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        @if ($formatType === 'narrative')
            @foreach ($sections as $section)
                <div class="findings-narrative">
                    @if (!empty($section['intro']))
                        <span class="intro">{{ $section['intro'] }}</span>
                    @endif
                    {!! nl2br(e($section['content'])) !!}
                </div>
            @endforeach
        @else
            <table class="findings-table">
                @foreach ($sections as $section)
                    <tr>
                        <td class="finding-label">{{ $section['title'] }}:</td>
                        <td class="finding-text">{!! nl2br(e($section['content'])) !!}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        @if (count($impressionItems) > 0)
            <div class="impression-block">
                <p class="impression-title">Impresión diagnóstica:</p>
                <ul class="impression-list">
                    @foreach ($impressionItems as $item)
                        <li>{{ $item['number'] }}. {{ $item['text'] }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="signature">
            <p class="name">{{ $physicianName }}</p>
            <p class="specialty">{{ $physicianSpecialty }}</p>
        </div>
    </div>

    <div class="page-footer">
        <table>
            <tr>
                <td class="footer-address">
                    <strong>Dirección:</strong> {{ $clinicAddress }}
                    @if ($clinicPhone)
                        &nbsp;&nbsp;|&nbsp;&nbsp; <strong>Tel.:</strong> {{ $clinicPhone }}
                    @endif
                </td>
                <td class="footer-brand">{{ $clinicName }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
