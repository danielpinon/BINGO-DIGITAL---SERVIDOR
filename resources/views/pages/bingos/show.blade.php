@extends('layouts.app', ['activePage' => 'bingos', 'titlePage' => __('Detalhes do Bingo')])

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-0">{{ $bingo->name }}</h3>
                    <small class="text-muted">Detalhes do evento</small>
                </div>
                <div class="d-flex" style="gap: 8px;">
                    <form action="{{ route('cards.pdf.start', $bingo) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info btn-sm">
                            <i class="material-icons">picture_as_pdf</i> Gerar PDF
                        </button>
                    </form>
                    @if($bingo->pdf_available)
                        <a href="{{ route('cards.export', ['bingo_id' => $bingo->id]) }}" class="btn btn-success btn-sm">
                            <i class="material-icons">download</i> Baixar PDF
                        </a>
                    @endif
                    <a href="{{ route('bingos.edit', $bingo) }}" class="btn btn-warning btn-sm">
                        <i class="material-icons">edit</i> Editar
                    </a>
                    <a href="{{ route('bingos.index') }}" class="btn btn-outline-primary btn-sm">
                        Voltar
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="card card-stats">
                    <div class="card-header card-header-info card-header-icon">
                        <div class="card-icon"><i class="material-icons">event</i></div>
                        <p class="card-category">Data</p>
                        <h4 class="card-title">{{ optional($bingo->event_date)->format('d/m/Y') }}</h4>
                    </div>
                    <div class="card-footer">
                        <div class="stats">Hora: {{ optional($bingo->event_time)->format('H:i') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card card-stats">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon"><i class="material-icons">confirmation_number</i></div>
                        <p class="card-category">Cartelas</p>
                        <h4 class="card-title">{{ $bingo->cards->count() }}</h4>
                    </div>
                    <div class="card-footer">
                        <div class="stats">Pré-visualização (até 8)</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="card card-stats">
                    <div class="card-header card-header-success card-header-icon">
                        <div class="card-icon"><i class="material-icons">emoji_events</i></div>
                        <p class="card-category">Ganhadores</p>
                        <h4 class="card-title">{{ $bingo->winners->count() }}</h4>
                    </div>
                    <div class="card-footer">
                        <div class="stats">{!! $bingo->status_badge !!}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Informações Gerais</h4>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Nome:</strong> {{ $bingo->name }}</p>
                        <p class="mb-2"><strong>Descrição:</strong> {{ $bingo->description ?: 'Sem descrição' }}</p>
                        <p class="mb-2"><strong>Intervalo de números:</strong> {{ $bingo->number_range_start }} até {{ $bingo->number_range_end }}</p>
                        <p class="mb-2"><strong>Números por cartela:</strong> {{ $bingo->numbers_per_card }}</p>
                        <p class="mb-2"><strong>Sorteios/Rodadas:</strong> {{ $bingo->round_quantity }}</p>
                        <p class="mb-0"><strong>Cartelas por página no PDF:</strong> {{ $bingo->cards_per_page ?? 1 }}</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header card-header-info">
                        <h4 class="card-title">Padrões de Premiação</h4>
                    </div>
                    <div class="card-body">
                        @if($bingo->prizePatterns->isEmpty())
                            <p class="text-muted mb-0">Nenhum padrão cadastrado.</p>
                        @else
                            <ul class="mb-0 pl-3">
                                @foreach($bingo->prizePatterns as $pattern)
                                    <li>
                                        {{ $pattern->pattern_order }}. {{ $pattern->name }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Rodadas</h4>
                    </div>
                    <div class="card-body">
                        @if($bingo->rounds->isEmpty())
                            <p class="text-muted mb-0">Nenhuma rodada criada.</p>
                        @else
                            <ul class="mb-0 pl-3">
                                @foreach($bingo->rounds as $round)
                                    <li>
                                        Rodada {{ $round->round_number }}
                                        <span class="badge badge-{{ $round->status === 'ongoing' ? 'success' : ($round->status === 'finished' ? 'info' : 'secondary') }} ml-2">
                                            {{ $round->status === 'ongoing' ? 'Em andamento' : ($round->status === 'finished' ? 'Finalizada' : 'Pendente') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header card-header-warning">
                        <h4 class="card-title">Ações</h4>
                    </div>
                    <div class="card-body">
                        @if($bingo->status === 'preparation')
                            <form action="{{ route('bingos.start', $bingo) }}" method="POST" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="material-icons">play_arrow</i> Iniciar Bingo
                                </button>
                            </form>
                        @elseif($bingo->status === 'ongoing')
                            <a href="{{ route('draw.index', $bingo) }}" class="btn btn-primary btn-block mb-2">
                                <i class="material-icons">casino</i> Ir para Sorteio
                            </a>
                            <form action="{{ route('bingos.finish', $bingo) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="material-icons">stop</i> Finalizar Bingo
                                </button>
                            </form>
                        @else
                            <p class="text-muted mb-0">Bingo finalizado.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
