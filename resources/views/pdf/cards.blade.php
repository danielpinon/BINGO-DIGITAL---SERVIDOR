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
            width: 48mm;
            height: auto;
            margin-bottom: 1mm;
        }
        .pdf-title {
            color: #1e3a8a;
            font-size: 16px;
            font-weight: bold;
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
            border: 2px solid #4c1d95;
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
            background: #4c1d95;
            color: #fff;
            padding: 8px 12px;
            text-align: center;
        }
        .card-header .title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .card-header .number { font-size: 18px; font-weight: bold; }
        .card-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 2px;
            padding: 8px;
        }
        .cell {
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            height: 32px;
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
    @endphp

    @foreach($bingo->cards as $card)
        <div class="page">
            <div class="pdf-header">
                <img src="{{ public_path('material/img/fenix-logo.png') }}" class="pdf-logo" alt="Fênix Motocenter">
                <div class="pdf-title">{{ $bingo->name }}</div>
                <div class="pdf-subtitle">
                    {{ $bingo->round_quantity }} rodadas usando as mesmas cartelas |
                    {{ $copiesPerPage }} {{ $copiesPerPage === 1 ? 'cartela' : 'cartelas' }} por página
                </div>
            </div>

            <div class="copies">
                @for($copy = 1; $copy <= $copiesPerPage; $copy++)
                    <div class="card-container copy-count-{{ $copiesPerPage }}">
                        <div class="card-header">
                            <div class="title">{{ $bingo->name }}</div>
                            <div class="number">CARTELA {{ $card->card_number }}</div>
                        </div>
                        <table class="card-grid">
                            @foreach($card->grid as $row => $cols)
                                <tr>
                                    @foreach($cols as $col => $number)
                                        <td class="cell">{{ str_pad($number, 2, '0', STR_PAD_LEFT) }}</td>
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
