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
            left: 145mm;
            top: 14.7mm;
            width: 49mm;
            height: 17mm;
            z-index: 1;
            font-size: 18pt;
            font-weight: 800;
            line-height: 17mm;
            text-align: center;
        }
        .card-number-bottom {
            position: absolute;
            left: 145mm;
            top: 247.5mm;
            width: 47mm;
            height: 11.5mm;
            z-index: 1;
            font-size: 16pt;
            font-weight: 800;
            line-height: 11.5mm;
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
        $templatePath = $bingo->card_template_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($bingo->card_template_path)
            ? \Illuminate\Support\Facades\Storage::disk('public')->path($bingo->card_template_path)
            : public_path('material/img/bingo-ticket-template.jpeg');
        $mainX = [37.7, 51.8, 66.0, 80.2, 94.4];
        $mainY = [94.2, 107.2, 120.2, 133.2, 146.2];
        $leftX = [22.7, 33.0, 43.4, 53.7, 64.1];
        $leftY = [193.2, 202.4, 211.4, 220.5, 229.6];
        $rightX = [110.5, 120.9, 131.3, 141.7, 152.1];
        $rightY = [168.8, 177.9, 187.0, 196.1, 205.2];
    @endphp

    @foreach($bingo->cards as $card)
        @php $grid = $card->grid; @endphp
        <div class="ticket">
            <img src="{{ $templatePath }}" class="template-bg" alt="">
            <div class="card-number">{{ $card->card_number }}</div>
            <div class="card-number-bottom">{{ $card->card_number }}</div>

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
