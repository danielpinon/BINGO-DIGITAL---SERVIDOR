@extends('layouts.app', ['activePage' => 'cards', 'titlePage' => __('Gerar Cartelas')])

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <h3>Gerar Cartelas</h3>
                    <p>Gere cartelas automaticamente para um bingo</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Configuração da Geração</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('cards.generate') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Selecionar Bingo *</label>
                                <select name="bingo_id" class="form-control @error('bingo_id') is-invalid @enderror" required>
                                    <option value="">-- Selecione um bingo --</option>
                                    @foreach($bingos as $bingo)
                                        <option value="{{ $bingo->id }}" {{ old('bingo_id') == $bingo->id ? 'selected' : '' }}>
                                            {{ $bingo->name }} ({{ $bingo->event_date->format('d/m/Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('bingo_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Quantidade de Cartelas *</label>
                                <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 100) }}" min="1" max="1000" required>
                                @error('quantity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="material-icons">grid_on</i> Gerar Cartelas
                                </button>
                                <a href="{{ route('cards.index') }}" class="btn btn-outline-primary">Ver Cartelas</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Informações</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="material-icons text-success">check_circle</i> Cartelas geradas com números únicos</li>
                            <li class="mb-2"><i class="material-icons text-success">check_circle</i> Unicidade garantida dentro do mesmo bingo</li>
                            <li class="mb-2"><i class="material-icons text-success">check_circle</i> Layout 5x5 com 25 números por cartela</li>
                            <li class="mb-2"><i class="material-icons text-success">check_circle</i> Numeração sequencial automática (001, 002...)</li>
                            <li class="mb-2"><i class="material-icons text-success">check_circle</i> Possível exportar para PDF</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
