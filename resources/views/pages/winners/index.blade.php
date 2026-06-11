@extends('layouts.app', ['activePage' => 'winners', 'titlePage' => __('Ganhadores')])

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <h3>Ganhadores</h3>
                    <p>Histórico de ganhadores dos bingos</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Lista de Ganhadores</h4>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Bingo</th>
                                    <th>Rodada</th>
                                    <th>Cartela</th>
                                    <th>Responsável</th>
                                    <th>Padrão</th>
                                    <th>Confirmado em</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($winners as $winner)
                                <tr>
                                    <td>{{ $winner->bingo->name }}</td>
                                    <td>{{ $winner->round ? 'Rodada ' . $winner->round->round_number : '-' }}</td>
                                    <td><strong>{{ $winner->card->card_number }}</strong></td>
                                    <td>{{ $winner->responsible?->name ?: '-' }}</td>
                                    <td>{{ $winner->prizePattern->name }}</td>
                                    <td>{{ $winner->confirmed_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="material-icons" style="font-size: 48px; color: #cbd5e1;">emoji_events</i>
                                        <p class="mt-2 text-muted">Nenhum ganhador registrado ainda.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($winners->hasPages())
                    <div class="card-footer">
                        {{ $winners->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
