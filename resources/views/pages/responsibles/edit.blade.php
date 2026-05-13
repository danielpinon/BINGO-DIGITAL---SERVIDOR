@extends('layouts.app', ['activePage' => 'responsibles', 'titlePage' => __('Editar Responsável')])

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Editar Responsável</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('responsibles.update', $responsible) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label class="form-label">Nome *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $responsible->name) }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Telefone</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $responsible->phone) }}" placeholder="(00) 00000-0000">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $responsible->email) }}" placeholder="email@exemplo.com">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $responsible->status) == 'active' ? 'selected' : '' }}>Ativo</option>
                                    <option value="inactive" {{ old('status', $responsible->status) == 'inactive' ? 'selected' : '' }}>Inativo</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="material-icons">save</i> Atualizar
                                </button>
                                <a href="{{ route('responsibles.index') }}" class="btn btn-outline-primary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
