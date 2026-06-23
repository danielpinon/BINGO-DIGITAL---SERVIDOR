@extends('layouts.app', ['activePage' => 'cards', 'titlePage' => __('Cartelas')])

@section('content')
<div class="content cards-page">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3>Cartelas</h3>
                        <p>Visualize e gerencie as cartelas geradas</p>
                    </div>
                    <div class="d-flex" style="gap: 8px;">
                        @if(request('bingo_id'))
                            <a href="{{ route('cards.export', ['bingo_id' => request('bingo_id'), 'print' => 1]) }}" target="_blank" class="btn btn-info">
                                <i class="material-icons">picture_as_pdf</i> Imprimir PDF
                            </a>
                        @endif
                        <a href="{{ route('cards.generate.form') }}" class="btn btn-primary">
                            <i class="material-icons">add</i> Gerar Cartelas
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('cards.index') }}" method="GET" class="row align-items-end">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="form-label">Bingo</label>
                                    <select name="bingo_id" class="form-control">
                                        <option value="">Todos</option>
                                        @foreach($bingos as $bingo)
                                            <option value="{{ $bingo->id }}" {{ request('bingo_id') == $bingo->id ? 'selected' : '' }}>{{ $bingo->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="form-label">Responsável</label>
                                    <select name="responsible_id" class="form-control">
                                        <option value="">Todos</option>
                                        @foreach($responsibles as $responsible)
                                            <option value="{{ $responsible->id }}" {{ request('responsible_id') == $responsible->id ? 'selected' : '' }}>{{ $responsible->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">Todos</option>
                                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Disponível</option>
                                        <option value="distributed" {{ request('status') == 'distributed' ? 'selected' : '' }}>Distribuída</option>
                                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Devolvida</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">Filtrar</button>
                                <a href="{{ route('cards.index') }}" class="btn btn-outline-primary">Limpar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards Grid -->
        <div class="row">
            @forelse($cards as $card)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-header" style="background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 100%); color: #fff; padding: 10px 15px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">{{ $card->bingo?->name ?? 'Bingo removido' }}</span>
                            <span style="font-size: 1.2rem; font-weight: 700;">{{ $card->card_number }}</span>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <div class="bingo-card-grid" style="gap: 4px; padding: 8px;">
                            @foreach($card->grid as $row => $cols)
                                @foreach($cols as $col => $number)
                                    <div class="bingo-number-cell" style="border-radius: 6px; font-size: 0.85rem;">
                                        {{ str_pad($number, 2, '0', STR_PAD_LEFT) }}
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                        
                        @if($card->responsible)
                            <div class="text-center mt-2">
                                <small class="text-muted">Resp: {{ $card->responsible->name }}</small>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge {{ $card->status == 'available' ? 'badge-primary' : ($card->status == 'distributed' ? 'badge-success' : 'badge-warning') }}">
                                {{ $card->status == 'available' ? 'Disponível' : ($card->status == 'distributed' ? 'Distribuída' : 'Devolvida') }}
                            </span>
                            <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#assignModal{{ $card->id }}">
                                <i class="material-icons" style="font-size: 16px;">person_add</i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assign Modal -->
            <div class="modal fade" id="assignModal{{ $card->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Atribuir Cartela {{ $card->card_number }}</h5>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <form action="{{ route('cards.assign', $card) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Responsável</label>
                                    <select name="responsible_id" class="form-control" required>
                                        <option value="">-- Selecione --</option>
                                        @foreach($responsibles as $responsible)
                                            <option value="{{ $responsible->id }}" {{ $card->responsible_id == $responsible->id ? 'selected' : '' }}>{{ $responsible->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-primary" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Atribuir</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="material-icons" style="font-size: 48px; color: #cbd5e1;">grid_off</i>
                    <p class="mt-2 text-muted">Nenhuma cartela encontrada.</p>
                    <a href="{{ route('cards.generate.form') }}" class="btn btn-primary btn-sm mt-2">Gerar Cartelas</a>
                </div>
            </div>
            @endforelse
        </div>

        @if($cards->hasPages())
        <div class="row">
            <div class="col-12">
                <div class="cards-pagination">
                    {{ $cards->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
