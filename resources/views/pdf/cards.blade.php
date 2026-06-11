<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cartelas - {{ $bingo->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #fff; }
        .page { width: 100%; padding: 10mm; page-break-after: always; }
        .pdf-header {
            text-align: center;
            margin-bottom: 8mm;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5mm;
        }
        .pdf-logo {
            width: 42mm;
            height: auto;
            margin-bottom: 2mm;
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
        .card-container { 
            width: 48%; 
            float: left; 
            margin: 1%; 
            border: 2px solid #4c1d95; 
            border-radius: 8px; 
            overflow: hidden;
            page-break-inside: avoid;
        }
        .card-header { 
            background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 100%); 
            color: #fff; 
            padding: 8px 12px; 
            text-align: center;
        }
        .card-header .title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .card-header .number { font-size: 18px; font-weight: bold; }
        .card-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 2px; padding: 8px; }
        .cell { 
            aspect-ratio: 1; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border: 1px solid #e2e8f0; 
            border-radius: 4px; 
            font-size: 14px; 
            font-weight: bold;
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
    <div class="page">
        <div class="pdf-header">
            <img src="{{ public_path('material/img/logo.png') }}" class="pdf-logo" alt="Logo">
            <div class="pdf-title">{{ $bingo->name }}</div>
            <div class="pdf-subtitle">
                {{ $bingo->round_quantity }} {{ $bingo->round_quantity == 1 ? 'rodada' : 'rodadas' }} usando as mesmas cartelas
            </div>
        </div>

        @foreach($bingo->cards as $index => $card)
            <div class="card-container">
                <div class="card-header">
                    <div class="title">{{ $bingo->name }}</div>
                    <div class="number">CARTELA {{ $card->card_number }}</div>
                </div>
                <div class="card-grid">
                    @foreach($card->grid as $row => $cols)
                        @foreach($cols as $col => $number)
                            <div class="cell">{{ str_pad($number, 2, '0', STR_PAD_LEFT) }}</div>
                        @endforeach
                    @endforeach
                </div>
                @if($card->responsible)
                <div class="card-footer">
                    Responsável: {{ $card->responsible->name }}
                </div>
                @endif
            </div>
            @if(($index + 1) % 4 == 0)
                <div class="clear"></div>
            @endif
        @endforeach
    </div>
</body>
</html>
