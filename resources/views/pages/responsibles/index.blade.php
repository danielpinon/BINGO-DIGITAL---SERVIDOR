@extends('layouts.app', ['activePage' => 'responsibles', 'titlePage' => __('Controle de Responsáveis')])

@section('content')
<div class="content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row">
            <div class="col-12">
                <div class="page-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3>Controle de Responsáveis</h3>
                        <p>Gerencie os responsáveis pelas cartelas</p>
                    </div>
                    <a href="{{ route('responsibles.create') }}" class="btn btn-primary">
                        <i class="material-icons">add</i> Novo Responsável
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="card card-stats">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">people</i>
                        </div>
                        <p class="card-category">Total de Responsáveis</p>
                        <h3 class="card-title">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">Pessoas cadastradas</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card card-stats">
                    <div class="card-header card-header-success card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">check_circle</i>
                        </div>
                        <p class="card-category">Ativos</p>
                        <h3 class="card-title">{{ $stats['active'] }}</h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">Responsáveis ativos</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="card card-stats">
                    <div class="card-header card-header-warning card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">grid_on</i>
                        </div>
                        <p class="card-category">Cartelas Vinculadas</p>
                        <h3 class="card-title">{{ $stats['total_cards'] }}</h3>
                    </div>
                    <div class="card-footer">
                        <div class="stats">Total de cartelas distribuídas</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Responsibles List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Lista de Responsáveis</h4>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Telefone</th>
                                    <th>Email</th>
                                    <th>Cartelas</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($responsibles as $responsible)
                                <tr>
                                    <td>#{{ $responsible->id }}</td>
                                    <td><strong>{{ $responsible->name }}</strong></td>
                                    <td>{{ $responsible->phone ?: '-' }}</td>
                                    <td>{{ $responsible->email ?: '-' }}</td>
                                    <td>{{ $responsible->cards_count }}</td>
                                    <td>{!! $responsible->status_badge !!}</td>
                                    <td>
                                        <div class="d-flex" style="gap: 5px;">
                                            <a href="{{ route('responsibles.edit', $responsible) }}" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="material-icons">edit</i>
                                            </a>
                                            <form action="{{ route('responsibles.destroy', $responsible) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja remover este responsável?')">
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
                                    <td colspan="7" class="text-center py-4">
                                        <i class="material-icons" style="font-size: 48px; color: #cbd5e1;">people_outline</i>
                                        <p class="mt-2 text-muted">Nenhum responsável cadastrado ainda.</p>
                                        <a href="{{ route('responsibles.create') }}" class="btn btn-primary btn-sm mt-2">Cadastrar primeiro responsável</a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($responsibles->hasPages())
                    <div class="card-footer">
                        {{ $responsibles->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
