<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top ">
  <div class="container-fluid">
    <div class="navbar-wrapper d-flex align-items-center">
      <a class="navbar-brand d-flex align-items-center" href="#" style="gap: 10px;">
        <i class="material-icons" style="font-size: 28px; color: var(--bingo-accent);">grid_on</i>
        <span style="font-weight: 700; color: var(--bingo-primary);">BINGO DIGITAL</span>
      </a>
    </div>
    <button class="navbar-toggler" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
      <span class="sr-only">Toggle navigation</span>
      <span class="navbar-toggler-icon icon-bar"></span>
      <span class="navbar-toggler-icon icon-bar"></span>
      <span class="navbar-toggler-icon icon-bar"></span>
    </button>
    
    <div class="collapse navbar-collapse justify-content-end">
      <ul class="navbar-nav d-flex align-items-center">
        <li class="nav-item">
          <a class="nav-link" href="#" title="Tela Pública" target="_blank">
            <i class="material-icons" style="color: var(--bingo-accent);">open_in_new</i>
          </a>
        </li>
        
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="material-icons" style="color: var(--bingo-accent);">notifications</i>
            <span class="notification" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">0</span>
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
            <a class="dropdown-item" href="#">Nenhuma notificação</a>
          </div>
        </li>
        
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="navbarDropdownProfile" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <div class="d-flex align-items-center" style="gap: 8px;">
              <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--bingo-accent) 0%, var(--bingo-secondary) 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; font-size: 0.9rem;">
                {{ substr(Auth::user()->name, 0, 1) }}
              </div>
              <div class="d-none d-md-block">
                <div style="font-weight: 600; color: var(--bingo-text-dark); font-size: 0.85rem;">{{ Auth::user()->name }}</div>
                <div style="font-size: 0.75rem; color: #94a3b8;">{{ Auth::user()->type == 0 ? 'Administrador' : 'Usuário' }}</div>
              </div>
              <i class="material-icons" style="color: #94a3b8;">expand_more</i>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownProfile">
            <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="material-icons" style="font-size: 18px; vertical-align: middle; margin-right: 5px;">person</i> Perfil</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="material-icons" style="font-size: 18px; vertical-align: middle; margin-right: 5px;">exit_to_app</i> Sair</a>
          </div>
        </li>
      </ul>
    </div>
  </div>
</nav>
