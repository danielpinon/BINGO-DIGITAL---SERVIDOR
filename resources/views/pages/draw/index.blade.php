@extends('layouts.app', ['activePage' => 'draw', 'titlePage' => __('Sorteio')])

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3>Sistema de Sorteio</h3>
                        <p>{{ $bingo->name }} - {{ $bingo->event_date->format('d/m/Y') }}</p>
                    </div>
                    <a href="{{ route('public.screen', $bingo) }}" target="_blank" class="btn btn-primary">
                        <i class="material-icons">open_in_new</i> Tela Pública
                    </a>
                </div>
            </div>
        </div>

        <livewire:bingo-draw :bingo-id="$bingo->id" />
    </div>
</div>
@endsection
