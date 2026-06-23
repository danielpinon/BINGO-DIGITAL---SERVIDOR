@extends('layouts.app', ['activePage' => 'bingos', 'titlePage' => __('Gestão de Bingos')])

@section('content')
<div class="content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="page-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3>Gestão de Bingos</h3>
                        <p>Gerencie todos os seus eventos de bingo</p>
                    </div>
                    <a href="{{ route('bingos.create') }}" class="btn btn-primary">
                        <i class="material-icons">add</i> Novo Bingo
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">event</i>
                        </div>
                        <p class="card-category">Total de Bingos</p>
                        <h3 class="card-title">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">Todos os eventos cadastrados</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-warning card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">schedule</i>
                        </div>
                        <p class="card-category">Em Preparação</p>
                        <h3 class="card-title">{{ $stats['preparation'] }}</h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">Bingos ainda não iniciados</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-success card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">play_circle</i>
                        </div>
                        <p class="card-category">Em Andamento</p>
                        <h3 class="card-title">{{ $stats['ongoing'] }}</h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">Bingos em andamento</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-stats">
                    <div class="card-header card-header-info card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">check_circle</i>
                        </div>
                        <p class="card-category">Finalizados</p>
                        <h3 class="card-title">{{ $stats['finished'] }}</h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">Bingos finalizados</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bingos List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Lista de Bingos</h4>
                        <p class="card-category">Todos os eventos cadastrados no sistema</p>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome do Bingo</th>
                                    <th>Data/Hora</th>
                                    <th>Cartelas</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bingos as $bingo)
                                <tr>
                                    <td>#{{ $bingo->id }}</td>
                                    <td>
                                        <strong>{{ $bingo->name }}</strong>
                                        @if($bingo->description)
                                            <br><small class="text-muted">{{ Str::limit($bingo->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $bingo->event_date->format('d/m/Y') }}
                                        <br><small class="text-muted">{{ $bingo->event_time->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        {{ $bingo->cards_count }} / {{ $bingo->card_quantity }}
                                        @if($bingo->card_generation_badge)
                                            <br><small>{!! $bingo->card_generation_badge !!}</small>
                                        @endif
                                        <br><small>{!! $bingo->cards_pdf_badge !!}</small>
                                        @if($bingo->card_generation_message && $bingo->card_generation_status !== 'ready')
                                            <br><small class="text-muted">{{ $bingo->card_generation_message }}</small>
                                        @endif
                                    </td>
                                    <td>{!! $bingo->status_badge !!}</td>
                                    <td>
                                        <div class="d-flex" style="gap: 5px;">
                                            <a href="{{ route('bingos.show', $bingo) }}" class="btn btn-sm btn-info" title="Visualizar">
                                                <i class="material-icons">visibility</i>
                                            </a>
                                            <a href="{{ route('bingos.edit', $bingo) }}" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="material-icons">edit</i>
                                            </a>
                                            <form action="{{ route('cards.pdf.start', $bingo) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-secondary" title="Gerar PDF das Cartelas">
                                                    <i class="material-icons">picture_as_pdf</i> Gerar PDF
                                                </button>
                                            </form>
                                            @if($bingo->cards_pdf_status === 'ready' && $bingo->cards_pdf_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($bingo->cards_pdf_path))
                                                <a href="{{ route('cards.export', ['bingo_id' => $bingo->id]) }}" class="btn btn-sm btn-success" title="Baixar PDF das Cartelas">
                                                    <i class="material-icons">download</i> Baixar PDF
                                                </a>
                                            @endif
                                            @if($bingo->status === 'preparation')
                                                <form action="{{ route('bingos.start', $bingo) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Iniciar Bingo">
                                                        <i class="material-icons">play_arrow</i>
                                                    </button>
                                                </form>
                                            @elseif($bingo->status === 'ongoing')
                                                <a href="{{ route('draw.index', $bingo) }}" class="btn btn-sm btn-primary" title="Ir para Sorteio">
                                                    <i class="material-icons">casino</i>
                                                </a>
                                                <form action="{{ route('bingos.finish', $bingo) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Finalizar Bingo">
                                                        <i class="material-icons">stop</i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('bingos.destroy', $bingo) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja remover este bingo?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Remover">
                                                    <i class="material-icons">delete</i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="material-icons" style="font-size: 48px; color: #cbd5e1;">event_busy</i>
                                        <p class="mt-2 text-muted">Nenhum bingo cadastrado ainda.</p>
                                        <a href="{{ route('bingos.create') }}" class="btn btn-primary btn-sm mt-2">Criar primeiro bingo</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($bingos->hasPages())
                    <div class="card-footer">
                        {{ $bingos->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
