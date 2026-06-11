<div>
    <div class="row">
        <!-- Left Panel - Draw Controls -->
        <div class="col-lg-8">
            <div class="card">
                    <div class="card-header card-header-primary d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title">Sistema de Sorteio</h4>
                        <p class="card-category">
                            {{ $bingo->name }} · Rodada {{ $roundNumber }} de {{ $roundQuantity }}
                            @if($currentPrizeName)
                                · Prêmio: {{ $currentPrizeName }}
                            @endif
                        </p>
                    </div>
                    <div class="d-flex align-items-center" style="gap: 10px;">
                            <span class="badge badge-success">Rodada {{ $roundNumber }} em andamento</span>
                        <form action="{{ route('bingos.finish', $bingo) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Deseja finalizar o bingo?')">
                                <i class="material-icons">stop</i> Finalizar
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Last Drawn Number -->
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <small class="text-muted">ÚLTIMO NÚMERO SORTEADO</small>
                            </div>
                            @if($lastNumber)
                            <div class="number-ball recent mx-auto" style="width: 120px; height: 120px; font-size: 3rem;">
                                {{ str_pad($lastNumber, 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <div class="mt-3">
                            <button wire:click="undoLast" wire:loading.attr="disabled" class="btn btn-outline-primary btn-sm">
                                <i class="material-icons" style="font-size: 16px;">undo</i> Desfazer
                            </button>
                            </div>
                            @else
                            <div class="number-ball mx-auto" style="width: 120px; height: 120px; font-size: 1.5rem; background: #e2e8f0; color: #94a3b8;">
                                --
                            </div>
                            @endif
                        </div>

                        <!-- Draw Button -->
                        <div class="col-md-4 text-center">
                            <button wire:click="drawNumber" wire:loading.attr="disabled" class="btn btn-primary btn-lg" style="padding: 20px 40px; font-size: 1.2rem;">
                                <i class="material-icons" style="font-size: 28px;">casino</i>
                                <br>Sortear Número
                            </button>
                        </div>

                        <!-- Manual Input -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Controle Manual</label>
                                <div class="input-group">
                                    <input type="number" wire:model="manualNumber" class="form-control" placeholder="Nº" min="{{ $bingo->number_range_start }}" max="{{ $bingo->number_range_end }}">
                                    <div class="input-group-append">
                                        <button type="button" wire:click="addManualNumber" wire:loading.attr="disabled" class="btn btn-primary">Adicionar</button>
                                    </div>
                                </div>
                                @error('manualNumber')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Drawn Numbers Grid -->
                    <div class="row">
                        <div class="col-12">
                            <h5>Números Sorteados da Rodada {{ $roundNumber }} ({{ count($drawnNumbers) }})</h5>
                            <div class="d-flex flex-wrap mt-3" style="gap: 8px;">
                                @for($i = $bingo->number_range_start; $i <= $bingo->number_range_end; $i++)
                                    <div class="text-center" style="width: 40px;">
                                        <div class="{{ in_array($i, $drawnNumbers) ? 'number-ball' : '' }}" 
                                             style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.85rem; {{ in_array($i, $drawnNumbers) ? '' : 'background: #f1f5f9; color: #cbd5e1;' }} {{ $i == $lastNumber ? 'animation: bounce 0.5s ease;' : '' }}">
                                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Stats & Winners -->
        <div class="col-lg-4">
            <!-- Stats -->
            <div class="card">
                <div class="card-header card-header-primary">
                    <h4 class="card-title">Estatísticas</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Números Sorteados</span>
                        <strong>{{ $stats['drawn'] ?? 0 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Números Restantes</span>
                        <strong>{{ $stats['remaining'] ?? 0 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Cartelas em Jogo</span>
                        <strong>{{ $stats['totalCards'] ?? 0 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Possíveis Ganhadores / Perto de Bater</span>
                        <strong>{{ count($possibleWinners) }}</strong>
                    </div>
                </div>
            </div>

            <!-- Possible Winners -->
            @if(count($possibleWinners) > 0)
            <div class="card mt-4">
                <div class="card-header card-header-warning">
                    <h4 class="card-title"><i class="material-icons">emoji_events</i> Possíveis Ganhadores</h4>
                </div>
                <div class="card-body">
                    @foreach($possibleWinners as $winner)
                    <div class="mb-3 p-3" style="border: 2px solid {{ $winner['is_winner'] ? '#10b981' : '#f59e0b' }}; border-radius: 12px; background: {{ $winner['is_winner'] ? 'rgba(16,185,129,0.05)' : 'rgba(245,158,11,0.05)' }};">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Cartela: {{ $winner['card']->card_number }}</strong>
                            @if($winner['is_winner'])
                                <span class="badge badge-success">GANHADOR!</span>
                            @else
                                <span class="badge badge-warning">PERTO DE BATER (Faltam {{ count($winner['missing']) }})</span>
                            @endif
                        </div>
                        
                        @if($winner['card']->responsible)
                            <small class="text-muted">Resp: {{ $winner['card']->responsible->name }}</small>
                            <br>
                        @endif
                        
                        @if(!$winner['is_winner'])
                            <small>Faltando: {{ implode(', ', $winner['missing']) }}</small>
                        @else
                            <button class="btn btn-sm btn-success mt-2" wire:click="confirmWinner({{ $winner['card']->id }})" wire:loading.attr="disabled">
                                <i class="material-icons" style="font-size: 16px;">check</i> Validar Ganhador
                            </button>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
