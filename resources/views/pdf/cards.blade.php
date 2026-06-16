<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cartelas - {{ $bingo->name }}</title>
    <style>
        @page { margin: 8mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #fff; }
        .page { width: 100%; page-break-after: always; }
        .page:last-child { page-break-after: auto; }
        .pdf-header {
            text-align: center;
            margin-bottom: 5mm;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3mm;
        }
        .pdf-logo {
            display: block;
            width: 48mm;
            height: auto;
            margin: 0 auto 2mm;
        }
        .pdf-title {
            color: #111827;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 1mm;
        }
        .pdf-subtitle {
            color: #64748b;
            font-size: 10px;
        }
        .copies { width: 100%; }
        .card-container {
            float: left;
            margin: 1%;
            border: 1px solid #111827;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .copy-count-1 { width: 84%; margin: 8mm 8%; }
        .copy-count-2 { width: 48%; }
        .copy-count-3,
        .copy-count-5,
        .copy-count-6 { width: 31.333%; }
        .copy-count-4 { width: 48%; }
        .card-header {
            background: #fff;
            color: #111827;
            padding: 5px 8px 0;
            border-bottom: 1px solid #111827;
        }
        .card-meta {
            font-size: 9px;
            font-weight: bold;
            line-height: 1.1;
            overflow: hidden;
        }
        .card-meta .left { float: left; width: 32%; text-align: left; }
        .card-meta .right { float: right; width: 64%; text-align: center; text-transform: uppercase; }
        .card-header .bingo-title {
            clear: both;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 30px;
            line-height: 1;
            letter-spacing: 8px;
            text-align: center;
            padding: 2px 0 3px 8px;
            border-top: 1px solid #111827;
        }
        .card-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            padding: 0;
        }
        .cell {
            border-right: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            height: 40px;
            width: 20%;
        }
        .cell:nth-child(5) { border-right: 0; }
        .card-grid tr:last-child .cell { border-bottom: 0; }
        .center-logo {
            max-width: 70%;
            max-height: 31px;
            width: auto;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .card-footer {
            padding: 5px 12px;
            text-align: center;
            font-size: 10px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .clear { clear: both; }
    </style>
</head>
<body>
    @php
        $copiesPerPage = max(1, min((int) ($bingo->cards_per_page ?? 1), 6));
        $cardLogoPath = $bingo->card_logo_path ? storage_path('app/public/' . $bingo->card_logo_path) : null;
        $hasCardLogo = $cardLogoPath && file_exists($cardLogoPath);
        $cardTitle = trim($bingo->card_title ?: 'BINGO');
    @endphp

    @foreach($bingo->cards as $card)
        <div class="page">
            <div class="pdf-header">
                <img src="{{ public_path('material/img/fenix-logo.png') }}" class="pdf-logo" alt="Fênix Motocenter">
                <div class="pdf-title">B I N G O</div>
                <div class="pdf-subtitle">
                    {{ $bingo->name }} |
                    {{ $bingo->round_quantity }} rodadas usando as mesmas cartelas |
                    {{ $copiesPerPage }} {{ $copiesPerPage === 1 ? 'cartela' : 'cartelas' }} por página
                </div>
            </div>

            <div class="copies">
                @for($copy = 1; $copy <= $copiesPerPage; $copy++)
                    <div class="card-container copy-count-{{ $copiesPerPage }}">
                        <div class="card-header">
                            <div class="card-meta">
                                <span class="left">N° {{ $card->card_number }}</span>
                                <span class="right">{{ $bingo->name }}</span>
                            </div>
                            <div class="bingo-title">{{ $cardTitle }}</div>
                        </div>
                        <table class="card-grid">
                            @foreach($card->grid as $row => $cols)
                                <tr>
                                    @foreach($cols as $col => $number)
                                        <td class="cell">
                                            @if($hasCardLogo && (int) $row === 2 && (int) $col === 2)
                                                <img src="{{ $cardLogoPath }}" class="center-logo" alt="Logo">
                                            @else
                                                {{ str_pad($number, 2, '0', STR_PAD_LEFT) }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </table>
                        @if($card->responsible)
                            <div class="card-footer">
                                Responsável: {{ $card->responsible->name }}
                            </div>
                        @endif
                    </div>

                    @if(($copiesPerPage === 4 && $copy === 2) || ($copiesPerPage === 6 && $copy === 3))
                        <div class="clear"></div>
                    @endif
                @endfor
                <div class="clear"></div>
            </div>
        </div>
    @endforeach
</body>
</html>
