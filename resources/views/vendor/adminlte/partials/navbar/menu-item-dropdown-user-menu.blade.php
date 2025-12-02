@php( $logout_url = View::getSection('logout_url') ?? config('adminlte.logout_url', 'logout') )
@php( $profile_url = View::getSection('profile_url') ?? config('adminlte.profile_url', 'logout') )

@if (config('adminlte.usermenu_profile_url', false))
    @php( $profile_url = Auth::user()->adminlte_profile_url() )
@endif

@if (config('adminlte.use_route_url', false))
    @php( $profile_url = $profile_url ? route($profile_url) : '' )
    @php( $logout_url = $logout_url ? route($logout_url) : '' )
@else
    @php( $profile_url = $profile_url ? url($profile_url) : '' )
    @php( $logout_url = $logout_url ? url($logout_url) : '' )
@endif

<li class="nav-item dropdown user-menu">

    {{-- User menu toggler --}}
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        @if(config('adminlte.usermenu_image'))
            <img src="{{ Auth::user()->adminlte_image() }}"
                 class="user-image img-circle elevation-2"
                 alt="{{ Auth::user()->name }}">
        @endif
        <span @if(config('adminlte.usermenu_image')) class="d-none d-md-inline" @endif>
            {{ Auth::user()->name }}
        </span>
    </a>

    {{-- User menu dropdown --}}
    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">

        {{-- User menu header --}}
        @if(!View::hasSection('usermenu_header') && config('adminlte.usermenu_header'))
            <li class="user-header {{ config('adminlte.usermenu_header_class', 'bg-primary') }}
                @if(!config('adminlte.usermenu_image')) h-auto @endif">
                @if(config('adminlte.usermenu_image'))
                    <img src="{{ Auth::user()->adminlte_image() }}"
                         class="img-circle elevation-2"
                         alt="{{ Auth::user()->name }}">
                @endif
                <p class="@if(!config('adminlte.usermenu_image')) mt-0 @endif">
                    {{ Auth::user()->name }}
                    @if(config('adminlte.usermenu_desc'))
                        <small>{{ Auth::user()->adminlte_desc() }}</small>
                    @endif
                </p>
            </li>
        @else
            @yield('usermenu_header')
        @endif

        {{-- Configured user menu links --}}
        @each('adminlte::partials.navbar.dropdown-item', $adminlte->menu("navbar-user"), 'item')

        {{-- User menu body --}}
        @hasSection('usermenu_body')
            <li class="user-body">
                @yield('usermenu_body')
            </li>
        @endif

        {{-- User menu footer --}}
        <li class="user-footer">
            @if($profile_url)
                <a href="{{ $profile_url }}" class="nav-link btn btn-default btn-flat d-inline-block">
                    <i class="fa fa-fw fa-user text-lightblue"></i>
                    Perfil
                </a>
            @endif
            <a class="btn btn-default btn-flat float-right @if(!$profile_url) btn-block @endif"
               href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa fa-fw fa-power-off text-red"></i>
                Cerrar sesión
            </a>
            <form id="logout-form" action="{{ $logout_url }}" method="POST" style="display: none;">
                @if(config('adminlte.logout_method'))
                    {{ method_field(config('adminlte.logout_method')) }}
                @endif
                {{ csrf_field() }}
            </form>
            <button id="btnDarkMode" class="btn btn-dark btn-flat btn-block mt-2" type="button">
                <span id="darkModeIcon"><i class="fa fa-moon"></i></span>
                <span id="darkModeText">Modo nocturno</span>
            </button>
        </li>

    </ul>

</li>

@push('css')
<style>
    body.dark-mode {
        background: #18191a !important;
        color: #e4e6eb !important;
    }
    body.dark-mode .card,
    body.dark-mode .modal-content {
        background: #242526 !important;
        color: #e4e6eb !important;
    }
    body.dark-mode .card-header,
    body.dark-mode .modal-header {
        background: #202124 !important;
        color: #e4e6eb !important;
    }
    body.dark-mode .btn,
    body.dark-mode .btn:active,
    body.dark-mode .btn:focus {
        border-color: #444 !important;
    }
    body.dark-mode .btn-primary {
        background: #3578e5 !important;
        border-color: #3578e5 !important;
    }
    body.dark-mode .btn-danger {
        background: #e53935 !important;
        border-color: #e53935 !important;
    }
    body.dark-mode .btn-success {
        background: #43a047 !important;
        border-color: #43a047 !important;
    }
    body.dark-mode .form-control {
        background: #3a3b3c !important;
        color: #e4e6eb !important;
        border-color: #555 !important;
    }
</style>
@endpush

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('btnDarkMode');
        var icon = document.getElementById('darkModeIcon');
        var text = document.getElementById('darkModeText');
        function updateDarkModeBtn() {
            if(document.body.classList.contains('dark-mode')) {
                icon.innerHTML = '<i class="fa fa-sun"></i>';
                text.textContent = 'Modo claro';
            } else {
                icon.innerHTML = '<i class="fa fa-moon"></i>';
                text.textContent = 'Modo nocturno';
            }
        }
        if(btn){
            btn.addEventListener('click', function() {
                document.body.classList.toggle('dark-mode');
                localStorage.setItem('darkMode', document.body.classList.contains('dark-mode') ? '1' : '0');
                updateDarkModeBtn();
            });
        }
        if(localStorage.getItem('darkMode') === '1') {
            document.body.classList.add('dark-mode');
            updateDarkModeBtn();
        }
    });
</script>
@endpush
