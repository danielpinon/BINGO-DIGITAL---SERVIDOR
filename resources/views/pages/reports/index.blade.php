@extends('layouts.app', ['activePage' => 'reports', 'titlePage' => __('Relatórios')])

@section('content')
<div class="content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <h3>Relatórios</h3>
                    <p>Visão geral e análise dos bingos</p>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('reports.index') }}" method="GET" class="row align-items-end">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label class="form-label">Filtrar por Bingo</label>
                                    <select name="bingo_id" class="form-control">
                                        <option value="">Todos os bingos</option>
                                        @foreach($bingos as $bingo)
                                            <option value="{{ $bingo->id }}" {{ $bingoId == $bingo->id ? 'selected' : '' }}>{{ $bingo->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">Filtrar</button>
                                <a href="{{ route('reports.index') }}" class="btn btn-outline-primary">Limpar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- General Stats -->
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="card card-stats">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon"><i class="material-icons">event</i></div>
                        <p class="card-category">Bingos</p>
                        <h3 class="card-title">{{ $generalStats['total_bingos'] }}</h3>
                    </div>
                    <div class="card-footer"><div class="stats">Total de bingos cadastrados</div></div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card card-stats">
                    <div class="card-header card-header-success card-header-icon">
                        <div class="card-icon"><i class="material-icons">grid_on</i></div>
                        <p class="card-category">Cartelas</p>
                        <h3 class="card-title">{{ $generalStats['total_cards'] }}</h3>
                    </div>
                    <div class="card-footer"><div class="stats">Total de cartelas geradas</div></div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card card-stats">
                    <div class="card-header card-header-warning card-header-icon">
                        <div class="card-icon"><i class="material-icons">emoji_events</i></div>
                        <p class="card-category">Ganhadores</p>
                        <h3 class="card-title">{{ $generalStats['total_winners'] }}</h3>
                    </div>
                    <div class="card-footer"><div class="stats">Total de ganhadores confirmados</div></div>
                </div>
            </div>
        </div>

        <!-- Card Status -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Status das Cartelas</h4>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--bingo-primary);">{{ $cardStatusStats['available'] }}</div>
                                <small class="text-muted">Disponíveis</small>
                            </div>
                            <div class="col-4">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--bingo-success);">{{ $cardStatusStats['distributed'] }}</div>
                                <small class="text-muted">Distribuídas</small>
                            </div>
                            <div class="col-4">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--bingo-warning);">{{ $cardStatusStats['returned'] }}</div>
                                <small class="text-muted">Devolvidas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-info">
                        <h4 class="card-title">Cartelas por Responsável (Top 5)</h4>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Responsável</th><th class="text-right">Cartelas</th></tr></thead>
                            <tbody>
                                @forelse($cardsByResponsible as $r)
                                    <tr>
                                        <td>{{ $r->name }}</td>
                                        <td class="text-right font-weight-bold">{{ $r->cards_count }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted">Nenhum responsável com cartelas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards by Bingo & Finished Bingos -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Cartelas por Bingo (Top 10)</h4>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Bingo</th><th class="text-right">Cartelas</th></tr></thead>
                            <tbody>
                                @forelse($cardsByBingo as $b)
                                    <tr>
                                        <td>{{ $b->name }}</td>
                                        <td class="text-right font-weight-bold">{{ $b->cards_count }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted">Nenhum bingo com cartelas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header card-header-success">
                        <h4 class="card-title">Bingos Finalizados</h4>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Bingo</th><th class="text-right">Cartelas</th><th class="text-right">Ganhadores</th></tr></thead>
                            <tbody>
                                @forelse($finishedBingos as $b)
                                    <tr>
                                        <td>{{ $b->name }}</td>
                                        <td class="text-right">{{ $b->cards_count }}</td>
                                        <td class="text-right font-weight-bold">{{ $b->winners_count }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">Nenhum bingo finalizado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Draws -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-info">
                        <h4 class="card-title">Últimos Sorteios</h4>
                    </div>
                    <div class="card-body">
                        @forelse($recentDraws as $bingo)
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="m-0">{{ $bingo->name }}</h5>
                                    <span class="badge badge-primary">{{ $bingo->drawn_numbers_count }} números sorteados</span>
                                </div>
                                <div class="d-flex flex-wrap" style="gap: 6px;">
                                    @foreach($bingo->drawnNumbers as $dn)
                                        <div class="number-ball" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                            {{ str_pad($dn->number, 2, '0', STR_PAD_LEFT) }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3">Nenhum sorteio realizado ainda.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Bingo-specific report -->
        @if($bingoReport)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Relatório do Bingo: {{ $bingoReport->name }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-3 text-center">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--bingo-primary);">{{ $bingoReport->cards_count }}</div>
                                <small class="text-muted">Cartelas</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--bingo-success);">{{ $bingoReport->drawn_numbers_count }}</div>
                                <small class="text-muted">Números Sorteados</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--bingo-warning);">{{ $bingoReport->winners_count ?? $bingoReport->winners->count() }}</div>
                                <small class="text-muted">Ganhadores</small>
                            </div>
                            <div class="col-md-3 text-center">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--bingo-purple);">{{ $bingoReport->prizePatterns->count() }}</div>
                                <small class="text-muted">Padrões de Prêmio</small>
                            </div>
                        </div>

                        <h5 class="mb-3">Padrões de Prêmio</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-hover">
                                <thead><tr><th>Nome</th><th>Tipo</th><th>Ordem</th><th>Status</th></tr></thead>
                                <tbody>
                                    @foreach($bingoReport->prizePatterns as $pattern)
                                        <tr>
                                            <td>{{ $pattern->name }}</td>
                                            <td>{{ $pattern->pattern_type }}</td>
                                            <td>{{ $pattern->pattern_order }}</td>
                                            <td>
                                                @if($pattern->is_completed)
                                                    <span class="badge badge-success">Concluído</span>
                                                @else
                                                    <span class="badge badge-primary">Pendente</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($bingoReport->winners->count() > 0)
                        <h5 class="mb-3">Ganhadores Confirmados</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>Cartela</th><th>Responsável</th><th>Padrão</th></tr></thead>
                                <tbody>
                                    @foreach($bingoReport->winners as $winner)
                                        <tr>
                                            <td>{{ $winner->card->card_number ?? '-' }}</td>
                                            <td>{{ $winner->responsible->name ?? '-' }}</td>
                                            <td>{{ $winner->prizePattern->name ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
