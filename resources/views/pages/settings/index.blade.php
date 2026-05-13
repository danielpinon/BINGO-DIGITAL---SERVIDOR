@extends('layouts.app', ['activePage' => 'settings', 'titlePage' => __('Configurações')])

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <h3>Configurações</h3>
                    <p>Gerencie as preferências do sistema</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header card-header-primary">
                        <h4 class="card-title">Configurações do Sistema</h4>
                        <p class="card-category">Parâmetros gerais do BINGO DIGITAL</p>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-5">
                            <i class="material-icons" style="font-size: 64px; color: var(--bingo-primary); opacity: 0.5;">settings</i>
                            <h4 class="mt-3 text-muted">Em Desenvolvimento</h4>
                            <p class="text-muted">O painel de configurações avançadas está sendo implementado.</p>
                            <p class="text-muted">Em breve você poderá personalizar temas, notificações e parâmetros do sorteio.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header card-header-info">
                        <h4 class="card-title">Informações</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="material-icons text-muted mr-2">info</i>
                            <span class="text-muted">Versão: <strong>1.0.0</strong></span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="material-icons text-muted mr-2">code</i>
                            <span class="text-muted">Laravel {{ app()->version() }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="material-icons text-muted mr-2">php</i>
                            <span class="text-muted">PHP {{ phpversion() }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="material-icons text-muted mr-2">storage</i>
                            <span class="text-muted">MySQL / MariaDB</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
