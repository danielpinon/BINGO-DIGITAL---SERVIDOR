@extends('layouts.app', ['activePage' => 'gerenciamento-de-usuarios', 'titlePage' => __('Gerenciar Usuários')])

@section('content')
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header card-header-primary">
                            <h4 class="card-title ">Usuários do Sistema</h4>
                            <p class="card-category">Aqui você gerencia seus usuários</p>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button data-toggle="modal" data-target="#newUserModalAdmin"
                                        class="btn btn-sm btn-primary">Adicionar Usuário</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class=" text-primary">
                                        <tr>
                                            <th>
                                                Nome
                                            </th>
                                            <th>
                                                Email
                                            </th>
                                            <th>
                                                Data de criação
                                            </th>
                                            <th class="text-right">
                                                Ações
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($users->where('type', 0) as $user)
                                            <tr>
                                                <td>
                                                    {{ $user->name }}
                                                </td>
                                                <td>
                                                    {{ $user->email }}
                                                </td>
                                                <td>
                                                    {{ date_format(date_create($user->created_at), 'd/m/Y H:i') }}
                                                </td>
                                                <td class="td-actions text-right">
                                                    <button rel="tooltip" class="btn btn-success btn-link"
                                                        data-toggle="modal" data-target="#editUserModal"
                                                        data-json="{{ json_encode($user) }}">
                                                        <i class="material-icons">edit</i>
                                                    </button>
                                                    @if (auth()->user()->id != $user->id)
                                                        <button rel="tooltip"
                                                            class="btn btn-danger btn-link btn-deletar-user"
                                                            data-id="{{ $user->id }}" data-name="{{ $user->name }}">
                                                            <i class="material-icons">delete</i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">Nenhum registro encontrado.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header card-header-primary">
                            <h4 class="card-title ">Usuários do Aplicativo</h4>
                            <p class="card-category">Gerenciamento do Aplicativo</p>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button data-toggle="modal" data-target="#newUserModalApp"
                                        class="btn btn-sm btn-primary">Adicionar Usuário</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class=" text-primary">
                                        <tr>
                                            <th>
                                                Nome
                                            </th>
                                            <th>
                                                Email
                                            </th>
                                            <th>
                                                Tipo de Usuário
                                            </th>
                                            <th>
                                                Data de criação
                                            </th>
                                            <th class="text-right">
                                                Ações
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($users->where('type', 1) as $user)
                                            <tr>
                                                <td>
                                                    {{ $user->name }}
                                                </td>
                                                <td>
                                                    {{ $user->email }}
                                                </td>
                                                <td>
                                                    @if ($user->app_type == '1')
                                                        Médico
                                                    @else
                                                        Admin
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ date_format(date_create($user->created_at), 'd/m/Y H:i') }}
                                                </td>
                                                <td class="td-actions text-right">
                                                    <button rel="tooltip" class="btn btn-success btn-link"
                                                        data-toggle="modal" data-target="#editUserAppModal"
                                                        data-json="{{ json_encode($user) }}">
                                                        <i class="material-icons">edit</i>
                                                    </button>
                                                    @if (auth()->user()->id != $user->id)
                                                        <button rel="tooltip"
                                                            class="btn btn-danger btn-link btn-deletar-user"
                                                            data-id="{{ $user->id }}" data-name="{{ $user->name }}">
                                                            <i class="material-icons">delete</i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">Nenhum registro encontrado.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Modal de Adicionar Novo Usuário --}}
    <div class="modal fade" id="newUserModalAdmin" tabindex="-1" role="dialog" aria-labelledby="newUserModalAdminLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newUserModalAdminLabel">Criar Novo Usuário Administrativo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('user.registro.admin') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Nome</label>
                                    <input type="text" class="form-control" placeholder="Anderson ..." name="name">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" placeholder="Anderson@email.com"
                                        name="email">
                                    <small class="form-text text-muted">Esse email servirá para você acessar o sistema e
                                        recuperar senha!</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Senha</label>
                                    <input type="password" class="form-control" placeholder="Senha" minlength="8"
                                        name="password">
                                    <small class="form-text text-muted">Digite a senha que irá utilizar para acessar ao
                                        sistema. <br> Senha com mínimo 8 caracteres </small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Confirmar Senha</label>
                                    <input type="password" class="form-control" placeholder="Confirmar Senha"
                                        name="password_confirmation">
                                    <small class="form-text text-muted">Confirme a senha</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal de Adicionar Novo Usuário --}}
    <div class="modal fade" id="newUserModalApp" tabindex="-1" role="dialog" aria-labelledby="newUserModalAppLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newUserModalAppLabel">Criar Novo Usuário Aplicativo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('user.registro.app') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Nome</label>
                                    <input type="text" class="form-control" placeholder="Anderson ..."
                                        name="name">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="typeUserApp">Tipo de Usuário do Aplicativo</label>
                                    <select class="form-control" name="app_type" data-style="btn btn-link"
                                        id="typeUserApp">
                                        <option value="0">Admin</option>
                                        <option value="1" selected>Montador</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" placeholder="Anderson@email.com"
                                        name="email">
                                    <small class="form-text text-muted">Esse email servirá para você acessar o sistema e
                                        recuperar senha!</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Senha</label>
                                    <input type="password" class="form-control" placeholder="Senha" minlength="8"
                                        name="password">
                                    <small class="form-text text-muted">Digite a senha que irá utilizar para acessar ao
                                        sistema. <br> Senha com mínimo 8 caracteres </small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Confirmar Senha</label>
                                    <input type="password" class="form-control" placeholder="Confirmar Senha"
                                        name="password_confirmation">
                                    <small class="form-text text-muted">Confirme a senha</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal de Adicionar Novo Usuário --}}
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Editar Usuário</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('user.update') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Nome</label>
                                    <input type="text" class="form-control" placeholder="Anderson ..."
                                        name="name">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" placeholder="Anderson@email.com"
                                        name="email">
                                    <small class="form-text text-muted">Esse email servirá para você acessar o sistema e
                                        recuperar senha!</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Senha</label>
                                    <input type="password" class="form-control" placeholder="Senha" minlength="8"
                                        name="password">
                                    <small class="form-text text-muted">Digite a senha que irá utilizar para acessar ao
                                        sistema. <br> Senha com mínimo 8 caracteres </small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Confirmar Senha</label>
                                    <input type="password" class="form-control" placeholder="Confirmar Senha"
                                        name="password_confirmation">
                                    <small class="form-text text-muted">Confirme a senha</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal de Adicionar Novo Usuário --}}
    <div class="modal fade" id="editUserAppModal" tabindex="-1" role="dialog" aria-labelledby="editUserAppModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserAppModalLabel">Editar Usuário</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('user.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" name="id">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Nome</label>
                                    <input type="text" class="form-control" placeholder="Anderson ..."
                                        name="name">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="typeUserApp">Tipo de Usuário do Aplicativo</label>
                                    <select class="form-control" name="app_type" data-style="btn btn-link"
                                        id="typeUserApp">
                                        <option value="0">Admin</option>
                                        <option value="1" selected>Médico</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" class="form-control" placeholder="Anderson@email.com"
                                        name="email">
                                    <small class="form-text text-muted">Esse email servirá para você acessar o sistema e
                                        recuperar senha!</small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Senha</label>
                                    <input type="password" class="form-control" placeholder="Senha" minlength="8"
                                        name="password">
                                    <small class="form-text text-muted">Digite a senha que irá utilizar para acessar ao
                                        sistema. <br> Senha com mínimo 8 caracteres </small>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Confirmar Senha</label>
                                    <input type="password" class="form-control" placeholder="Confirmar Senha"
                                        name="password_confirmation">
                                    <small class="form-text text-muted">Confirme a senha</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $('#editUserModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget)
            var json = button.data('json')
            var modal = $(this)
            var inputs = modal.find('.modal-body input')
            inputs[0].value = json["id"];
            inputs[1].value = json["name"];
            inputs[2].value = json["email"];
        })

        $('#editUserAppModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget)
            var json = button.data('json')
            var modal = $(this)
            var inputs = modal.find('.modal-body input')
            inputs[0].value = json["id"];
            inputs[1].value = json["name"];
            inputs[2].value = json["email"];
            $(this).find('select[name="app_type"]').val(json["app_type"]).trigger('change');
        })

        $('.btn-deletar-user').click((event) => {
            var url = "{{ route('user.delete', '#') }}";
            Swal.fire({
                title: 'Você tem certeza?',
                text: "Essa ação é irreversível!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, apagar ' + $(event.currentTarget).data('name') + '!'
            }).then((result) => {
                if (result.value) {
                    window.location.href = url.replace('#', $(event.currentTarget).data('id'));
                }
            });
        });
    </script>
@endpush
