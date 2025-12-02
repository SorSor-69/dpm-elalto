<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Esto es clave para la responsividad -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', config('app.name'))</title>
    <!-- AdminLTE & Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}">
    @stack('css')
    <style>
        /* Mejoras responsivas generales */
        body { font-size: 14px; }
        .table-responsive {overflow-x:auto;}
        .btn, .form-control {min-width: 0;}
        
        /* Optimizaciones para móvil */
        @media (max-width: 768px) {
            body { font-size: 13px; }
            .content-header h1 { font-size: 1.2rem; }
            .btn { padding: 0.5rem 0.75rem; font-size: 0.9rem; }
            .form-control { font-size: 16px; } /* Evita zoom automático en iOS */
            .container-fluid { padding: 0.5rem; }
            .card { margin-bottom: 0.75rem; }
            .modal-dialog { margin: 0.5rem; }
        }
        
        @media (max-width: 576px) {
            .content-header h1 { font-size: 1.1rem; }
            .sidebar-mini .main-sidebar { width: 100vw; }
            .main-content { margin-left: 0 !important; }
            .btn-group { flex-wrap: wrap; }
            .table { font-size: 0.85rem; }
            .col-md-6, .col-lg-6 { width: 100% !important; }
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <div class="container-fluid px-1 px-md-2 py-2">
            @yield('content')
        </div>
    </div>
    <!-- Scripts -->
    <script src="{{ asset('vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    @stack('js')
</body>
</html>