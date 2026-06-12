@extends('layouts.app', ['activePage' => 'bingos', 'titlePage' => __('Novo Bingo')])

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <h3>Novo Bingo</h3>
                    <p>Cadastre um novo evento de bingo</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Informações do Bingo</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('bingos.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Nome do Bingo *</label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ex: Bingo Beneficente APAE" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Descrição</label>
                                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Descrição do evento...">{{ old('description') }}</textarea>
                                        @error('description')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Data do Evento *</label>
                                        <input type="date" name="event_date" class="form-control @error('event_date') is-invalid @enderror" value="{{ old('event_date') }}" required>
                                        @error('event_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Hora do Evento *</label>
                                        <input type="time" name="event_time" class="form-control @error('event_time') is-invalid @enderror" value="{{ old('event_time') }}" required>
                                        @error('event_time')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Quantidade de Cartelas *</label>
                                        <input type="number" name="card_quantity" class="form-control @error('card_quantity') is-invalid @enderror" value="{{ old('card_quantity', 300) }}" min="1" required>
                                        @error('card_quantity')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Números por Cartela *</label>
                                        <input type="number" name="numbers_per_card" class="form-control @error('numbers_per_card') is-invalid @enderror" value="{{ old('numbers_per_card', 25) }}" min="1" required>
                                        @error('numbers_per_card')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Quantidade de Sorteios/Rodadas *</label>
                                        <input type="number" name="round_quantity" class="form-control @error('round_quantity') is-invalid @enderror" value="{{ old('round_quantity', 3) }}" min="3" max="5" required>
                                        @error('round_quantity')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Cartelas por Página no PDF *</label>
                                        <input type="number" name="cards_per_page" class="form-control @error('cards_per_page') is-invalid @enderror" value="{{ old('cards_per_page', 1) }}" min="1" max="6" required>
                                        @error('cards_per_page')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted">Quantidade de cópias da mesma cartela por página.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Intervalo de Números (Início) *</label>
                                        <input type="number" name="number_range_start" class="form-control @error('number_range_start') is-invalid @enderror" value="{{ old('number_range_start', 1) }}" min="1" required>
                                        @error('number_range_start')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Intervalo de Números (Fim) *</label>
                                        <input type="number" name="number_range_end" class="form-control @error('number_range_end') is-invalid @enderror" value="{{ old('number_range_end', 75) }}" min="1" required>
                                        @error('number_range_end')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Padrões de Premiação *</label>
                                        <div class="row">
                                            @foreach($patterns as $key => $label)
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input class="form-check-input" type="checkbox" name="prize_patterns[]" value="{{ $key }}" {{ in_array($key, old('prize_patterns', [])) ? 'checked' : '' }}>
                                                        {{ $label }}
                                                        <span class="form-check-sign">
                                                            <span class="check"></span>
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        @error('prize_patterns')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="material-icons">save</i> Salvar Bingo
                                    </button>
                                    <a href="{{ route('bingos.index') }}" class="btn btn-outline-primary">Cancelar</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
