<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cartelas - {{ $bingo->name }}</title>
    <style>
        @page { margin: 0; size: A4 portrait; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; background: #fff; color: #111; }
        .ticket {
            position: relative;
            width: 210mm;
            height: 297mm;
            page-break-after: always;
            overflow: hidden;
        }
        .ticket:last-child { page-break-after: auto; }
        .template-bg {
            position: absolute;
            left: 0;
            top: 0;
            width: 210mm;
            height: 297mm;
            z-index: 0;
        }
        .card-number {
            position: absolute;
            left: 147mm;
            top: 22mm;
            width: 44mm;
            height: 10mm;
            z-index: 1;
            font-size: 12pt;
            font-weight: 700;
            line-height: 10mm;
            text-align: center;
        }
        .num {
            position: absolute;
            z-index: 1;
            width: 10mm;
            height: 7mm;
            margin-left: -5mm;
            margin-top: -3.5mm;
            font-size: 18pt;
            line-height: 7mm;
            font-weight: 800;
            text-align: center;
            color: #111;
        }
        .num-small {
            width: 8mm;
            height: 5.5mm;
            margin-left: -4mm;
            margin-top: -2.75mm;
            font-size: 12pt;
            line-height: 5.5mm;
            font-weight: 800;
        }
    </style>
</head>
<body>
    @php
        $templatePath = public_path('material/img/bingo-ticket-template.jpeg');
        $mainX = [34.0, 49.0, 63.8, 78.7, 93.6];
        $mainY = [94.2, 107.2, 120.2, 133.2, 146.2];
        $leftX = [19.7, 30.0, 40.4, 50.7, 61.1];
        $leftY = [201.8, 211.0, 220.0, 229.1, 238.2];
        $rightX = [114.1, 124.7, 135.3, 145.9, 156.4];
        $rightY = [181.8, 190.9, 200.0, 209.1, 218.2];
    @endphp

    @foreach($bingo->cards as $card)
        @php $grid = $card->grid; @endphp
        <div class="ticket">
            <img src="{{ $templatePath }}" class="template-bg" alt="">
            <div class="card-number">N° {{ $card->card_number }}</div>

            @foreach($grid as $row => $cols)
                @foreach($cols as $col => $number)
                    <div class="num" style="left: {{ $mainX[(int) $col] }}mm; top: {{ $mainY[(int) $row] }}mm;">
                        {{ str_pad($number, 2, '0', STR_PAD_LEFT) }}
                    </div>
                    <div class="num num-small" style="left: {{ $leftX[(int) $col] }}mm; top: {{ $leftY[(int) $row] }}mm;">
                        {{ str_pad($number, 2, '0', STR_PAD_LEFT) }}
                    </div>
                    <div class="num num-small" style="left: {{ $rightX[(int) $col] }}mm; top: {{ $rightY[(int) $row] }}mm;">
                        {{ str_pad($number, 2, '0', STR_PAD_LEFT) }}
                    </div>
                @endforeach
            @endforeach
        </div>
    @endforeach
</body>
</html>
