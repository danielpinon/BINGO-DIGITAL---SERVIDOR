<div class="sidebar" data-color="purple" data-background-color="white">
    <div class="logo">
        <a href="{{ route('admin.home') }}" class="simple-text logo-normal" style="display: flex; align-items: center; justify-content: center; padding: 15px;">
            <img src="{{ asset('material/img/fenix-logo.png') }}" alt="Fênix Motocenter" style="width: 170px; height: 54px; object-fit: contain; border-radius: 6px; background: #fff; padding: 6px;">
        </a>
        <div style="text-align: center; color: rgba(255,255,255,0.5); font-size: 0.7rem; margin-top: -10px; padding-bottom: 10px; letter-spacing: 2px; text-transform: uppercase;">
            Painel Administrativo
        </div>
    </div>
    <div class="sidebar-wrapper">
        <ul class="nav">
            {{-- PRINCIPAL --}}
            <li class="sidebar-menu-section">Principal</li>
            
            <li class="nav-item{{ $activePage == 'dashboard' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('admin.home') }}">
                    <i class="material-icons">dashboard</i>
                    <p>{{ __('Dashboard') }}</p>
                </a>
            </li>

            {{-- GERENCIAMENTO --}}
            <li class="sidebar-menu-section">Gerenciamento</li>
            
            <li class="nav-item{{ $activePage == 'bingos' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('bingos.index') }}">
                    <i class="material-icons">event</i>
                    <p>{{ __('Bingos') }}</p>
                </a>
            </li>
            
            <li class="nav-item{{ $activePage == 'cards' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('cards.index') }}">
                    <i class="material-icons">grid_on</i>
                    <p>{{ __('Cartelas') }}</p>
                </a>
            </li>
            
            <li class="nav-item{{ $activePage == 'responsibles' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('responsibles.index') }}">
                    <i class="material-icons">people</i>
                    <p>{{ __('Responsáveis') }}</p>
                </a>
            </li>
            
            <li class="nav-item{{ $activePage == 'draw' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('bingos.index') }}">
                    <i class="material-icons">casino</i>
                    <p>{{ __('Sorteio') }}</p>
                </a>
            </li>

            <li class="nav-item{{ $activePage == 'winners' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('winners.index') }}">
                    <i class="material-icons">emoji_events</i>
                    <p>{{ __('Ganhadores') }}</p>
                </a>
            </li>

            <li class="nav-item{{ $activePage == 'reports' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('reports.index') }}">
                    <i class="material-icons">assessment</i>
                    <p>{{ __('Relatórios') }}</p>
                </a>
            </li>

            {{-- CONFIGURAÇÕES --}}
            <li class="sidebar-menu-section">Configurações</li>
            
            <li class="nav-item {{ ($activePage == 'profile' || $activePage == 'gerenciamento-de-usuarios') ? ' active' : '' }}">
                <a class="nav-link" data-toggle="collapse" href="#usersMenu" aria-expanded="false">
                    <i class="material-icons">manage_accounts</i>
                    <p>{{ __('Usuários') }}
                        <b class="caret"></b>
                    </p>
                </a>
                <div class="collapse" id="usersMenu">
                    <ul class="nav">
                        <li class="nav-item{{ $activePage == 'profile' ? ' active' : '' }}">
                            <a class="nav-link" href="{{ route('profile.edit') }}">
                                <i class="material-icons">badge</i>
                                <span class="sidebar-normal">{{ __('Perfil') }}</span>
                            </a>
                        </li>
                        <li class="nav-item{{ $activePage == 'gerenciamento-de-usuarios' ? ' active' : '' }}">
                            <a class="nav-link" href="{{ route('user.index') }}">
                                <i class="material-icons">group_add</i>
                                <span class="sidebar-normal">{{ __('Gerenciar Usuários') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li class="nav-item{{ $activePage == 'settings' ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('settings.index') }}">
                    <i class="material-icons">settings</i>
                    <p>{{ __('Configurações') }}</p>
                </a>
            </li>
            
            <li class="nav-item" style="margin-bottom: 2rem;">
                <a class="nav-link" href="{{ route('logout') }}"
                    onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    <i class="material-icons">logout</i>
                    <p>{{ __('Sair') }}</p>
                </a>
            </li>
        </ul>
    </div>
</div>
