<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tela Pública - {{ $bingo->name }}</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link href="{{ asset('material') }}/css/custom-bingo.css?v=1.0" rel="stylesheet" />
    <link href="{{ asset('material') }}/css/material-dashboard.css?v=2.1.1" rel="stylesheet" />
    <style>
        body { 
            margin: 0; 
            padding: 0; 
            background: linear-gradient(180deg, #0f0518 0%, #1a0a3e 100%); 
            color: #e2e8f0; 
            font-family: 'Roboto', sans-serif;
            min-height: 100vh;
        }
        .public-header {
            background: rgba(255,255,255,0.05);
            padding: 20px 40px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .public-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fbbf24;
        }
        .public-logo {
            width: 180px;
            height: 64px;
            object-fit: contain;
            border-radius: 8px;
            background: rgba(255,255,255,0.92);
            padding: 4px;
            margin-right: 14px;
        }
        .public-header .info {
            display: flex;
            gap: 30px;
            font-size: 0.9rem;
        }
        .public-content {
            padding: 30px 40px;
        }
        .last-number-section {
            text-align: center;
            padding: 30px;
        }
        .last-number-label {
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: rgba(255,255,255,0.5);
            margin-bottom: 20px;
        }
        .last-number-value {
            font-size: 10rem;
            font-weight: 900;
            color: #fbbf24;
            text-shadow: 0 0 60px rgba(251, 191, 36, 0.5);
            line-height: 1;
        }
        .number-grid {
            display: grid;
            grid-template-columns: repeat(15, 1fr);
            gap: 6px;
            margin-top: 30px;
        }
        .number-grid-item {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            background: rgba(255,255,255,0.03);
            border: 2px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.2);
        }
        .number-grid-item.drawn {
            background: linear-gradient(135deg, #7c3aed 0%, #4c1d95 100%);
            border-color: #7c3aed;
            color: #fff;
            box-shadow: 0 0 15px rgba(124, 58, 237, 0.4);
        }
        .number-grid-item.recent {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border-color: #fbbf24;
            color: #0f0518;
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.5);
        }
        .close-section {
            margin-top: 30px;
        }
        .close-card {
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 24px;
            border: 2px solid rgba(251,191,36,0.45);
            text-align: center;
        }
        .close-card h3 {
            color: #fbbf24;
            font-size: 1.25rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 15px;
        }
        .missing-numbers {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .missing-number {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            font-weight: 900;
            background: #fbbf24;
            color: #0f0518;
            box-shadow: 0 0 18px rgba(251,191,36,0.45);
        }
        .footer-message {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(90deg, #4c1d95 0%, #7c3aed 100%);
            padding: 15px 40px;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 500;
        }
        @media (max-width: 768px) {
            .number-grid { grid-template-columns: repeat(10, 1fr); }
            .last-number-value { font-size: 5rem; }
        }
    </style>
    @livewireStyles
</head>
<body>
    <div class="public-header">
        <div style="display: flex; align-items: center;">
            <img src="{{ asset('material/img/fenix-logo.png') }}" class="public-logo" alt="Fênix Motocenter">
            <h1>{{ $bingo->name }}</h1>
        </div>
        <div class="info">
            <div><i class="material-icons" style="font-size: 18px; vertical-align: middle;">event</i> {{ $bingo->event_date->format('d/m/Y') }}</div>
            <div><i class="material-icons" style="font-size: 18px; vertical-align: middle;">access_time</i> {{ \Carbon\Carbon::parse($bingo->event_time)->format('H:i') }}</div>
            <div>RODADA {{ $round?->round_number ?? 1 }} DE {{ $bingo->round_quantity }}</div>
            <div><span style="color: #10b981;">●</span> EM ANDAMENTO</div>
        </div>
    </div>

    <div class="public-content">
        <div class="last-number-section">
            <div class="last-number-label">Último Número Sorteado</div>
            @if($lastDrawn)
                <div class="last-number-value">{{ str_pad($lastDrawn->number, 2, '0', STR_PAD_LEFT) }}</div>
                <div style="margin-top: 15px; color: rgba(255,255,255,0.5);">
                    Sorteado às {{ $lastDrawn->drawn_at->format('H:i:s') }}
                </div>
            @else
                <div class="last-number-value" style="color: rgba(255,255,255,0.2);">--</div>
                <div style="margin-top: 15px; color: rgba(255,255,255,0.5);">Aguarde o início do sorteio</div>
            @endif
        </div>

        <div class="number-grid">
            @for($i = $bingo->number_range_start; $i <= $bingo->number_range_end; $i++)
                <div class="number-grid-item {{ in_array($i, $drawnNumbersList) ? 'drawn' : '' }} {{ $lastDrawn && $lastDrawn->number == $i ? 'recent' : '' }}">
                    {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                </div>
            @endfor
        </div>

        @if(count($possibleWinners) > 0)
        <div class="close-section">
            <div class="close-card">
                <h3><i class="material-icons" style="font-size: 24px; vertical-align: middle;">campaign</i> Tem cartela perto de bater</h3>
                @foreach($possibleWinners as $missingNumbers)
                    <div class="missing-numbers {{ !$loop->last ? 'mb-3' : '' }}">
                        @foreach($missingNumbers as $number)
                            <div class="missing-number">{{ str_pad($number, 2, '0', STR_PAD_LEFT) }}</div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="footer-message">
        <i class="material-icons" style="font-size: 20px; vertical-align: middle; margin-right: 10px;">campaign</i>
        Boa sorte a todos! Acompanhe o sorteio e boa sorte!
    </div>

    @livewireScripts
    <script>
        setTimeout(function() {
            window.location.reload();
        }, 5000);
    </script>
</body>
</html>
