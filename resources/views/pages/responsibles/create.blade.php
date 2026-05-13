@extends('layouts.app', ['activePage' => 'responsibles', 'titlePage' => __('Novo Responsável')])

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Cadastrar Responsável</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('responsibles.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Nome *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Telefone</label>
                                <input type="text" name="phone" class="form-control phone-mask @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="(00) 00000-0000" inputmode="numeric" maxlength="15">
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="email@exemplo.com">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">Status *</label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Ativo</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inativo</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="material-icons">save</i> Salvar
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

@push('js')
<script>
    (function () {
        const phoneInputs = document.querySelectorAll('.phone-mask');
        const formatPhone = function (value) {
            const digits = (value || '').replace(/\D/g, '').slice(0, 11);
            if (digits.length <= 10) {
                return digits
                    .replace(/^(\d{2})(\d)/, '($1) $2')
                    .replace(/(\d{4})(\d)/, '$1-$2');
            }
            return digits
                .replace(/^(\d{2})(\d)/, '($1) $2')
                .replace(/(\d{5})(\d)/, '$1-$2');
        };

        phoneInputs.forEach(function (input) {
            input.value = formatPhone(input.value);
            input.addEventListener('input', function (e) {
                e.target.value = formatPhone(e.target.value);
            });
        });
    })();
</script>
@endpush
