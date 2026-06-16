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
        .copies {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .copy-slot {
            text-align: center;
            vertical-align: middle;
            padding: 2mm;
        }
        .card-container {
            display: inline-block;
            border: 1px solid #111827;
            border-radius: 8px;
            page-break-inside: avoid;
            background: #fff;
        }
        .copy-count-1 { width: 170mm; }
        .copy-count-2 { width: 170mm; }
        .copy-count-3 { width: 165mm; }
        .copy-count-4 { width: 91mm; }
        .copy-count-5,
        .copy-count-6 { width: 91mm; }
        .card-header {
            background: #fff;
            color: #111827;
            padding: 5px 8px 0;
            border-bottom: 1px solid #111827;
            height: 17mm;
        }
        .copy-count-4 .card-header,
        .copy-count-5 .card-header,
        .copy-count-6 .card-header { height: 14mm; padding: 3px 6px 0; }
        .card-meta {
            font-size: 9px;
            font-weight: bold;
            line-height: 1.1;
            overflow: hidden;
        }
        .copy-count-4 .card-meta,
        .copy-count-5 .card-meta,
        .copy-count-6 .card-meta { font-size: 7px; }
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
        .copy-count-3 .card-header .bingo-title { font-size: 24px; letter-spacing: 7px; }
        .copy-count-4 .card-header .bingo-title,
        .copy-count-5 .card-header .bingo-title,
        .copy-count-6 .card-header .bingo-title {
            font-size: 19px;
            letter-spacing: 5px;
            padding-left: 5px;
        }
        .card-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            padding: 0;
            table-layout: fixed;
        }
        .cell {
            border-right: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            width: 20%;
        }
        .copy-count-1 .cell { font-size: 26px; height: 18mm; }
        .copy-count-2 .cell { font-size: 26px; height: 15mm; }
        .copy-count-3 .cell { font-size: 20px; height: 9mm; }
        .copy-count-4 .cell,
        .copy-count-5 .cell,
        .copy-count-6 .cell { font-size: 15px; height: 8mm; }
        .cell.no-right { border-right: 0; }
        .cell.no-bottom { border-bottom: 0; }
        .center-logo-wrap {
            display: block;
            width: 100%;
            text-align: center;
            line-height: 1;
        }
        .center-logo {
            width: 30mm;
            max-width: 95%;
            max-height: 14mm;
            height: auto;
            display: inline-block;
            margin: 0 auto;
            vertical-align: middle;
        }
        .copy-count-2 .center-logo { width: 27mm; }
        .copy-count-3 .center-logo { width: 22mm; max-height: 10mm; }
        .copy-count-4 .center-logo,
        .copy-count-5 .center-logo,
        .copy-count-6 .center-logo { width: 17mm; max-height: 9mm; }
        .card-footer {
            padding: 5px 12px;
            text-align: center;
            font-size: 10px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .empty-slot { width: 91mm; height: 64mm; display: inline-block; }
        .clear { clear: both; }
    </style>
</head>
<body>
    @php
        $copiesPerPage = max(1, min((int) ($bingo->cards_per_page ?? 1), 6));
        $customCardLogoPath = $bingo->card_logo_path ? storage_path('app/public/' . $bingo->card_logo_path) : null;
        $defaultCardLogoPath = public_path('material/img/fenix-logo.png');
        $cardLogoPath = $customCardLogoPath && file_exists($customCardLogoPath) ? $customCardLogoPath : $defaultCardLogoPath;
        $hasCardLogo = $cardLogoPath && file_exists($cardLogoPath);
        $cardTitle = trim($bingo->card_title ?: 'BINGO');
        $columns = $copiesPerPage >= 4 ? 2 : 1;
        $rows = (int) ceil($copiesPerPage / $columns);
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

            <table class="copies">
                @for($slotRow = 0; $slotRow < $rows; $slotRow++)
                    <tr>
                        @for($slotCol = 0; $slotCol < $columns; $slotCol++)
                            @php $copy = ($slotRow * $columns) + $slotCol + 1; @endphp
                            <td class="copy-slot">
                                @if($copy <= $copiesPerPage)
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
                                                        <td class="cell {{ (int) $col === 4 ? 'no-right' : '' }} {{ (int) $row === 4 ? 'no-bottom' : '' }}">
                                                            @if($hasCardLogo && (int) $row === 2 && (int) $col === 2)
                                                                <span class="center-logo-wrap">
                                                                    <img src="{{ $cardLogoPath }}" class="center-logo" alt="Logo">
                                                                </span>
                                                            @else
                                                                {{ str_pad($number, 2, '0', STR_PAD_LEFT) }}
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                @else
                                    <span class="empty-slot"></span>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endfor
            </table>
        </div>
    @endforeach
</body>
</html>
