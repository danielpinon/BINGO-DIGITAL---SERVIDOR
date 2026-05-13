@extends('layouts.app', ['activePage' => 'dashboard', 'titlePage' => __('Dashboard')])

@section('content')
<div class="content">
    <div class="container-fluid">
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">event</i>
                        </div>
                        <p class="card-category">Bingos Cadastrados</p>
                        <h3 class="card-title">{{ $stats['total_bingos'] }}</h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">Total de bingos criados</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-success card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">grid_on</i>
                        </div>
                        <p class="card-category">Cartelas Geradas</p>
                        <h3 class="card-title">{{ $stats['total_cards'] }}</h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">Total de cartelas geradas</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-warning card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">people</i>
                        </div>
                        <p class="card-category">Responsáveis</p>
                        <h3 class="card-title">{{ $stats['total_responsibles'] }}</h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">Total de responsáveis</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-info card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">check_circle</i>
                        </div>
                        <p class="card-category">Bingos Finalizados</p>
                        <h3 class="card-title">{{ $stats['finished_bingos'] }}</h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">Total de bingos finalizados</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Ongoing Bingos -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Bingos em Andamento</h4>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Data</th>
                                    <th>Cartelas</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ongoingBingos as $bingo)
                                <tr>
                                    <td><strong>{{ $bingo->name }}</strong></td>
                                    <td>{{ $bingo->event_date->format('d/m/Y') }}</td>
                                    <td>{{ $bingo->cards_count }}</td>
                                    <td>{!! $bingo->status_badge !!}</td>
                                    <td>
                                        @if($bingo->status === 'ongoing')
                                            <a href="{{ route('draw.index', $bingo) }}" class="btn btn-sm btn-primary">Sorteio</a>
                                        @else
                                            <form action="{{ route('bingos.start', $bingo) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Iniciar</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Nenhum bingo em andamento</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Active Bingo / Last Numbers -->
            <div class="col-lg-4">
                @if($activeBingo)
                <div class="card" style="background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 100%); color: #fff;">
                    <div class="card-body text-center">
                        <h5>Bingo em Andamento</h5>
                        <h3>{{ $activeBingo->name }}</h3>
                        <div class="mt-3">
                            <div class="d-flex flex-wrap justify-content-center" style="gap: 6px;">
                                @foreach(array_slice($lastDrawnNumbers, 0, 15) as $num)
                                    <div class="number-ball" style="width: 36px; height: 36px; font-size: 0.8rem;">{{ str_pad($num, 2, '0', STR_PAD_LEFT) }}</div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('draw.index', $activeBingo) }}" class="btn btn-light">Ir para o Sorteio</a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Quick Actions -->
                <div class="card mt-4">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Atalhos Rápidos</h4>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('bingos.create') }}" class="quick-action-card primary mb-3">
                            <div class="icon"><i class="material-icons">add</i></div>
                            <div>
                                <div style="font-weight: 600;">Novo Bingo</div>
                                <small class="text-muted">Criar um novo bingo</small>
                            </div>
                        </a>
                        <a href="{{ route('cards.generate.form') }}" class="quick-action-card success mb-3">
                            <div class="icon"><i class="material-icons">grid_on</i></div>
                            <div>
                                <div style="font-weight: 600;">Gerar Cartelas</div>
                                <small class="text-muted">Gerar cartelas para um bingo</small>
                            </div>
                        </a>
                        <a href="{{ route('responsibles.create') }}" class="quick-action-card warning">
                            <div class="icon"><i class="material-icons">person_add</i></div>
                            <div>
                                <div style="font-weight: 600;">Novo Responsável</div>
                                <small class="text-muted">Cadastrar um novo responsável</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bingos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Bingos Recentes</h4>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Data</th>
                                    <th>Cartelas</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentBingos as $bingo)
                                <tr>
                                    <td><strong>{{ $bingo->name }}</strong></td>
                                    <td>{{ $bingo->event_date->format('d/m/Y') }}</td>
                                    <td>{{ $bingo->cards_count }}</td>
                                    <td>{!! $bingo->status_badge !!}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
