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
        html,
        body {
            width: 100%;
            min-height: 100%;
        }
        body { 
            margin: 0; 
            padding: 0; 
            background: linear-gradient(180deg, #0f0518 0%, #1a0a3e 100%); 
            color: #e2e8f0; 
            font-family: 'Roboto', sans-serif;
            min-height: 100vh;
            overflow: hidden;
        }
        .public-screen-shell {
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr auto;
        }
        .public-header {
            background: rgba(255,255,255,0.05);
            padding: 12px 40px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }
        .public-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fbbf24;
            line-height: 1.1;
        }
        .public-logo {
            width: 140px;
            height: 50px;
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
            white-space: nowrap;
        }
        .public-content {
            min-height: 0;
            padding: 22px 40px 16px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(280px, 360px);
            gap: 24px;
            align-items: stretch;
        }
        .draw-panel,
        .side-panel {
            min-height: 0;
        }
        .last-number-section {
            text-align: center;
            padding: 10px 0 18px;
        }
        .last-number-label {
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: rgba(255,255,255,0.5);
            margin-bottom: 8px;
        }
        .last-number-value {
            font-size: clamp(5rem, 12vw, 9rem);
            font-weight: 900;
            color: #fbbf24;
            text-shadow: 0 0 60px rgba(251, 191, 36, 0.5);
            line-height: 1;
        }
        .number-grid {
            display: grid;
            grid-template-columns: repeat(15, 1fr);
            gap: 6px;
            margin-top: 10px;
        }
        .number-grid-item {
            min-height: clamp(48px, 6.4vh, 78px);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-weight: 700;
            font-size: clamp(0.85rem, 1.2vw, 1.2rem);
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
            height: 100%;
        }
        .close-card {
            height: 100%;
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 20px;
            border: 2px solid rgba(251,191,36,0.45);
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .close-card h3 {
            color: #fbbf24;
            font-size: 1.05rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 14px;
            line-height: 1.3;
        }
        .close-list {
            overflow: hidden;
        }
        .close-row {
            padding: 14px;
            margin-bottom: 12px;
            border-radius: 12px;
            background: rgba(15, 5, 24, 0.55);
            border: 1px solid rgba(251,191,36,0.25);
        }
        .close-row-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: #fff;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .close-row-badge {
            color: #0f0518;
            background: #fbbf24;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 0.8rem;
            font-weight: 900;
        }
        .missing-numbers {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .missing-number {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            font-weight: 900;
            background: #fbbf24;
            color: #0f0518;
            box-shadow: 0 0 18px rgba(251,191,36,0.45);
        }
        .empty-close {
            height: 100%;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.035);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 24px;
            color: rgba(255,255,255,0.55);
            font-weight: 600;
        }
        .footer-message {
            background: linear-gradient(90deg, #4c1d95 0%, #7c3aed 100%);
            padding: 12px 40px;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 500;
        }
        @media (max-width: 1100px) {
            body { overflow: auto; }
            .public-screen-shell { min-height: 100vh; }
            .public-header {
                align-items: flex-start;
                flex-direction: column;
                padding: 16px;
            }
            .public-header .info {
                flex-wrap: wrap;
                gap: 10px 18px;
                white-space: normal;
            }
            .public-content {
                grid-template-columns: 1fr;
                padding: 18px 16px;
            }
            .close-card,
            .empty-close { height: auto; }
            .close-list { overflow: visible; }
        }
        @media (max-width: 768px) {
            .number-grid {
                grid-template-columns: repeat(5, 1fr);
                gap: 6px;
            }
            .number-grid-item {
                min-height: 48px;
                font-size: 0.95rem;
            }
            .last-number-value { font-size: 5rem; }
            .public-logo { width: 118px; height: 44px; }
            .public-header h1 { font-size: 1.15rem; }
            .footer-message {
                padding: 12px 16px;
                font-size: 0.95rem;
            }
        }
    </style>
    @livewireStyles
</head>
<body>
    <div class="public-screen-shell">
        <div class="public-header">
            <div style="display: flex; align-items: center;">
                <img src="{{ asset('material/img/fenix-logo.png') }}" class="public-logo" alt="Fênix Motocenter">
                <h1>{{ $bingo->name }}</h1>
            </div>
            <div class="info">
                <div><i class="material-icons" style="font-size: 18px; vertical-align: middle;">event</i> {{ $bingo->event_date->format('d/m/Y') }}</div>
                <div><i class="material-icons" style="font-size: 18px; vertical-align: middle;">access_time</i> {{ \Carbon\Carbon::parse($bingo->event_time)->format('H:i') }}</div>
                <div id="public-round-label">RODADA {{ $round?->round_number ?? 1 }} DE {{ $bingo->round_quantity }}</div>
                <div><span style="color: #10b981;">●</span> EM ANDAMENTO</div>
            </div>
        </div>

        <div class="public-content">
            <main class="draw-panel">
                <div class="last-number-section">
                    <div class="last-number-label">Último Número Sorteado</div>
                    <div id="last-number-value" class="last-number-value" style="{{ $lastDrawn ? '' : 'color: rgba(255,255,255,0.2);' }}">
                        {{ $lastDrawn ? str_pad($lastDrawn->number, 2, '0', STR_PAD_LEFT) : '--' }}
                    </div>
                    <div id="last-number-time" style="margin-top: 8px; color: rgba(255,255,255,0.5);">
                        @if($lastDrawn)
                            Sorteado às {{ $lastDrawn->drawn_at->format('H:i:s') }}
                        @else
                            Aguarde o início do sorteio
                        @endif
                    </div>
                </div>

                <div class="number-grid" id="number-grid">
                    @for($i = $bingo->number_range_start; $i <= $bingo->number_range_end; $i++)
                        <div class="number-grid-item {{ in_array($i, $drawnNumbersList) ? 'drawn' : '' }} {{ $lastDrawn && $lastDrawn->number == $i ? 'recent' : '' }}" data-number="{{ $i }}">
                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                        </div>
                    @endfor
                </div>
            </main>

            <aside class="side-panel" id="close-panel">
                @if(count($possibleWinners) > 0)
                    <div class="close-section">
                        <div class="close-card">
                            <h3><i class="material-icons" style="font-size: 24px; vertical-align: middle;">campaign</i> Tem cartela perto de bater</h3>
                            <div class="close-list">
                                @foreach($possibleWinners as $missingNumbers)
                                    <div class="close-row">
                                        <div class="close-row-title">
                                            <span>Faltando</span>
                                            <span class="close-row-badge">{{ count($missingNumbers) }}</span>
                                        </div>
                                        <div class="missing-numbers">
                                            @foreach($missingNumbers as $number)
                                                <div class="missing-number">{{ str_pad($number, 2, '0', STR_PAD_LEFT) }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="empty-close">
                        Nenhuma cartela perto de bater no momento
                    </div>
                @endif
            </aside>
        </div>

        <div class="footer-message">
            <i class="material-icons" style="font-size: 20px; vertical-align: middle; margin-right: 10px;">campaign</i>
            Boa sorte a todos! Acompanhe o sorteio e boa sorte!
        </div>
    </div>

    @livewireScripts
    <script>
        (function() {
            const stateUrl = @json(route('public.screen.state', $bingo));
            const streamUrl = @json(route('public.screen.stream', $bingo));
            const numberGrid = document.getElementById('number-grid');
            const lastNumberValue = document.getElementById('last-number-value');
            const lastNumberTime = document.getElementById('last-number-time');
            const roundLabel = document.getElementById('public-round-label');
            const closePanel = document.getElementById('close-panel');
            let lastSignature = '';
            let isUpdating = false;
            let fallbackTimer = null;

            function padNumber(number) {
                return String(number).padStart(2, '0');
            }

            function renderClosePanel(possibleWinners) {
                if (!possibleWinners.length) {
                    closePanel.innerHTML = '<div class="empty-close">Nenhuma cartela perto de bater no momento</div>';
                    return;
                }

                const rows = possibleWinners.map(function(missingNumbers) {
                    const numbers = missingNumbers.map(function(number) {
                        return '<div class="missing-number">' + padNumber(number) + '</div>';
                    }).join('');

                    return '<div class="close-row">' +
                        '<div class="close-row-title">' +
                            '<span>Faltando</span>' +
                            '<span class="close-row-badge">' + missingNumbers.length + '</span>' +
                        '</div>' +
                        '<div class="missing-numbers">' + numbers + '</div>' +
                    '</div>';
                }).join('');

                closePanel.innerHTML = '<div class="close-section">' +
                    '<div class="close-card">' +
                        '<h3><i class="material-icons" style="font-size: 24px; vertical-align: middle;">campaign</i> Tem cartela perto de bater</h3>' +
                        '<div class="close-list">' + rows + '</div>' +
                    '</div>' +
                '</div>';
            }

            function applyState(state) {
                const signature = JSON.stringify({
                    round: state.round ? state.round.id : null,
                    prize: state.round ? state.round.prize : null,
                    drawn: state.drawn_numbers,
                    last: state.last_drawn,
                    winners: state.possible_winners,
                });

                if (signature === lastSignature) {
                    return;
                }

                lastSignature = signature;

                if (state.round) {
                    roundLabel.textContent = 'RODADA ' + state.round.number + ' DE ' + state.bingo.round_quantity;
                }

                const drawnSet = new Set(state.drawn_numbers);
                const lastNumber = state.last_drawn ? state.last_drawn.number : null;
                numberGrid.querySelectorAll('.number-grid-item').forEach(function(item) {
                    const number = Number(item.dataset.number);
                    item.classList.toggle('drawn', drawnSet.has(number));
                    item.classList.toggle('recent', lastNumber === number);
                });

                if (state.last_drawn) {
                    lastNumberValue.textContent = padNumber(state.last_drawn.number);
                    lastNumberValue.style.color = '';
                    lastNumberTime.textContent = 'Sorteado às ' + state.last_drawn.time;
                } else {
                    lastNumberValue.textContent = '--';
                    lastNumberValue.style.color = 'rgba(255,255,255,0.2)';
                    lastNumberTime.textContent = 'Aguarde o início do sorteio';
                }

                renderClosePanel(state.possible_winners);
            }

            async function updateScreen() {
                if (isUpdating) {
                    return;
                }

                isUpdating = true;

                try {
                    const response = await fetch(stateUrl, {
                        cache: 'no-store',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Erro ao atualizar tela');
                    }

                    applyState(await response.json());
                } catch (error) {
                    console.warn(error);
                } finally {
                    isUpdating = false;
                }
            }

            function startFallback() {
                if (fallbackTimer) {
                    return;
                }

                updateScreen();
                fallbackTimer = setInterval(updateScreen, 2500);
            }

            if ('EventSource' in window) {
                const stream = new EventSource(streamUrl);

                stream.addEventListener('screen-state', function(event) {
                    applyState(JSON.parse(event.data));
                });

                stream.onerror = function() {
                    stream.close();
                    startFallback();
                };
            } else {
                startFallback();
            }
        })();
    </script>
</body>
</html>
